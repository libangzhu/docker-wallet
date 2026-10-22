package api

import (
	"context"
	"database/sql"
	"encoding/json"

	"wallet/internal/app"
	redisx "wallet/internal/platform/redis"
)

type Handler struct {
	*app.Deps
}

func New(d *app.Deps) *Handler {
	return &Handler{Deps: d}
}

func scanJSONMap(raw []byte) any {
	if len(raw) == 0 {
		return nil
	}
	var v any
	if err := json.Unmarshal(raw, &v); err != nil {
		return string(raw)
	}
	return v
}

func nullString(ns sql.NullString) string {
	if ns.Valid {
		return ns.String
	}
	return ""
}

func quotation(ctx context.Context, rdb *redisx.Clients, name string) map[string]any {
	if rdb == nil || rdb.Ticker == nil || name == "" {
		return nil
	}
	vals, err := rdb.Ticker.HGetAll(ctx, "quotation_"+name).Result()
	if err != nil || len(vals) == 0 {
		vals, err = rdb.Default.HGetAll(ctx, "quotation_"+name).Result()
		if err != nil || len(vals) == 0 {
			return nil
		}
	}
	out := make(map[string]any, len(vals))
	for k, v := range vals {
		out[k] = v
	}
	return out
}
