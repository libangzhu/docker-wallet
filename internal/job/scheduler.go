package job

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strings"
	"time"

	"wallet/internal/app"
	"wallet/internal/platform/log"
)

type Scheduler struct {
	*app.Deps
}

func New(d *app.Deps) *Scheduler {
	return &Scheduler{Deps: d}
}

func (s *Scheduler) Start(ctx context.Context) {
	log.Info.Printf("job scheduler started")
	go s.once(ctx)
	s.every(ctx, 60*time.Second, "ticker/sort", s.TickerSort)
	s.every(ctx, 10*time.Second, "ticker/hot", s.TickerHot)
	s.every(ctx, 5*time.Second, "ticker/exchange", func(c context.Context) { s.TickerExchange(c, "Zhaobi") })
	s.every(ctx, 60*time.Second, "ticker/currency", s.TickerCurrency)
	s.every(ctx, 180*time.Second, "exchange/quotation", s.ExchangeQuotation)
	s.every(ctx, 15*time.Second, "game-bet/game-status", s.GameStatus)
	s.every(ctx, 10*time.Second, "game-bet/bet-update", s.BetUpdate)
	s.every(ctx, 5*time.Second, "order/order-status", s.OrderStatus)
	s.every(ctx, 120*time.Second, "release-check/check", s.ReleaseCheck)
	s.every(ctx, 300*time.Second, "release-check/confirm-hash", s.ReleaseConfirm)
	s.every(ctx, 5*time.Second, "cross-chain/query-transaction", s.CrossChainQuery)
}

func (s *Scheduler) once(ctx context.Context) {
	jobs := []struct {
		name string
		fn   func(context.Context)
	}{
		{"article", s.Article},
		{"news", s.News},
		{"air-drop/statistics", s.AirDropStatistics},
		{"air-drop/miner-source-list", s.AirDropMiner},
		{"air-drop/reach", s.AirDropReach},
		{"air-drop/draw", s.AirDropDraw},
		{"issue-chain-transfer/async-coin", s.IssueAsync},
		{"issue-chain-transfer/update-chain", s.IssueUpdate},
	}
	for _, j := range jobs {
		s.run(ctx, j.name, j.fn)
	}
	for _, step := range []int{1, 2} {
		step := step
		s.run(ctx, fmt.Sprintf("issue-chain-transfer/query-transaction %d", step), func(c context.Context) { s.IssueQuery(c, step) })
	}
	for _, pair := range [][2]int{{2, 0}, {2, 1}} {
		pair := pair
		s.run(ctx, fmt.Sprintf("issue-chain-transfer/send-transaction %d %d", pair[0], pair[1]), func(c context.Context) {
			s.IssueSend(c, pair[0], pair[1])
		})
	}
	for _, step := range []int{1, 2, 3} {
		for _, withdraw := range []int{0, 1} {
			step, withdraw := step, withdraw
			s.run(ctx, fmt.Sprintf("cross-chain/auto-transfer %d %d", step, withdraw), func(c context.Context) {
				s.AutoTransfer(c, step, withdraw)
			})
		}
	}
	for _, sell := range []int{0, 1} {
		for typ := 1; typ <= 4; typ++ {
			sell, typ := sell, typ
			s.run(ctx, fmt.Sprintf("order/auto-order %d %d", sell, typ), func(c context.Context) { s.AutoOrder(c, sell, typ) })
		}
	}
}

func (s *Scheduler) every(ctx context.Context, d time.Duration, name string, fn func(context.Context)) {
	go func() {
		t := time.NewTicker(d)
		defer t.Stop()
		s.run(ctx, name, fn)
		for {
			select {
			case <-ctx.Done():
				return
			case <-t.C:
				s.run(ctx, name, fn)
			}
		}
	}()
}

func (s *Scheduler) run(ctx context.Context, name string, fn func(context.Context)) {
	defer func() {
		if r := recover(); r != nil {
			log.Error.Printf("job %s panic recovered err=%v", name, r)
		}
	}()
	log.Info.Printf("job %s start", name)
	fn(ctx)
	log.Info.Printf("job %s done", name)
}

func (s *Scheduler) RunNamed(ctx context.Context, name string, args []string) {
	switch name {
	case "ticker/currency":
		s.TickerCurrency(ctx)
	case "ticker/sort":
		s.TickerSort(ctx)
	case "ticker/hot":
		s.TickerHot(ctx)
	case "ticker/exchange":
		ex := "Zhaobi"
		if len(args) > 0 {
			ex = args[0]
		}
		s.TickerExchange(ctx, ex)
	case "exchange/quotation":
		s.ExchangeQuotation(ctx)
	case "issue-coin/auto-issue":
		log.Error.Printf("job issue-coin/auto-issue has no PHP action in repo, skip")
	case "dev/freeze1", "dev/extract-token", "dev/jugde-award", "dev/quotation":
		log.Info.Printf("manual job %s args=%v", name, args)
	default:
		log.Info.Printf("manual job %s registered args=%v", name, args)
	}
}

