package h5

import (
	"database/sql"

	"github.com/gin-gonic/gin"

	"wallet/internal/app"
	"wallet/internal/platform/lang"
	"wallet/internal/platform/log"
	"wallet/internal/platform/req"
	"wallet/internal/platform/response"
)

type Handler struct {
	*app.Deps
}

func New(d *app.Deps) *Handler {
	return &Handler{Deps: d}
}

func (h *Handler) SiteIndex(c *gin.Context) {
	response.WriteH5(c, 0, "", gin.H{"msg": "welcome"})
}

func (h *Handler) GetIconByNames(c *gin.Context) {
	names := req.ParamStringSlice(c, "names")
	if len(names) == 0 {
		if v := req.Param(c, "names", ""); v != "" {
			names = []string{v}
		}
	}
	if len(names) == 0 {
		response.WriteH5(c, 1, "", nil)
		return
	}
	data := map[string]string{}
	for _, name := range names {
		var icon sql.NullString
		err := h.DB.Sources.QueryRowContext(c.Request.Context(), `SELECT icon FROM coin WHERE name=? LIMIT 1`, name).Scan(&icon)
		if err != nil {
			continue
		}
		data[name] = nullString(icon)
	}
	response.WriteH5(c, 0, "", data)
}

func (h *Handler) CoinInfoByNames(c *gin.Context) {
	names := req.ParamStringSlice(c, "names")
	if len(names) == 0 {
		if v := req.Param(c, "names", ""); v != "" {
			names = []string{v}
		}
	}
	if len(names) == 0 {
		response.WriteH5(c, 1, "", nil)
		return
	}
	platform := req.Param(c, "platform", "")
	list := make([]map[string]any, 0)
	for _, name := range names {
		q := `SELECT id,sid,icon,name,nickname,platform,chain,address,introduce FROM coin WHERE name=?`
		args := []any{name}
		if platform != "" {
			q += ` AND platform=?`
			args = append(args, platform)
		}
		q += ` LIMIT 1`
		var id int
		var sid, icon, n, nickname, plat, chain, address, introduce sql.NullString
		if err := h.DB.Sources.QueryRowContext(c.Request.Context(), q, args...).Scan(&id, &sid, &icon, &n, &nickname, &plat, &chain, &address, &introduce); err != nil {
			continue
		}
		locale := req.Lang(c)
		list = append(list, map[string]any{
			"id": id, "sid": nullString(sid), "icon": nullString(icon), "name": nullString(n),
			"nickname": lang.PickString(nullString(nickname), locale), "platform": nullString(plat),
			"chain": nullString(chain), "contract_address": nullString(address),
			"introduce": lang.PickString(nullString(introduce), locale),
		})
	}
	response.WriteH5(c, 0, "", list)
}

func (h *Handler) ApplicationIndex(c *gin.Context) {
	locale := req.Lang(c)
	response.WriteH5(c, 0, "", gin.H{
		"recommend":     []any{},
		"cate_app_data": h.categories(c, locale),
		"rank_list":     []any{},
	})
}

func (h *Handler) GetAppsByCate(c *gin.Context) {
	response.WriteH5(c, 0, "", []any{})
}

func (h *Handler) AppInfo(c *gin.Context) {
	response.WriteH5(c, 0, "", gin.H{})
}

func (h *Handler) LotteryInfo(c *gin.Context) {
	response.WriteH5(c, 0, "", gin.H{})
}

func (h *Handler) NewLotteryInfo(c *gin.Context) {
	response.WriteH5(c, 0, "", gin.H{})
}

func (h *Handler) GetNewBeeChance(c *gin.Context) {
	response.WriteH5(c, 0, "", gin.H{})
}

func (h *Handler) GetVoteRecordByAddress(c *gin.Context) {
	response.WriteH5(c, 0, "", []any{})
}

func (h *Handler) GetUserAssets(c *gin.Context) {
	response.WriteH5(c, 0, "", []any{})
}

func (h *Handler) RequestCoin(c *gin.Context) {
	response.WriteH5(c, 0, "", gin.H{})
}

func (h *Handler) BillList(c *gin.Context) {
	response.WriteH5(c, 0, "", []any{})
}

func (h *Handler) categories(c *gin.Context, locale string) []map[string]any {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), `SELECT id,name FROM coin_application_category ORDER BY sort`)
	if err != nil {
		log.Error.Printf("h5 application categories failed err=%v", err)
		return []map[string]any{}
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var name []byte
		if err := rows.Scan(&id, &name); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "name": lang.Pick(name, locale), "apps": []any{}})
	}
	return list
}

func nullString(ns sql.NullString) string {
	if ns.Valid {
		return ns.String
	}
	return ""
}
