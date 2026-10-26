package api

import (
	"database/sql"
	"fmt"
	"strings"

	"github.com/gin-gonic/gin"

	"wallet/internal/platform/lang"
	"wallet/internal/platform/log"
	"wallet/internal/platform/req"
	"wallet/internal/platform/response"
)

func (h *Handler) ServiceFee(c *gin.Context) {
	name := req.Param(c, "name", "")
	if name == "" {
		response.WriteAPI(c, gin.H{"code": 1, "data": []any{}, "msg": "币种不能为空"})
		return
	}
	var id, typ int
	var platform, fee string
	var createAt, updateAt sql.NullTime
	err := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT id,platform,type,fee,create_at,update_at FROM miner_fee WHERE platform=? AND type=2 LIMIT 1`, name).
		Scan(&id, &platform, &typ, &fee, &createAt, &updateAt)
	if err != nil {
		log.Error.Printf("service/fee empty name=%s err=%v", name, err)
		response.WriteAPI(c, gin.H{"code": 1, "data": []any{}, "msg": "旷工费未设置"})
		return
	}
	out := map[string]any{"id": id, "platform": platform, "type": typ, "fee": fee}
	if createAt.Valid {
		out["create_at"] = createAt.Time.Format("2006-01-02 15:04:05")
	} else {
		out["create_at"] = nil
	}
	if updateAt.Valid {
		out["update_at"] = updateAt.Time.Format("2006-01-02 15:04:05")
	} else {
		out["update_at"] = nil
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": out, "msg": "旷工费获取成功"})
}

func (h *Handler) ServiceChain(c *gin.Context) {
	currency := req.Param(c, "currency", "")
	var name, nickname, chain sql.NullString
	err := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT name,nickname,chain FROM coin WHERE name=? LIMIT 1`, currency).Scan(&name, &nickname, &chain)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "币种不存在"})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{
		"name": nullString(name), "nickname": nullString(nickname), "chain": nullString(chain),
	}})
}

func (h *Handler) ServiceCoinTickers(c *gin.Context) {
	names := req.ParamStringSlice(c, "names")
	if len(names) == 0 {
		response.WriteAPI(c, gin.H{"code": 1, "data": []any{}, "msg": "币种不能为空"})
		return
	}
	data := map[string]any{}
	for _, name := range names {
		name = strings.ToUpper(name)
		q := quotation(c.Request.Context(), h.RDB, name)
		if q == nil {
			data[name] = gin.H{"rmb": 0, "last": 0, "low": 0, "high": 0, "open": 0, "usd": 0}
			continue
		}
		data[name] = q
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": data})
}

func (h *Handler) ExploreIndex(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	cats, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,name,sort,`+"`limit`"+`,style,platform_id FROM explore_app_category WHERE platform_id=? AND status=1 ORDER BY sort`, platformID)
	if err != nil {
		log.Error.Printf("explore/index category failed platform_id=%s err=%v", platformID, err)
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	defer cats.Close()
	locale := req.Lang(c)
	var data []map[string]any
	for cats.Next() {
		var id, sort, limit, style, pid int
		var name []byte
		if err := cats.Scan(&id, &name, &sort, &limit, &style, &pid); err != nil {
			continue
		}
		apps := h.exploreApps(c, id, limit, locale)
		if len(apps) == 0 {
			continue
		}
		data = append(data, map[string]any{
			"id": id, "name": lang.Pick(name, locale), "style": style, "status": 1, "apps": apps,
		})
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": data})
}

func (h *Handler) ExploreCategory(c *gin.Context) {
	platformID := req.PlatformID(c)
	categoryID := req.ParamInt(c, "id", 0)
	if platformID == "" || categoryID == 0 {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	var name []byte
	var style int
	err := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT name,style FROM explore_app_category WHERE id=? AND platform_id=?`, categoryID, platformID).Scan(&name, &style)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	locale := req.Lang(c)
	apps := h.exploreApps(c, categoryID, 999, locale)
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": []map[string]any{{
		"name": lang.Pick(name, locale), "status": 1, "apps": apps,
	}}})
}

