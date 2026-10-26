package api

import (
	"database/sql"
	"encoding/json"
	"math/rand"
	"strconv"
	"strings"

	"github.com/gin-gonic/gin"

	"wallet/internal/platform/lang"
	"wallet/internal/platform/log"
	"wallet/internal/platform/req"
	"wallet/internal/platform/response"
)

func (h *Handler) ApplicationIndex(c *gin.Context) {
	platformID := req.ParamInt(c, "platform_id", 1)
	locale := req.Lang(c)
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{
		"recommend":     h.appRecommend(c, platformID, locale),
		"cate_app_data": h.appCategories(c, platformID, locale),
		"banner":        h.appBanners(c, platformID),
	}})
}

func (h *Handler) ApplicationCateAppInfo(c *gin.Context) {
	locale := req.Lang(c)
	response.WriteAPI(c, gin.H{"code": 0, "data": h.appCategories(c, 0, locale)})
}

func (h *Handler) ApplicationAppInfo(c *gin.Context) {
	id := req.ParamInt(c, "id", 0)
	locale := req.Lang(c)
	row := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT id,name,official_url,h5_url,advertise,description,enable,platform_id FROM coin_application WHERE id=?`, id)
	var rid, enable, pid int
	var name []byte
	var official, h5url, ad, desc sql.NullString
	if err := row.Scan(&rid, &name, &official, &h5url, &ad, &desc, &enable, &pid); err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": nil})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{
		"id": rid, "name": lang.Pick(name, locale), "official_url": nullString(official),
		"h5_url": nullString(h5url), "advertise": nullString(ad), "description": nullString(desc),
		"enable": enable, "platform_id": pid,
	}})
}

func (h *Handler) ApplicationGetAppsByCate(c *gin.Context) {
	cate := req.ParamInt(c, "cate_id", 0)
	locale := req.Lang(c)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,name,official_url,h5_url,advertise FROM coin_application WHERE enable=1`)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var name []byte
		var official, h5url, ad sql.NullString
		if err := rows.Scan(&id, &name, &official, &h5url, &ad); err != nil {
			continue
		}
		list = append(list, map[string]any{
			"id": id, "name": lang.Pick(name, locale), "official_url": nullString(official),
			"h5_url": nullString(h5url), "advertise": nullString(ad), "cate_id": cate,
		})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) ApplicationRankList(c *gin.Context) {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), `SELECT id,name,sort FROM coin_applicate_rank ORDER BY sort`)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
		return
	}
	defer rows.Close()
	list := scanIDNameSort(rows)
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) ApplicationAppSearch(c *gin.Context) {
	keyword := req.Param(c, "keyword", "")
	locale := req.Lang(c)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,name,h5_url FROM coin_application WHERE enable=1`)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var name []byte
		var h5url sql.NullString
		if err := rows.Scan(&id, &name, &h5url); err != nil {
			continue
		}
		n := lang.Pick(name, locale)
		if keyword != "" && !strings.Contains(strings.ToLower(n), strings.ToLower(keyword)) {
			continue
		}
		list = append(list, map[string]any{"id": id, "name": n, "h5_url": nullString(h5url)})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) ApplicationGetHotSearch(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
}

func (h *Handler) ApplicationAddUserNum(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}

func (h *Handler) TickerIndex(c *gin.Context) {
	page := req.ParamInt(c, "page", 1)
	exchange := strings.ToLower(req.Param(c, "exchange", "zhaobi"))
	if exchange == "jingwang" {
		exchange = "jinwang"
	}
	allowed := map[string]bool{"huobi": true, "binance": true, "zhaobi": true, "bitnasdaq": true, "jinwang": true}
	if !allowed[exchange] {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "不存在的交易平台", "data": nil})
		return
	}
	data := h.tickerFromCache(c, exchange, page)
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": data})
}

func (h *Handler) TickerOptional(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": []any{}})
}

func (h *Handler) MarketIndex(c *gin.Context) {
	h.TickerIndex(c)
}

func (h *Handler) MarketDetail(c *gin.Context) {
	symbol := req.Param(c, "symbol", "")
	q := quotation(c.Request.Context(), h.RDB, symbol)
	response.WriteAPI(c, gin.H{"code": 0, "data": q})
}

func (h *Handler) MarketExchange(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
}

func (h *Handler) SupportedSymbolSearch(c *gin.Context) {
	keyword := req.Param(c, "keyword", "")
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,symbol,exchange FROM coin_supported_symbol WHERE symbol LIKE ? LIMIT 50`, "%"+keyword+"%")
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var symbol, exchange sql.NullString
		if err := rows.Scan(&id, &symbol, &exchange); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "symbol": nullString(symbol), "exchange": nullString(exchange)})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) WalletTokenCategory(c *gin.Context) {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), `SELECT id,name FROM wallet_supported_token_catetory`)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
		return
	}
	defer rows.Close()
	response.WriteAPI(c, gin.H{"code": 0, "data": scanIDName(rows), "msg": "success"})
}

