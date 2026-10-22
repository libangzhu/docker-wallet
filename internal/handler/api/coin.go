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

func (h *Handler) GetCoinByID(c *gin.Context) {
	id := req.ParamInt(c, "id", 0)
	if id == 0 {
		response.WriteAPI(c, []any{})
		return
	}
	row := h.DB.Sources.QueryRowContext(c.Request.Context(),
		"SELECT id,sid,name,optional_name,nickname,icon,introduce,official,paper,exchange,platform,chain,`release`,price,area_search,publish_count,circulate_count,decimals,address,platform_id,treaty,is_nft FROM coin WHERE id=?", id)
	data, err := scanCoinRow(row)
	if err != nil {
		if err == sql.ErrNoRows {
			response.WriteAPI(c, []any{})
			return
		}
		log.Error.Printf("get-coin-by-id query failed id=%d err=%v", id, err)
		response.WriteAPI(c, []any{})
		return
	}
	locale := req.Lang(c)
	data["nickname"] = lang.Pick(asBytes(data["nickname"]), locale)
	data["introduce"] = lang.Pick(asBytes(data["introduce"]), locale)
	response.WriteAPI(c, data)
}

func (h *Handler) Search(c *gin.Context) {
	platformID := req.Param(c, "platform_id", "")
	if platformID == "" {
		response.WriteAPI(c, gin.H{"code": -1, "data": nil})
		return
	}
	chain := req.Param(c, "chain", "")
	keyword := req.Param(c, "keyword", "")
	page := req.ParamInt(c, "page", 1)
	limit := req.ParamInt(c, "limit", 10)
	if page < 1 {
		page = 1
	}
	if limit < 1 {
		limit = 10
	}

	where := []string{"JSON_CONTAINS(platform_id, ?)"}
	args := []any{platformID}
	if chain != "" {
		where = append(where, "chain=?")
		args = append(args, chain)
	}
	if keyword != "" {
		where = append(where, "(name LIKE ? OR optional_name LIKE ?)")
		like := "%" + keyword + "%"
		args = append(args, like, like)
	}
	sqlStr := `SELECT id,sid,icon,name,optional_name,nickname,platform,chain,address AS contract_address,treaty FROM coin WHERE ` +
		strings.Join(where, " AND ") + ` ORDER BY id LIMIT ? OFFSET ?`
	args = append(args, limit, (page-1)*limit)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), sqlStr, args...)
	if err != nil {
		log.Error.Printf("coin search failed platform_id=%s err=%v", platformID, err)
		response.WriteAPI(c, gin.H{"code": -1, "data": nil})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	locale := req.Lang(c)
	for rows.Next() {
		var id, treaty int
		var sid, icon, name, optional, nickname, platform, ch, contract sql.NullString
		if err := rows.Scan(&id, &sid, &icon, &name, &optional, &nickname, &platform, &ch, &contract, &treaty); err != nil {
			log.Error.Printf("coin search scan failed err=%v", err)
			continue
		}
		list = append(list, map[string]any{
			"id":               id,
			"sid":              nullString(sid),
			"icon":             nullString(icon),
			"name":             nullString(name),
			"optional_name":    nullString(optional),
			"nickname":         lang.PickString(nullString(nickname), locale),
			"platform":         nullString(platform),
			"chain":            nullString(ch),
			"contract_address": nullString(contract),
			"treaty":           treaty,
		})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) SearchCoinByName(c *gin.Context) {
	name := req.Param(c, "name", "")
	if name == "" {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
		return
	}
	locale := req.Lang(c)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,sid,icon,name,optional_name,nickname,platform,chain,address AS contract_address,treaty FROM coin WHERE name=?`, name)
	if err != nil {
		log.Error.Printf("search-coin-by-name failed name=%s err=%v", name, err)
		response.WriteAPI(c, gin.H{"code": -1, "msg": "查询失败", "data": nil})
		return
	}
	defer rows.Close()
	list := make([]map[string]any, 0)
	for rows.Next() {
		var id, treaty int
		var sid, icon, n, optional, nickname, platform, ch, contract sql.NullString
		if err := rows.Scan(&id, &sid, &icon, &n, &optional, &nickname, &platform, &ch, &contract, &treaty); err != nil {
			continue
		}
		list = append(list, map[string]any{
			"id":               id,
			"sid":              nullString(sid),
			"icon":             nullString(icon),
			"name":             nullString(n),
			"optional_name":    nullString(optional),
			"nickname":         lang.PickString(nullString(nickname), locale),
			"platform":         nullString(platform),
			"chain":            nullString(ch),
			"contract_address": nullString(contract),
			"treaty":           treaty,
		})
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) CoinIndex(c *gin.Context) {
	names := req.ParamStringSlice(c, "names")
	if len(names) == 0 {
		if v := req.ParamAny(c, "names"); v != nil {
			names = anySlice(v)
		}
	}
	if len(names) == 0 {
		response.WriteAPI(c, gin.H{"code": 0, "data": []any{}})
		return
	}
	locale := req.Lang(c)
	list := make([]map[string]any, 0)
	for _, raw := range names {
		var name, platform string
		if strings.Contains(raw, ",") {
			parts := strings.SplitN(raw, ",", 2)
			name = strings.TrimSpace(parts[0])
			platform = strings.TrimSpace(parts[1])
			if strings.EqualFold(name, "USDT") && strings.EqualFold(platform, "BTC") {
				platform = "omni"
			}
		} else {
			name = strings.TrimSpace(raw)
		}
		var q string
		var args []any
		if platform != "" {
			q = `SELECT id,sid,icon,name,nickname,introduce,platform,chain,treaty FROM coin WHERE name=? AND (platform=? OR chain=?) LIMIT 1`
			args = []any{name, platform, platform}
		} else {
			q = `SELECT id,sid,icon,name,nickname,introduce,platform,chain,treaty FROM coin WHERE name=? LIMIT 1`
			args = []any{name}
		}
		var id, treaty int
		var sid, icon, n, nickname, introduce, plat, chain sql.NullString
		err := h.DB.Sources.QueryRowContext(c.Request.Context(), q, args...).Scan(&id, &sid, &icon, &n, &nickname, &introduce, &plat, &chain, &treaty)
		if err != nil {
			continue
		}
		ticker := quotation(c.Request.Context(), h.RDB, nullString(n))
		chainQ := quotation(c.Request.Context(), h.RDB, nullString(chain))
		item := map[string]any{
			"id":              id,
			"sid":             nullString(sid),
			"icon":            nullString(icon),
			"name":            nullString(n),
			"nickname":        lang.PickString(nullString(nickname), locale),
			"introduce":       lang.PickString(nullString(introduce), locale),
			"platform":        nullString(plat),
			"chain":           nullString(chain),
			"treaty":          treaty,
			"chain_quotation": chainQ,
		}
		if ticker != nil {
			item["low"] = ticker["low"]
			item["high"] = ticker["high"]
			item["last"] = ticker["last"]
			item["rmb"] = ticker["rmb"]
			item["usd"] = ticker["usd"]
		}
		if chainQ != nil {
			item["chain_rmb"] = chainQ["rmb"]
			item["chain_usd"] = chainQ["usd"]
		} else {
			item["chain_rmb"] = 0
			item["chain_usd"] = 0
		}
		list = append(list, item)
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": list})
}

func (h *Handler) GetMinerFeeByName(c *gin.Context) {
	name := req.Param(c, "name", "")
	var chain sql.NullString
	err := h.DB.Sources.QueryRowContext(c.Request.Context(), `SELECT chain FROM coin WHERE name=? LIMIT 1`, name).Scan(&chain)
	if err != nil || !chain.Valid || chain.String == "" {
		response.WriteException(c, 8, "币种不存在", 0, "coin/get-miner-fee-by-name")
		return
	}
	var id, level, typ int
	var platform string
	var min, max, fee string
	var createAt, updateAt sql.NullTime
	err = h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT id,platform,min,max,level,type,fee,create_at,update_at FROM miner_fee WHERE platform=? AND type=1 LIMIT 1`,
		chain.String).Scan(&id, &platform, &min, &max, &level, &typ, &fee, &createAt, &updateAt)
	if err != nil {
		log.Error.Printf("get-miner-fee-by-name empty name=%s chain=%s err=%v", name, chain.String, err)
		response.WriteException(c, 8, "旷工费未设置", 0, "coin/get-miner-fee-by-name")
		return
	}
	out := map[string]any{
		"id": id, "platform": platform, "min": format6(min), "max": format6(max),
		"level": level, "type": typ, "fee": fee,
	}
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
	response.WriteAPI(c, out)
}

