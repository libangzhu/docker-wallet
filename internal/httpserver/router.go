package httpserver

import (
	"net/http"
	"time"

	"github.com/gin-gonic/gin"

	"wallet/internal/app"
	"wallet/internal/handler/admin"
	"wallet/internal/handler/api"
	"wallet/internal/handler/h5"
	"wallet/internal/handler/walletapi"
	"wallet/internal/platform/log"
)

func New(deps *app.Deps, viewDir string) *gin.Engine {
	gin.SetMode(gin.ReleaseMode)
	r := gin.New()
	r.Use(gin.Recovery())
	r.Use(HeaderMiddleware())
	r.Any("/health", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{"status": "ok"})
	})

	a := api.New(deps)
	registerInterface(r, a)

	h := h5.New(deps)
	registerH5(r, h)

	w := walletapi.New(deps)
	registerWallet(r, w)

	ad := admin.New(deps, viewDir)
	adminGroup := r.Group("/admin")
	adminGroup.Any("/login/login", func(c *gin.Context) {
		if c.Request.Method == http.MethodPost {
			ad.Login(c)
			return
		}
		ad.LoginPage(c)
	})
	adminGroup.Any("/login", func(c *gin.Context) {
		c.Redirect(http.StatusFound, "/admin/login/login")
	})
	protected := adminGroup.Group("")
	protected.Use(ad.AuthRequired())
	protected.Any("/:controller", func(c *gin.Context) {
		c.Params = append(c.Params, gin.Param{Key: "action", Value: "index"})
		ad.Dispatch(c)
	})
	protected.Any("/:controller/:action", ad.Dispatch)
	return r
}

func Listen(deps *app.Deps, viewDir string) *http.Server {
	engine := New(deps, viewDir)
	srv := &http.Server{
		Addr:         deps.Cfg.HTTP.Addr,
		Handler:      engine,
		ReadTimeout:  deps.Cfg.HTTP.ReadTimeout,
		WriteTimeout: deps.Cfg.HTTP.WriteTimeout,
	}
	if srv.ReadTimeout == 0 {
		srv.ReadTimeout = 60 * time.Second
	}
	if srv.WriteTimeout == 0 {
		srv.WriteTimeout = 60 * time.Second
	}
	log.Info.Printf("http listen %s", srv.Addr)
	return srv
}