func (h *Handler) WalletInfo(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{}, "msg": "success"})
}

func (h *Handler) WalletGetBalance(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{"balance": "0"}, "msg": "success"})
}

func (h *Handler) WalletTransfer(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}

func (h *Handler) WalletGameTradeUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 1, "msg": "ok"})
}

func (h *Handler) WalletChainChains(c *gin.Context) {
	if c.Request.Method != "GET" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "请求方式错误!", "data": nil})
		return
	}
	addr := req.Param(c, "wallet_address", "")
	if addr == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	page := req.ParamInt(c, "page", 1)
	size := req.ParamInt(c, "size", 10)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,token,platform,status,create_time FROM coin_platform_withhold WHERE wallet_address=? ORDER BY create_time DESC LIMIT ? OFFSET ?`,
		addr, size, (page-1)*size)
	if err != nil {
		log.Error.Printf("wallet-chain/chains failed addr=%s err=%v", addr, err)
		response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{"list": []any{}, "page": gin.H{"pageCount": 0, "pageSize": size, "currentPage": page}}, "msg": "success"})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, status int
		var token, platform sql.NullString
		var createAt sql.NullTime
		if err := rows.Scan(&id, &token, &platform, &status, &createAt); err != nil {
			continue
		}
		item := map[string]any{"id": id, "token": nullString(token), "platform": nullString(platform), "status": status}
		if createAt.Valid {
			item["create_time"] = createAt.Time.Format("2006-01-02 15:04:05")
		}
		list = append(list, item)
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{"list": list, "page": gin.H{"pageCount": 1, "pageSize": size, "currentPage": page}}, "msg": "success"})
}

func (h *Handler) WalletChainChain(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": nil, "msg": "success"})
}

func (h *Handler) WalletChainDetail(c *gin.Context) {
	if c.Request.Method != "GET" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "请求方式错误!", "data": nil})
		return
	}
	id := req.Param(c, "id", "")
	if id == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{"id": id}, "msg": "success"})
}

func (h *Handler) WalletCoinIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}

func (h *Handler) TradeAddMemo(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) TradeUpdateMemo(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) TradeGetMemo(c *gin.Context) {
	hash := req.Param(c, "hash", "")
	var memo sql.NullString
	_ = h.DB.Sources.QueryRowContext(c.Request.Context(), `SELECT memo FROM coin_hash_memo WHERE hash=?`, hash).Scan(&memo)
	response.WriteAPI(c, gin.H{"code": 0, "data": nullString(memo), "msg": "success"})
}
func (h *Handler) TradeSyncToDb(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) TradeAddressIsExist(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": false, "msg": "success"})
}
func (h *Handler) TradeTokenTransfer(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}

func (h *Handler) BillInsert(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) BillList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}

func (h *Handler) MemberIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}

func (h *Handler) LockIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}

func (h *Handler) OrderIndex(c *gin.Context) {
	if c.Request.Method != "POST" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "请求错误!"})
		return
	}
	address := req.Param(c, "address", "")
	token := strings.ToUpper(req.Param(c, "token_name", ""))
	market := strings.ToUpper(req.Param(c, "market_name", ""))
	isSell := req.Param(c, "isSell", "")
	key := "C2C_" + bool01(isSell) + "_" + strings.ReplaceAll(token, "/", "") + "_" + strings.ReplaceAll(market, "/", "")
	if h.RDB.App != nil {
		exists, _ := h.RDB.App.HExists(c.Request.Context(), key, address).Result()
		if exists {
			response.WriteAPI(c, gin.H{"code": -1, "msg": "尚存在一笔未完成的交易!"})
			return
		}
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}

func (h *Handler) CrossChainTransfer(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) CrossChainFixTransfer(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) CrossChainTransferStatus(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": gin.H{}})
}

func (h *Handler) IssueCoinValidate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) IssueCoinApply(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) IssueCoinApplyList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}
func (h *Handler) IssueCoinApplyDetail(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{}, "msg": "success"})
}
func (h *Handler) IssueCoinSendTransfer(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) IssueCoinVerify(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) IssueCoinRevoke(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) IssueCoinSymbolValidate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) IssueChainIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}

func (h *Handler) AirDropIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}
func (h *Handler) PlatformCoinsAirDrop(c *gin.Context) {
	if key := req.Param(c, "key", ""); key != "" {
		plain, err := h.DES.Decrypt(key)
		if err != nil {
			log.Error.Printf("platform-coins/air-drop decrypt failed err=%v", err)
			response.WriteAPI(c, gin.H{"code": -1, "data": nil, "msg": "缺少必要的参数"})
			return
		}
		var body map[string]any
		if err := json.Unmarshal([]byte(plain), &body); err != nil {
			response.WriteAPI(c, gin.H{"code": -1, "data": nil, "msg": "缺少必要的参数"})
			return
		}
		c.Set("_json_body", body)
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": gin.H{}})
}
func (h *Handler) PlatformCoinsIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}
func (h *Handler) PlatformCoinsRelation(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}

func (h *Handler) ArticleList(c *gin.Context) {
	rows, err := h.DB.Coin.QueryContext(c.Request.Context(), `SELECT id,title,content,create_time FROM article ORDER BY id DESC LIMIT 20`)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
		return
	}
	defer rows.Close()
	response.WriteAPI(c, gin.H{"code": 0, "data": scanContent(rows)})
}
func (h *Handler) FeedbackIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) AppUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{}, "msg": "success"})
}
func (h *Handler) AppNewUpdate(c *gin.Context) { h.AppUpdate(c) }
func (h *Handler) AppUpdateVersion(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{}, "msg": "success"})
}
func (h *Handler) OssUpload(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": gin.H{}})
}
func (h *Handler) ClientParamsIndex(c *gin.Context) {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), `SELECT id,type,url FROM coin_client_params`)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var typ, url sql.NullString
		if err := rows.Scan(&id, &typ, &url); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "type": nullString(typ), "url": nullString(url)})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list, "msg": "success"})
}
func (h *Handler) ValidateIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) GameIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}
func (h *Handler) GameBetIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}, "msg": "success"})
}
func (h *Handler) CoinDogArticle(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
}
func (h *Handler) CoinDogArticleInfo(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{}})
}
func (h *Handler) CoinDogLive(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
}
func (h *Handler) CoinDogArticleBanner(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
}
func (h *Handler) VisitStatisticsIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success"})
}
func (h *Handler) SecKillIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
}

func (h *Handler) tickerFromCache(c *gin.Context, exchange string, page int) map[string]any {
	key := "ticker_" + exchange
	raw, err := h.RDB.Ticker.Get(c.Request.Context(), key).Result()
	if err != nil || raw == "" {
		return gin.H{"ticker": []any{}, "page": page}
	}
	var data any
	if err := json.Unmarshal([]byte(raw), &data); err != nil {
		return gin.H{"ticker": []any{}, "page": page}
	}
	return gin.H{"ticker": data, "page": page}
}

const ossPrefix = "https://service.export.net/upload"

func (h *Handler) appRecommend(c *gin.Context, platformID int, locale string) []map[string]any {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT relate_id,name,type,icon FROM coin_applicate_recommend WHERE platform_id=? ORDER BY sort`, platformID)
	if err != nil {
		log.Error.Printf("application recommend failed platform_id=%d err=%v", platformID, err)
		return []map[string]any{}
	}
	defer rows.Close()
	disabledCate := h.idSet(c, `SELECT id FROM coin_application_category WHERE enable<>1 AND platform_id=?`, platformID)
	disabledApp := h.idSet(c, `SELECT id FROM coin_application WHERE enable<>1 AND platform_id=?`, platformID)
	list := make([]map[string]any, 0)
	for rows.Next() {
		var relateID, typ, icon int
		var name []byte
		if err := rows.Scan(&relateID, &name, &typ, &icon); err != nil {
			continue
		}
		if typ == 1 && disabledCate[relateID] {
			continue
		}
		if typ == 2 && disabledApp[relateID] {
			continue
		}
		list = append(list, map[string]any{
			"app_cate_id": relateID,
			"name":        lang.Pick(name, locale),
			"type":        typ,
			"icon":        h.imageURL(c, icon),
		})
	}
	return list
}