func (h *Handler) GetRecList(c *gin.Context) {
	h.recommendList(c, false)
}

func (h *Handler) GetNewRecList(c *gin.Context) {
	h.recommendList(c, true)
}

func (h *Handler) recommendList(c *gin.Context, newer bool) {
	page := req.ParamInt(c, "page", 1)
	limit := req.ParamInt(c, "limit", 999)
	platformID := req.ParamInt(c, "platform_id", 1)
	if !newer {
		platformID = req.ParamInt(c, "platform_id", 2)
		switch platformID {
		case 1:
			platformID = 3
		case 2:
			platformID = 1
		case 3:
			platformID = 2
		}
	}
	typ := req.ParamInt(c, "type", 1)
	chain := req.Param(c, "chain", "")
	recommend := req.Param(c, "recommend", "")
	where := "platform_id=? AND type=?"
	args := []any{platformID, typ}
	if recommend != "" {
		where += " AND recommend=?"
		args = append(args, recommend)
	}
	if newer && chain != "" {
		where += " AND chain=?"
		args = append(args, chain)
	}
	q := fmt.Sprintf(`SELECT cid FROM coin_recommend WHERE %s ORDER BY sort ASC LIMIT ? OFFSET ?`, where)
	args = append(args, limit, (page-1)*limit)
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(), q, args...)
	if err != nil {
		log.Error.Printf("recommend list failed err=%v", err)
		response.WriteAPI(c, gin.H{"count": 0, "data": nil})
		return
	}
	defer rows.Close()
	var cids []int
	for rows.Next() {
		var cid int
		if err := rows.Scan(&cid); err == nil {
			cids = append(cids, cid)
		}
	}
	locale := req.Lang(c)
	list := make([]map[string]any, 0)
	for _, cid := range cids {
		item, err := h.loadCoinByID(c, cid, locale)
		if err != nil {
			continue
		}
		list = append(list, item)
	}
	if newer && len(list) == 0 {
		response.WriteAPI(c, gin.H{"count": 0, "data": nil})
		return
	}
	response.WriteAPI(c, gin.H{"count": len(list), "data": list})
}