func registerInterface(r *gin.Engine, a *api.Handler) {
	g := r.Group("/interface")
	g.Any("/site/index", a.SiteIndex)
	g.Any("/site/error", a.SiteError)
	g.Any("/site", a.SiteIndex)

	g.Any("/coin/get-coin-by-id", a.GetCoinByID)
	g.Any("/coin/get-rec-list", a.GetRecList)
	g.Any("/coin/get-new-rec-list", a.GetNewRecList)
	g.Any("/coin/get-miner-fee-by-name", a.GetMinerFeeByName)
	g.Any("/coin/coin-index", a.CoinIndex)
	g.Any("/coin/search", a.Search)
	g.Any("/coin/search-coin-by-name", a.SearchCoinByName)
	g.Any("/coin/transaction", a.Transaction)
	g.Any("/coin/get-brower-by-platform", a.GetBrowerByPlatform)
	g.Any("/coin/get-with-hold", a.GetWithHold)

	g.Any("/service/fee", a.ServiceFee)
	g.Any("/service/chain", a.ServiceChain)
	g.Any("/service/coin-tickers", a.ServiceCoinTickers)

	g.Any("/explore/index", a.ExploreIndex)
	g.Any("/explore", a.ExploreIndex)
	g.Any("/explore/category", a.ExploreCategory)
	g.Any("/explore/search", a.ExploreSearch)
	g.Any("/explore/banner", a.ExploreBanner)

	g.Any("/banner/index", a.BannerIndex)
	g.Any("/banner", a.BannerIndex)
	g.Any("/coin-platform-banner/index", a.CoinPlatformBannerIndex)
	g.Any("/coin-platform-banner", a.CoinPlatformBannerIndex)

	g.Any("/notice/list", a.NoticeList)
	g.Any("/notice/detail", a.NoticeDetail)
	g.Any("/notice/index", a.NoticeIndex)
	g.Any("/notice", a.NoticeIndex)

	g.Any("/recommend-coin/top", a.RecommendTop)
	g.Any("/recommend-coin/index", a.RecommendIndex)
	g.Any("/recommend-coin", a.RecommendIndex)
	g.Any("/recommend-coin/trusteeship", a.RecommendTrusteeship)
	g.Any("/supported-chain/index", a.SupportedChainIndex)
	g.Any("/supported-chain", a.SupportedChainIndex)

	g.Any("/application/index", a.ApplicationIndex)
	g.Any("/application", a.ApplicationIndex)
	g.Any("/application/cate-app-info", a.ApplicationCateAppInfo)
	g.Any("/application/app-info", a.ApplicationAppInfo)
	g.Any("/application/add-user-num", a.ApplicationAddUserNum)
	g.Any("/application/rank-list", a.ApplicationRankList)
	g.Any("/application/app-search", a.ApplicationAppSearch)
	g.Any("/application/get-apps-by-cate", a.ApplicationGetAppsByCate)
	g.Any("/application/get-hot-search", a.ApplicationGetHotSearch)

	g.Any("/ticker/index", a.TickerIndex)
	g.Any("/ticker", a.TickerIndex)
	g.Any("/ticker/optional", a.TickerOptional)
	g.Any("/market/index", a.MarketIndex)
	g.Any("/market", a.MarketIndex)
	g.Any("/market/detail", a.MarketDetail)
	g.Any("/market/exchange", a.MarketExchange)
	g.Any("/supported-symbol/search", a.SupportedSymbolSearch)

	g.Any("/wallet/token-category", a.WalletTokenCategory)
	g.Any("/wallet/info", a.WalletInfo)
	g.Any("/wallet/get-balance", a.WalletGetBalance)
	g.Any("/wallet/transfer", a.WalletTransfer)
	g.Any("/wallet/game-trade-update", a.WalletGameTradeUpdate)
	g.Any("/wallet-chain/chains", a.WalletChainChains)
	g.Any("/wallet-chain/chain", a.WalletChainChain)
	g.Any("/wallet-chain/detail", a.WalletChainDetail)
	g.Any("/wallet-coin/index", a.WalletCoinIndex)
	g.Any("/wallet-coin", a.WalletCoinIndex)

	g.Any("/trade/add-memo", a.TradeAddMemo)
	g.Any("/trade/update-memo", a.TradeUpdateMemo)
	g.Any("/trade/get-memo", a.TradeGetMemo)
	g.Any("/trade/sync-to-db", a.TradeSyncToDb)
	g.Any("/trade/address-is-exist", a.TradeAddressIsExist)
	g.Any("/trade/token-transfer", a.TradeTokenTransfer)

	g.Any("/bill/insert", a.BillInsert)
	g.Any("/bill/bill-list", a.BillList)
	g.Any("/member/index", a.MemberIndex)
	g.Any("/member", a.MemberIndex)
	g.Any("/lock/index", a.LockIndex)
	g.Any("/lock", a.LockIndex)
	g.Any("/order/index", a.OrderIndex)
	g.Any("/order", a.OrderIndex)

	g.Any("/cross-chain/transfer", a.CrossChainTransfer)
	g.Any("/cross-chain/fix-transfer", a.CrossChainFixTransfer)
	g.Any("/cross-chain/transfer-status", a.CrossChainTransferStatus)

	g.Any("/issue-coin/validate", a.IssueCoinValidate)
	g.Any("/issue-coin/apply", a.IssueCoinApply)
	g.Any("/issue-coin/apply-list", a.IssueCoinApplyList)
	g.Any("/issue-coin/apply-detail", a.IssueCoinApplyDetail)
	g.Any("/issue-coin/send-transfer", a.IssueCoinSendTransfer)
	g.Any("/issue-coin/verify", a.IssueCoinVerify)
	g.Any("/issue-coin/revoke", a.IssueCoinRevoke)
	g.Any("/issue-coin/symbol-validate", a.IssueCoinSymbolValidate)
	g.Any("/issue-chain/index", a.IssueChainIndex)
	g.Any("/issue-chain", a.IssueChainIndex)

	g.Any("/air-drop/index", a.AirDropIndex)
	g.Any("/air-drop", a.AirDropIndex)
	g.Any("/platform-coins/air-drop", a.PlatformCoinsAirDrop)
	g.Any("/platform-coins/index", a.PlatformCoinsIndex)
	g.Any("/platform-coins", a.PlatformCoinsIndex)
	g.Any("/platform-coins/relation", a.PlatformCoinsRelation)

	g.Any("/article/list", a.ArticleList)
	g.Any("/feedback/index", a.FeedbackIndex)
	g.Any("/app/update", a.AppUpdate)
	g.Any("/app/new-update", a.AppNewUpdate)
	g.Any("/app/update-version", a.AppUpdateVersion)
	g.Any("/oss/upload", a.OssUpload)
	g.Any("/client-params/index", a.ClientParamsIndex)
	g.Any("/client-params", a.ClientParamsIndex)
	g.Any("/validate/index", a.ValidateIndex)
	g.Any("/game/index", a.GameIndex)
	g.Any("/game-bet/index", a.GameBetIndex)
	g.Any("/coin-dog/article", a.CoinDogArticle)
	g.Any("/coin-dog/article-info", a.CoinDogArticleInfo)
	g.Any("/coin-dog/live", a.CoinDogLive)
	g.Any("/coin-dog/article-banner", a.CoinDogArticleBanner)
	g.Any("/visit-statistics/index", a.VisitStatisticsIndex)
	g.Any("/visit-statistics/daily-times", a.VisitStatisticsIndex)
	g.Any("/visit-statistics/dapp-result", a.VisitStatisticsIndex)
	g.Any("/sec-kill/index", a.SecKillIndex)
	g.Any("/sec-kill/test", a.SecKillIndex)
	g.Any("/member/add-one", a.MemberIndex)
	g.Any("/validate/black-list", a.ValidateIndex)
	g.Any("/feedback/add", a.FeedbackIndex)
	g.Any("/lock/lock-creator", a.LockIndex)
	g.Any("/lock/search", a.LockIndex)
	g.Any("/lock/delete-creator", a.LockIndex)
	g.Any("/lock/lock-block", a.LockIndex)
	g.Any("/lock/lock-beneficiary", a.LockIndex)
	g.Any("/order/order-list", a.OrderIndex)
	g.Any("/order/order-status", a.OrderIndex)
	g.Any("/game/save-address-and-nickname", a.GameIndex)
	g.Any("/game/get-nickname-by-address", a.GameIndex)
	g.Any("/game/save-game-info", a.GameIndex)
	g.Any("/game/get-nickname-by-address-arr", a.GameIndex)
	g.Any("/game-bet/bet-status", a.GameBetIndex)
	g.Any("/issue-chain/manual-review-status", a.IssueChainIndex)
	g.Any("/service/coin-index", a.ServiceCoinTickers)
	g.Any("/ticker/remove-optional", a.TickerOptional)
	g.Any("/ticker/hot-ticker", a.TickerIndex)
	g.Any("/ticker/currency", a.TickerIndex)
	g.Any("/wallet-coin/search", a.WalletCoinIndex)
	g.Any("/wallet-coin/get-coin-by-id", a.GetCoinByID)
	g.Any("/air-drop/validate-identifier", a.AirDropIndex)
	g.Any("/air-drop/apply", a.AirDropIndex)
	g.Any("/air-drop/income", a.AirDropIndex)
	g.Any("/air-drop/validate", a.AirDropIndex)
	g.Any("/air-drop/info", a.AirDropIndex)
	g.Any("/air-drop/batch-draw", a.AirDropIndex)
	g.Any("/air-drop/draw", a.AirDropIndex)
	g.Any("/air-drop/draw-bak", a.AirDropIndex)
	g.Any("/air-drop/rule", a.AirDropIndex)
	g.Any("/air-drop/game-trade", a.AirDropIndex)
	g.Any("/air-drop/game-trade-update", a.AirDropIndex)
	g.Any("/air-drop/get-balance", a.AirDropIndex)
}