func (h *Handler) appCategories(c *gin.Context, platformID int, locale string) []map[string]any {
	q := `SELECT id,name,icon,banner,banner_url FROM coin_application_category WHERE enable=1`
	var args []any
	if platformID > 0 {
		q += ` AND platform_id=?`
		args = append(args, platformID)
	}
	q += ` ORDER BY sort`
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), q, args...)
	if err != nil {
		log.Error.Printf("application categories failed platform_id=%d err=%v", platformID, err)
		return []map[string]any{}
	}
	defer rows.Close()
	apps := h.cateApps(c, platformID, locale)
	limit := 5
	if platformID == 26 {
		limit = 15
	}
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, icon, banner int
		var name []byte
		var bannerURL sql.NullString
		if err := rows.Scan(&id, &name, &icon, &banner, &bannerURL); err != nil {
			continue
		}
		group := apps[id]
		if group == nil {
			group = []map[string]any{}
		}
		if len(group) > limit {
			group = group[:limit]
		}
		list = append(list, map[string]any{
			"id":               id,
			"name":             lang.Pick(name, locale),
			"icon_url":         h.imageURL(c, icon),
			"banner_image_url": h.imageURL(c, banner),
			"banner_url":       nullString(bannerURL),
			"apps":             group,
		})
	}
	return list
}