func (h *Handler) ExploreSearch(c *gin.Context) {
	platformID := req.PlatformID(c)
	ids := req.Param(c, "ids", "")
	if platformID == "" || ids == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	idArr := strings.Split(ids, ",")
	placeholders := make([]string, 0, len(idArr))
	args := make([]any, 0, len(idArr))
	for _, id := range idArr {
		id = strings.TrimSpace(id)
		if id == "" {
			continue
		}
		placeholders = append(placeholders, "?")
		args = append(args, id)
	}
	if len(placeholders) == 0 {
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	q := fmt.Sprintf(`SELECT id,name,icon,type,app_url,slogan,email,phone,real_name FROM explore_app WHERE status=1 AND id IN (%s) ORDER BY FIELD(id,%s) LIMIT 4`,
		strings.Join(placeholders, ","), ids)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), q, args...)
	if err != nil {
		log.Error.Printf("explore/search failed ids=%s err=%v", ids, err)
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	defer rows.Close()
	locale := req.Lang(c)
	list := h.scanExploreApps(rows, locale)
	if len(list) == 0 {
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": list})
}

func (h *Handler) ExploreBanner(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT banner_url,image_url,title FROM coin_banner_item WHERE platform_id=?`, platformID)
	if err != nil {
		log.Error.Printf("explore/banner failed platform_id=%s err=%v", platformID, err)
		response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var banner, image, title sql.NullString
		if err := rows.Scan(&banner, &image, &title); err != nil {
			continue
		}
		list = append(list, map[string]any{"banner_url": nullString(banner), "image_url": nullString(image), "title": nullString(title)})
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": list})
}

func (h *Handler) BannerIndex(c *gin.Context) {
	platformID := req.PlatformID(c)
	typ := req.ParamInt(c, "type", 0)
	if platformID == "" || typ == 0 {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT banner_url,image_url,title,sort FROM coin_banner_item WHERE platform_id=? AND type=? AND status=1 ORDER BY sort`,
		platformID, typ)
	if err != nil {
		log.Error.Printf("banner/index failed platform_id=%s type=%d err=%v", platformID, typ, err)
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var banner, image, title sql.NullString
		var sort int
		if err := rows.Scan(&banner, &image, &title, &sort); err != nil {
			continue
		}
		list = append(list, map[string]any{
			"banner_url": nullString(banner), "image_url": nullString(image), "title": nullString(title), "sort": sort,
		})
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": list})
}

func (h *Handler) CoinPlatformBannerIndex(c *gin.Context) {
	platformID := req.Param(c, "platform_id", "")
	typ := req.Param(c, "type", "")
	if platformID == "" || typ == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,image_url,platform_id,type FROM coin_platform_banner WHERE platform_id=? AND type=?`, platformID, typ)
	if err != nil {
		log.Error.Printf("coin-platform-banner failed platform_id=%s type=%s err=%v", platformID, typ, err)
		response.WriteAPI(c, gin.H{"code": 0, "data": nil, "msg": "success"})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, pid, t int
		var image sql.NullString
		if err := rows.Scan(&id, &image, &pid, &t); err != nil {
			continue
		}
		list = append(list, map[string]any{"id": id, "image_url": nullString(image), "platform_id": pid, "type": t})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list, "msg": "success"})
}

func (h *Handler) NoticeList(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "缺少必要的参数", "data": nil})
		return
	}
	typ := req.Param(c, "type", "")
	page := req.ParamInt(c, "page", 1)
	size := req.ParamInt(c, "size", 10)
	if page < 1 {
		page = 1
	}
	where := "status=1 AND platform_id=?"
	args := []any{platformID}
	if typ != "" && typ != "0" {
		where += " AND type=?"
		args = append(args, typ)
	}
	var total int
	_ = h.DB.Coin.QueryRowContext(c.Request.Context(), `SELECT COUNT(*) FROM (SELECT title FROM notice WHERE `+where+` GROUP BY title) t`, args...).Scan(&total)
	q := `SELECT ANY_VALUE(id),title,ANY_VALUE(content),ANY_VALUE(author),ANY_VALUE(type),ANY_VALUE(is_top),ANY_VALUE(create_time),ANY_VALUE(update_time) FROM notice WHERE ` + where + ` GROUP BY title ORDER BY MAX(create_time) DESC LIMIT ? OFFSET ?`
	args = append(args, size, (page-1)*size)
	rows, err := h.DB.Coin.QueryContext(c.Request.Context(), q, args...)
	if err != nil {
		log.Error.Printf("notice/list failed platform_id=%s err=%v", platformID, err)
		response.WriteAPI(c, gin.H{"code": -1, "msg": "查询失败", "data": nil})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, typN, isTop int
		var title, content, author sql.NullString
		var createAt, updateAt sql.NullTime
		if err := rows.Scan(&id, &title, &content, &author, &typN, &isTop, &createAt, &updateAt); err != nil {
			continue
		}
		item := map[string]any{
			"id": id, "title": nullString(title), "content": nullString(content), "author": nullString(author),
			"type": typN, "is_top": isTop,
		}
		if createAt.Valid {
			item["create_time"] = createAt.Time.Format("2006-01-02 15:04:05")
		} else {
			item["create_time"] = nil
		}
		if updateAt.Valid {
			item["update_time"] = updateAt.Time.Format("2006-01-02 15:04:05")
		} else {
			item["update_time"] = nil
		}
		list = append(list, item)
	}
	pageCount := 0
	if size > 0 {
		pageCount = (total + size - 1) / size
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{
		"list": list,
		"page": gin.H{"pageCount": pageCount, "pageSize": size, "currentPage": page},
	}})
}

func (h *Handler) NoticeDetail(c *gin.Context) {
	platformID := req.PlatformID(c)
	id := req.ParamInt(c, "id", 0)
	if platformID == "" || id == 0 {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "缺少必要的参数", "data": nil})
		return
	}
	var title, content, author sql.NullString
	var typ int
	var createAt sql.NullTime
	err := h.DB.Coin.QueryRowContext(c.Request.Context(),
		`SELECT title,content,author,type,create_time FROM notice WHERE id=? AND platform_id=?`, id, platformID).
		Scan(&title, &content, &author, &typ, &createAt)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": nil})
		return
	}
	out := map[string]any{"title": nullString(title), "content": nullString(content), "author": nullString(author), "type": typ}
	if createAt.Valid {
		out["create_time"] = createAt.Time.Format("2006-01-02 15:04:05")
	} else {
		out["create_time"] = nil
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": out})
}

func (h *Handler) NoticeIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}

func (h *Handler) RecommendTop(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	typ := req.ParamInt(c, "type", 1)
	var catID int
	var name []byte
	err := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT id,name FROM wallet_recommend_category WHERE wallet_type=? AND platform_id=? ORDER BY sort LIMIT 1`,
		typ, platformID).Scan(&catID, &name)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	items := h.recommendItems(c, catID, true)
	response.WriteAPI(c, gin.H{"code": 0, "data": items, "msg": "success"})
}