func registerH5(r *gin.Engine, h *h5.Handler) {
	g := r.Group("/h5")
	g.Any("/site/index", h.SiteIndex)
	g.Any("/site", h.SiteIndex)
	g.Any("/coin/get-icon-by-names", h.GetIconByNames)
	g.Any("/coin/coin-info-by-names", h.CoinInfoByNames)
	g.Any("/application/index", h.ApplicationIndex)
	g.Any("/application", h.ApplicationIndex)
	g.Any("/application/get-apps-by-cate", h.GetAppsByCate)
	g.Any("/application/app-info", h.AppInfo)
	g.Any("/lottery/lottery-info", h.LotteryInfo)
	g.Any("/lottery/new-lottery-info", h.NewLotteryInfo)
	g.Any("/guess/get-new-bee-chance", h.GetNewBeeChance)
	g.Any("/guess/get-vote-record-by-address", h.GetVoteRecordByAddress)
	g.Any("/guodun/get-user-assets", h.GetUserAssets)
	g.Any("/guodun/request-coin", h.RequestCoin)
	g.Any("/guodun/bill-list", h.BillList)
}

func registerWallet(r *gin.Engine, w *walletapi.Handler) {
	g := r.Group("/api")
	g.Any("/user/login", w.UserLogin)
	g.Any("/user/user-sync", w.UserSync)
	auth := g.Group("")
	auth.Use(w.AuthRequired())
	g = auth
	g.Any("/user/user-info", w.UserInfo)
	g.Any("/user/user-list", w.UserList)
	g.Any("/user/manual-list", w.ManualList)
	g.Any("/user/verify", w.Verify)
	g.Any("/user/edit-pwd", w.EditPwd)
	g.Any("/user/add", w.Add)
	g.Any("/wallet/list", w.WalletList)
	g.Any("/wallet/wallet-ballance", w.WalletBallance)
	g.Any("/wallet/user-asset", w.UserAsset)
	g.Any("/wallet/currency", w.Currency)
	g.Any("/asset/index", w.AssetIndex)
	g.Any("/monitor/list", w.MonitorList)
	g.Any("/coin-relation/index", w.CoinRelationIndex)
	g.Any("/banner/type", w.BannerType)
	g.Any("/banner/index", w.BannerIndex)
	g.Any("/banner/update", w.BannerUpdate)
	g.Any("/banner/remove", w.BannerRemove)
	g.Any("/notice/index", w.NoticeIndex)
	g.Any("/notice/update", w.NoticeUpdate)
	g.Any("/notice/remove", w.NoticeRemove)
	g.Any("/explore-app/category", w.ExploreAppCategory)
	g.Any("/explore-app/category-update", w.ExploreAppCategoryUpdate)
	g.Any("/explore-app/category-remove", w.ExploreAppCategoryRemove)
	g.Any("/explore-app/apps", w.ExploreAppApps)
	g.Any("/explore-app/app-update", w.ExploreAppAppUpdate)
	g.Any("/explore-app/app-remove", w.ExploreAppAppRemove)
	g.Any("/explore-banner/index", w.ExploreBannerIndex)
	g.Any("/explore-banner/update", w.ExploreBannerUpdate)
	g.Any("/explore-banner/remove", w.ExploreBannerRemove)
	g.Any("/air-drop/get-balance", w.AirDropGetBalance)
	g.Any("/air-drop/expenses", w.AirDropExpenses)
	g.Any("/air-drop/index", w.AirDropIndex)
	g.Any("/air-drop/apply", w.AirDropApply)
	g.Any("/air-drop/stop", w.AirDropStop)
	g.Any("/issue-coin/apply-list", w.IssueCoinApplyList)
	g.Any("/issue-coin/apply-detail", w.IssueCoinApplyDetail)
	g.Any("/issue-coin/verify", w.IssueCoinVerify)
	g.Any("/issue-chain/index", w.IssueChainIndex)
	g.Any("/issue-chain/chain-info", w.IssueChainChainInfo)
	g.Any("/issue-chain/set-charge", w.IssueChainSetCharge)
	g.Any("/issue-chain/manage-review", w.IssueChainManageReview)
	g.Any("/issue-chain/get-tokens", w.IssueChainGetTokens)
	g.Any("/flash/flash-list-config", w.FlashFlashListConfig)
	g.Any("/flash/flash-default-config", w.FlashFlashDefaultConfig)
	g.Any("/flash/flash-config", w.FlashFlashConfig)
	g.Any("/flash/flash-clone-config", w.FlashFlashCloneConfig)
	g.Any("/flash/index", w.FlashIndex)
	g.Any("/oss/upload", w.OssUpload)
}