func (h *Handler) cateApps(c *gin.Context, platformID int, locale string) map[int][]map[string]any {
	q := `SELECT c.cate_id,c.app_id,a.name,a.icon,a.type,a.native_url,a.native_login_url,a.h5_url,a.android_url,a.ios_url,a.app_store_url,a.advertise,a.redirect_type,a.open_type_app,a.platform_type,c.sort
		FROM coin_app_cate c JOIN coin_application a ON a.id=c.app_id WHERE a.enable=1`
	var args []any
	if platformID > 0 {
		q += ` AND a.platform_id=?`
		args = append(args, platformID)
	}
	q += ` ORDER BY c.sort`
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), q, args...)
	if err != nil {
		log.Error.Printf("application cate apps failed platform_id=%d err=%v", platformID, err)
		return map[int][]map[string]any{}
	}
	defer rows.Close()
	out := map[int][]map[string]any{}
	for rows.Next() {
		var cateID, appID, icon, typ, redirect, openType, platformType, sort int
		var name []byte
		var native, nativeLogin, h5url, android, ios, store, ad sql.NullString
		if err := rows.Scan(&cateID, &appID, &name, &icon, &typ, &native, &nativeLogin, &h5url, &android, &ios, &store, &ad, &redirect, &openType, &platformType, &sort); err != nil {
			continue
		}
		out[cateID] = append(out[cateID], map[string]any{
			"cate_id": cateID, "app_id": appID, "name": lang.Pick(name, locale), "icon": h.imageURL(c, icon),
			"type": typ, "native_url": nullString(native), "native_login_url": nullString(nativeLogin),
			"h5_url": nullString(h5url), "android_url": nullString(android), "ios_url": nullString(ios),
			"app_store_url": nullString(store), "advertise": nullString(ad), "redirect_type": redirect,
			"open_type_app": openType, "platform_type": platformType, "sort": sort,
			"app_user_num": h.appUserNum(c, appID),
		})
	}
	return out
}