func (h *Handler) GetBrowerByPlatform(c *gin.Context) {
	platform := req.Param(c, "platform", "")
	if platform == "" {
		response.WriteAPI(c, gin.H{"code": 1, "data": []any{}, "msg": "平台参数不能为空"})
		return
	}
	val, err := h.RDB.Default.HGet(c.Request.Context(), "platform_brower_info", strings.ToLower(platform)).Result()
	if err != nil {
		response.WriteAPI(c, gin.H{"code": 0, "data": ""})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "data": val})
}

func (h *Handler) GetWithHold(c *gin.Context) {
	platform := req.Param(c, "platform", "")
	coinName := req.Param(c, "coinname", "")
	var treaty sql.NullString
	_ = h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT treaty FROM coin WHERE name=? AND platform=? LIMIT 1`, strings.ToUpper(coinName), platform).Scan(&treaty)
	response.WriteAPI(c, gin.H{"code": 0, "data": gin.H{"treaty": nullString(treaty), "platform": platform}})
}

func (h *Handler) Transaction(c *gin.Context) {
	name := req.Param(c, "name", "")
	txhash := req.Param(c, "txhash", "")
	if name == "" || txhash == "" {
		response.WriteAPI(c, false)
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{"name": name, "txhash": txhash}})
}

func (h *Handler) loadCoinByID(c *gin.Context, id int, locale string) (map[string]any, error) {
	var sid, icon, name, nickname, introduce, platform, chain, optional sql.NullString
	var treaty int
	err := h.DB.Sources.QueryRowContext(c.Request.Context(),
		`SELECT sid,icon,name,optional_name,nickname,introduce,platform,chain,treaty FROM coin WHERE id=?`, id).
		Scan(&sid, &icon, &name, &optional, &nickname, &introduce, &platform, &chain, &treaty)
	if err != nil {
		return nil, err
	}
	ticker := quotation(c.Request.Context(), h.RDB, nullString(name))
	chainQ := quotation(c.Request.Context(), h.RDB, nullString(chain))
	item := map[string]any{
		"id": id, "sid": strings.Title(nullString(sid)), "icon": nullString(icon),
		"name": nullString(name), "nickname": lang.PickString(nullString(nickname), locale),
		"introduce": lang.PickString(nullString(introduce), locale), "optional_name": nullString(optional),
		"platform": nullString(platform), "chain": nullString(chain), "treaty": treaty,
		"chain_quotation": chainQ,
	}
	if ticker != nil {
		item["low"] = ticker["low"]
		item["high"] = ticker["high"]
		item["last"] = ticker["last"]
		item["rmb"] = ticker["rmb"]
		item["usd"] = ticker["usd"]
	} else {
		item["low"], item["high"], item["last"], item["rmb"], item["usd"] = 0, 0, 0, 0, 0
	}
	return item, nil
}

func scanCoinRow(row *sql.Row) (map[string]any, error) {
	var id, exchange, decimals, treaty, isNFT int
	var sid, name, optional, nickname, icon, introduce, official, paper, platform, chain, area, address, platformID sql.NullString
	var release sql.NullTime
	var price, publish, circulate sql.NullString
	if err := row.Scan(&id, &sid, &name, &optional, &nickname, &icon, &introduce, &official, &paper, &exchange, &platform, &chain, &release, &price, &area, &publish, &circulate, &decimals, &address, &platformID, &treaty, &isNFT); err != nil {
		return nil, err
	}
	out := map[string]any{
		"id": id, "sid": nullString(sid), "name": nullString(name), "optional_name": nullString(optional),
		"nickname": []byte(nullString(nickname)), "icon": nullString(icon), "introduce": []byte(nullString(introduce)),
		"official": nullString(official), "paper": nullString(paper), "exchange": exchange,
		"platform": nullString(platform), "chain": nullString(chain), "area_search": nullString(area),
		"publish_count": nullString(publish), "circulate_count": nullString(circulate), "decimals": decimals,
		"address": nullString(address), "platform_id": scanJSONMap([]byte(nullString(platformID))),
		"treaty": treaty, "is_nft": isNFT, "price": nullString(price),
	}
	if release.Valid {
		out["release"] = release.Time.Format("2006-01-02")
	} else {
		out["release"] = nil
	}
	return out, nil
}

func asBytes(v any) []byte {
	switch t := v.(type) {
	case []byte:
		return t
	case string:
		return []byte(t)
	default:
		return nil
	}
}

func format6(s string) string {
	f, err := strconvParse(s)
	if err != nil {
		return s
	}
	return fmt.Sprintf("%.6f", f)
}

func strconvParse(s string) (float64, error) {
	var f float64
	_, err := fmt.Sscanf(s, "%f", &f)
	return f, err
}

func anySlice(v any) []string {
	switch t := v.(type) {
	case []string:
		return t
	case []any:
		out := make([]string, 0, len(t))
		for _, item := range t {
			out = append(out, fmt.Sprint(item))
		}
		return out
	case string:
		return []string{t}
	default:
		return nil
	}
}
