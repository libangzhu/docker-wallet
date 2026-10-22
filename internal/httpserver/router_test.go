package httpserver

import (
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"

	"github.com/gin-gonic/gin"

	"wallet/internal/app"
	"wallet/internal/config"
)

func TestLockedAppRoutesRegistered(t *testing.T) {
	gin.SetMode(gin.TestMode)
	cfg := &config.Config{}
	cfg.HTTP.Addr = "127.0.0.1:8090"
	deps := &app.Deps{Cfg: cfg}
	engine := New(deps, "web/admin")

	paths := []string{
		"/interface/coin/get-coin-by-id",
		"/interface/coin/search",
		"/interface/coin/search-coin-by-name",
		"/interface/coin/coin-index",
		"/interface/coin/get-miner-fee-by-name",
		"/interface/service/fee",
		"/interface/explore/index",
		"/interface/explore/category",
		"/interface/explore/search",
		"/interface/explore/banner",
		"/interface/application/index",
		"/interface/recommend-coin/top",
		"/interface/recommend-coin/index",
		"/interface/recommend-coin/trusteeship",
		"/interface/supported-chain/index",
		"/interface/notice/list",
		"/interface/notice/detail",
		"/interface/banner/index",
		"/interface/coin-platform-banner/index",
		"/h5/coin/get-icon-by-names",
		"/h5/coin/coin-info-by-names",
		"/h5/application/index",
		"/api/user/login",
		"/admin/login",
		"/health",
	}
	for _, p := range paths {
		req := httptest.NewRequest(http.MethodGet, p, nil)
		w := httptest.NewRecorder()
		engine.ServeHTTP(w, req)
		if w.Code == http.StatusNotFound {
			t.Fatalf("route missing: %s", p)
		}
	}
}

func TestLockedAPIValidationContracts(t *testing.T) {
	gin.SetMode(gin.TestMode)
	cfg := &config.Config{}
	cfg.HTTP.Addr = "127.0.0.1:8090"
	engine := New(&app.Deps{Cfg: cfg}, "web/admin")

	cases := []struct {
		name   string
		method string
		path   string
		want   string
	}{
		{"site", http.MethodGet, "/interface/site/index", `"welcome to api!"`},
		{"site-code", http.MethodGet, "/interface/site/index", `"code":"0"`},
		{"health", http.MethodGet, "/health", `"status":"ok"`},
		{"supported-chain", http.MethodGet, "/interface/supported-chain/index", `"参数错误"`},
		{"banner", http.MethodGet, "/interface/banner/index", `"参数错误"`},
		{"notice", http.MethodGet, "/interface/notice/list", `"缺少必要的参数"`},
		{"explore", http.MethodGet, "/interface/explore/index", `"参数错误"`},
		{"recommend-trusteeship", http.MethodGet, "/interface/recommend-coin/trusteeship?recommend=0", `"参数错误"`},
		{"service-fee", http.MethodPost, "/interface/service/fee", `"币种不能为空"`},
		{"h5-icon", http.MethodPost, "/h5/coin/get-icon-by-names", `"PARAMS NOT VALIDATE"`},
		{"api-auth", http.MethodGet, "/api/user/user-info", `"40001"`},
		{"api-auth-msg", http.MethodGet, "/api/user/user-info", `"platform auth error"`},
	}
	for _, tc := range cases {
		req := httptest.NewRequest(tc.method, tc.path, nil)
		w := httptest.NewRecorder()
		engine.ServeHTTP(w, req)
		if w.Code != http.StatusOK {
			t.Fatalf("%s status=%d", tc.name, w.Code)
		}
		if !strings.Contains(w.Body.String(), tc.want) {
			t.Fatalf("%s body=%s want %s", tc.name, w.Body.String(), tc.want)
		}
	}
}