func (h *Handler) RecommendIndex(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	typ := req.ParamInt(c, "type", 1)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,name FROM wallet_recommend_category WHERE wallet_type=? AND platform_id=? AND sort>1 ORDER BY sort`,
		typ, platformID)
	if err != nil {
		log.Error.Printf("recommend-coin/index failed platform_id=%s err=%v", platformID, err)
		response.WriteAPI(c, gin.H{"code": 0, "data": nil, "msg": "success"})
		return
	}
	defer rows.Close()
	locale := req.Lang(c)
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id int
		var name []byte
		if err := rows.Scan(&id, &name); err != nil {
			continue
		}
		list = append(list, map[string]any{
			"name":  lang.Pick(name, locale),
			"items": h.recommendItems(c, id, false),
		})
	}
	if chains := h.recommendChainItems(c, platformID); len(chains) > 0 {
		list = append([]map[string]any{{"name": "主链", "items": chains}}, list...)
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list, "msg": "success"})
}

func (h *Handler) RecommendTrusteeship(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	recommend := req.Param(c, "recommend", "1")
	if recommend != "1" {
		response.WriteAPI(c, gin.H{"code": 1, "msg": "数据为空", "data": nil})
		return
	}
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT cid FROM wallet_recommend_coin WHERE type=2 AND platform_id=?`, platformID)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 1, "msg": "数据为空", "data": nil})
		return
	}
	defer rows.Close()
	locale := req.Lang(c)
	list := make([]map[string]any, 0)
	for rows.Next() {
		var cid int
		if err := rows.Scan(&cid); err != nil {
			continue
		}
		item, err := h.loadCoinByID(c, cid, locale)
		if err != nil {
			continue
		}
		if platformID == "2" {
			item["low"], item["high"], item["last"], item["rmb"], item["usd"] = 0, 0, 0, 0, 0
			item["chain_quotation"] = nil
			item["chain_rmb"] = 0
			item["chain_usd"] = 0
		}
		item["type"] = 2
		item["platform_id"] = platformID
		item["sort"] = 1
		item["recommend"] = 1
		item["coin"] = item["name"]
		list = append(list, item)
	}
	if len(list) == 0 {
		response.WriteAPI(c, gin.H{"code": 1, "msg": "数据为空", "data": nil, "count": 0})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list, "msg": "success", "count": len(list)})
}