func (h *Handler) appBanners(c *gin.Context, platformID int) []map[string]any {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,banner_url,image_url,title,platform_id,sort,status,type FROM coin_banner_item WHERE platform_id=?`, platformID)
	if err != nil {
		log.Error.Printf("application banner failed platform_id=%d err=%v", platformID, err)
		return []map[string]any{}
	}
	defer rows.Close()
	platform := h.platformBrief(c, platformID)
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, pid, sort, status, typ int
		var banner, image, title sql.NullString
		if err := rows.Scan(&id, &banner, &image, &title, &pid, &sort, &status, &typ); err != nil {
			continue
		}
		list = append(list, map[string]any{
			"id": id, "banner_url": nullString(banner), "image_url": nullString(image), "title": nullString(title),
			"platform_id": pid, "sort": sort, "status": status, "type": typ, "platform": platform,
		})
	}
	return list
}

func (h *Handler) imageURL(c *gin.Context, id int) string {
	if id == 0 {
		return ""
	}
	var file sql.NullString
	err := h.DB.Sources.QueryRowContext(c.Request.Context(), `SELECT file_url FROM coin_image WHERE id=?`, id).Scan(&file)
	if err != nil || !file.Valid || file.String == "" {
		return ""
	}
	return ossPrefix + file.String
}

func (h *Handler) appUserNum(c *gin.Context, id int) int {
	key := "app_id_" + strconv.Itoa(id)
	if h.RDB != nil && h.RDB.App != nil {
		v, err := h.RDB.App.HGet(c.Request.Context(), "application_user_num", key).Result()
		if err == nil && v != "" {
			if n, conv := strconv.Atoi(v); conv == nil {
				return n
			}
		}
		n := 1000 + rand.Intn(1001)
		_ = h.RDB.App.HSet(c.Request.Context(), "application_user_num", key, n).Err()
		return n
	}
	return 0
}

func (h *Handler) idSet(c *gin.Context, query string, platformID int) map[int]bool {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), query, platformID)
	set := map[int]bool{}
	if err != nil {
		return set
	}
	defer rows.Close()
	for rows.Next() {
		var id int
		if err := rows.Scan(&id); err == nil {
			set[id] = true
		}
	}
	return set
}

func (h *Handler) platformBrief(c *gin.Context, id int) map[string]any {
	var name, unit, download, introduce, summary sql.NullString
	var chainID int
	var charge string
	var createAt, updateAt sql.NullTime
	err := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT id,name,chain_id,issue_charge,charge_unit,download_url,introduce,summary,create_time,update_time FROM coin_platform WHERE id=?`, id).
		Scan(&id, &name, &chainID, &charge, &unit, &download, &introduce, &summary, &createAt, &updateAt)
	if err != nil {
		return nil
	}
	item := map[string]any{
		"id": id, "name": nullString(name), "chain_id": chainID, "issue_charge": charge,
		"charge_unit": nullString(unit), "download_url": nullString(download),
		"introduce": nullString(introduce), "summary": nullString(summary),
	}
	if createAt.Valid {
		item["create_time"] = createAt.Time.Format("2006-01-02 15:04:05")
	}
	if updateAt.Valid {
		item["update_time"] = updateAt.Time.Format("2006-01-02 15:04:05")
	}
	return item
}

func scanNamed(rows *sql.Rows, locale string) []map[string]any {
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var name []byte
		if err := rows.Scan(&id, &name); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "name": lang.Pick(name, locale)})
	}
	return list
}

func scanIDName(rows *sql.Rows) []map[string]any {
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var name sql.NullString
		if err := rows.Scan(&id, &name); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "name": nullString(name)})
	}
	return list
}

func scanIDNameSort(rows *sql.Rows) []map[string]any {
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, sort int
		var name sql.NullString
		if err := rows.Scan(&id, &name, &sort); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "name": nullString(name), "sort": sort})
	}
	return list
}

func scanContent(rows *sql.Rows) []map[string]any {
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var title, content sql.NullString
		var createAt sql.NullTime
		if err := rows.Scan(&id, &title, &content, &createAt); err != nil {
			continue
		}
		item := map[string]any{"id": id, "title": nullString(title), "content": nullString(content)}
		if createAt.Valid {
			item["create_time"] = createAt.Time.Format("2006-01-02 15:04:05")
		}
		list = append(list, item)
	}
	return list
}

func bool01(v string) string {
	if v == "1" || strings.EqualFold(v, "true") {
		return "1"
	}
	return "0"
}