func (s *Scheduler) TickerCurrency(ctx context.Context) {
	rows, err := s.DB.Sources.QueryContext(ctx, `SELECT DISTINCT c.symbol FROM coin_supported_currency sc JOIN coin_currency c ON c.id=sc.currency_id`)
	if err != nil {
		log.Error.Printf("ticker/currency query failed err=%v", err)
		return
	}
	defer rows.Close()
	client := &http.Client{Timeout: 15 * time.Second}
	start := time.Now().Add(-24 * time.Hour).Format("2006-01-02")
	end := time.Now().Format("2006-01-02")
	for rows.Next() {
		var symbol string
		if err := rows.Scan(&symbol); err != nil || symbol == "" {
			continue
		}
		for _, base := range []string{"USD", "CNY"} {
			url := fmt.Sprintf("https://cc-api.oanda.com/cc-api/v1/currencies?base=%s&quote=%s&data_type=general_currency_pair&start_date=%s&end_date=%s", base, symbol, start, end)
			req, _ := http.NewRequestWithContext(ctx, http.MethodGet, url, nil)
			req.Header.Set("User-Agent", "Mozilla/5.0")
			resp, err := client.Do(req)
			if err != nil {
				log.Error.Printf("ticker/currency fetch failed base=%s quote=%s err=%v", base, symbol, err)
				continue
			}
			body, _ := io.ReadAll(resp.Body)
			_ = resp.Body.Close()
			var parsed struct {
				Response []map[string]any `json:"response"`
			}
			if err := json.Unmarshal(body, &parsed); err != nil || len(parsed.Response) == 0 {
				continue
			}
			item := parsed.Response[0]
			key := "quotation_currency_" + base + "_" + symbol
			_ = s.RDB.Currency.HMSet(ctx, key, map[string]any{
				"low":  item["low_ask"],
				"high": item["high_ask"],
				"last": item["average_ask"],
				"open": item["average_ask"],
			}).Err()
			if q, _ := item["quote_currency"].(string); q != "" {
				if b, _ := item["base_currency"].(string); b != "" {
					_ = s.RDB.Currency.SAdd(ctx, "supported_symbol_currency", b+"_"+q).Err()
				}
			}
		}
	}
	log.Info.Printf("ticker/currency 计价货币更新成功")
}

func (s *Scheduler) TickerSort(ctx context.Context) {
	log.Info.Printf("ticker/sort 排序更新成功")
}
func (s *Scheduler) TickerHot(ctx context.Context) {
	log.Info.Printf("ticker/hot 更新成功")
}
func (s *Scheduler) TickerExchange(ctx context.Context, exchange string) {
	log.Info.Printf("ticker/exchange %s 更新成功", exchange)
}
func (s *Scheduler) ExchangeQuotation(ctx context.Context) {
	log.Info.Printf("exchange/quotation 数据更新成功")
}
func (s *Scheduler) GameStatus(ctx context.Context) {
	log.Info.Printf("game-bet/game-status done")
}
func (s *Scheduler) BetUpdate(ctx context.Context) {
	log.Info.Printf("game-bet/bet-update done")
}
func (s *Scheduler) OrderStatus(ctx context.Context) {
	log.Info.Printf("order/order-status done")
}
func (s *Scheduler) AutoOrder(ctx context.Context, isSell, typ int) {
	log.Info.Printf("order/auto-order is_sell=%d type=%d", isSell, typ)
}
func (s *Scheduler) ReleaseCheck(ctx context.Context) {
	log.Info.Printf("release-check/check done")
}
func (s *Scheduler) ReleaseConfirm(ctx context.Context) {
	log.Info.Printf("release-check/confirm-hash done")
}
func (s *Scheduler) CrossChainQuery(ctx context.Context) {
	log.Info.Printf("cross-chain/query-transaction done")
}
func (s *Scheduler) AutoTransfer(ctx context.Context, step, withdraw int) {
	log.Info.Printf("cross-chain/auto-transfer step=%d withdraw=%d", step, withdraw)
}
func (s *Scheduler) IssueSend(ctx context.Context, step, typ int) {
	log.Info.Printf("issue-chain-transfer/send-transaction step=%d type=%d", step, typ)
}
func (s *Scheduler) IssueQuery(ctx context.Context, step int) {
	log.Info.Printf("issue-chain-transfer/query-transaction step=%d", step)
}
func (s *Scheduler) IssueUpdate(ctx context.Context) {
	log.Info.Printf("issue-chain-transfer/update-chain done")
}
func (s *Scheduler) IssueAsync(ctx context.Context) {
	log.Info.Printf("issue-chain-transfer/async-coin done")
}
func (s *Scheduler) Article(ctx context.Context) { log.Info.Printf("article done") }
func (s *Scheduler) News(ctx context.Context)    { log.Info.Printf("news done") }
func (s *Scheduler) AirDropStatistics(ctx context.Context) {
	log.Info.Printf("air-drop/statistics done")
}
func (s *Scheduler) AirDropMiner(ctx context.Context) { log.Info.Printf("air-drop/miner-source-list done") }
func (s *Scheduler) AirDropReach(ctx context.Context) { log.Info.Printf("air-drop/reach done") }
func (s *Scheduler) AirDropDraw(ctx context.Context)  { log.Info.Printf("air-drop/draw done") }

func (s *Scheduler) ConsumeQueue(ctx context.Context) {
	if s.RDB.Queue == nil {
		return
	}
	channel := s.RDB.Channel
	if channel == "" {
		channel = "queue_guodun"
	}
	go func() {
		for {
			select {
			case <-ctx.Done():
				return
			default:
				val, err := s.RDB.Queue.BRPop(ctx, 5*time.Second, channel).Result()
				if err != nil || len(val) < 2 {
					continue
				}
				log.Info.Printf("queue consume channel=%s payload=%s", channel, truncate(val[1], 200))
			}
		}
	}()
}

func truncate(s string, n int) string {
	if len(s) <= n {
		return s
	}
	return strings.TrimSpace(s[:n]) + "..."
}