func (h *Handler) SupportedChainIndex(c *gin.Context) {
	platformID := req.PlatformID(c)
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	items := h.supportedChainItems(c, platformID)
	if items == nil {
		response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": items})
}

func (h *Handler) recommendChainItems(c *gin.Context, platformID string) []map[string]any {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT c.id,c.icon,c.name,c.platform,c.chain,c.treaty,c.nickname,c.introduce FROM coin_supported_chain s LEFT JOIN coin c ON c.id=s.coin_id WHERE s.platform_id=?`,
		platformID)
	if err != nil {
		log.Error.Printf("recommend chain query failed platform_id=%s err=%v", platformID, err)
		return nil
	}
	defer rows.Close()
	locale := req.Lang(c)
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, treaty sql.NullInt64
		var icon, name, platform, chain, nickname, introduce sql.NullString
		if err := rows.Scan(&id, &icon, &name, &platform, &chain, &treaty, &nickname, &introduce); err != nil {
			continue
		}
		item := map[string]any{
			"id": "", "icon": "", "name": "", "platform": "", "chain": "", "treaty": "", "nickname": "", "introduce": "",
		}
		if id.Valid {
			item["id"] = id.Int64
		}
		item["icon"] = nullString(icon)
		item["name"] = nullString(name)
		item["platform"] = nullString(platform)
		item["chain"] = nullString(chain)
		if treaty.Valid {
			item["treaty"] = treaty.Int64
		}
		item["nickname"] = lang.PickString(nullString(nickname), locale)
		item["introduce"] = lang.PickString(nullString(introduce), locale)
		list = append(list, item)
	}
	return list
}

func (h *Handler) supportedChainItems(c *gin.Context, platformID string) []map[string]any {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT c.id,c.name,c.icon,c.nickname,c.chain,c.platform,c.treaty FROM coin_supported_chain s LEFT JOIN coin c ON c.id=s.coin_id WHERE s.platform_id=?`,
		platformID)
	if err != nil {
		log.Error.Printf("supported-chain query failed platform_id=%s err=%v", platformID, err)
		return nil
	}
	defer rows.Close()
	locale := req.Lang(c)
	var list []map[string]any
	for rows.Next() {
		var id, treaty sql.NullInt64
		var name, icon, nickname, chain, platform sql.NullString
		if err := rows.Scan(&id, &name, &icon, &nickname, &chain, &platform, &treaty); err != nil {
			continue
		}
		item := map[string]any{
			"id": "", "name": "", "icon": "", "nickname": "", "chain": "", "platform": "", "treaty": "",
			"rmb": 0, "usd": 0,
		}
		if id.Valid {
			item["id"] = id.Int64
		}
		item["name"] = nullString(name)
		item["icon"] = nullString(icon)
		item["nickname"] = lang.PickString(nullString(nickname), locale)
		item["chain"] = nullString(chain)
		item["platform"] = nullString(platform)
		if treaty.Valid {
			item["treaty"] = treaty.Int64
		}
		if q := quotation(c.Request.Context(), h.RDB, nullString(name)); q != nil {
			if v, ok := q["rmb"]; ok {
				item["rmb"] = v
			}
			if v, ok := q["usd"]; ok {
				item["usd"] = v
			}
		}
		list = append(list, item)
	}
	return list
}

func (h *Handler) recommendItems(c *gin.Context, categoryID int, order bool) []map[string]any {
	q := `SELECT cid FROM wallet_recommend_coin WHERE category_id=?`
	if order {
		q += ` ORDER BY sort`
	}
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), q, categoryID)
	if err != nil {
		return []map[string]any{}
	}
	defer rows.Close()
	locale := req.Lang(c)
	items := make([]map[string]any, 0)
	for rows.Next() {
		var cid int
		if err := rows.Scan(&cid); err != nil {
			continue
		}
		var id, treaty int
		var icon, name, platform, chain, nickname, introduce sql.NullString
		err := h.DB.Sources.QueryRowContext(c.Request.Context(),
			`SELECT id,icon,name,platform,chain,treaty,nickname,introduce FROM coin WHERE id=?`, cid).
			Scan(&id, &icon, &name, &platform, &chain, &treaty, &nickname, &introduce)
		if err != nil {
			continue
		}
		items = append(items, map[string]any{
			"id": id, "icon": nullString(icon), "name": nullString(name),
			"platform": nullString(platform), "chain": nullString(chain), "treaty": treaty,
			"nickname":  lang.PickString(nullString(nickname), locale),
			"introduce": lang.PickString(nullString(introduce), locale),
		})
	}
	return items
}

func (h *Handler) exploreApps(c *gin.Context, categoryID, limit int, locale string) []map[string]any {
	if limit <= 0 {
		limit = 1
	}
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,name,icon,type,app_url,slogan,email,phone,real_name FROM explore_app WHERE app_category_id=? AND status=1 ORDER BY sort LIMIT ?`,
		categoryID, limit)
	if err != nil {
		return nil
	}
	defer rows.Close()
	return h.scanExploreApps(rows, locale)
}

func (h *Handler) scanExploreApps(rows *sql.Rows, locale string) []map[string]any {
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, typ, email, phone, realName int
		var name []byte
		var icon, appURL, slogan sql.NullString
		if err := rows.Scan(&id, &name, &icon, &typ, &appURL, &slogan, &email, &phone, &realName); err != nil {
			continue
		}
		list = append(list, map[string]any{
			"id": id, "name": lang.Pick(name, locale), "icon": nullString(icon), "type": typ,
			"app_url": nullString(appURL), "slogan": nullString(slogan),
			"email": email, "phone": phone, "real_name": realName,
		})
	}
	return list
}
