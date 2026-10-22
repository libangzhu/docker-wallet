package req

import (
	"encoding/json"
	"io"
	"strconv"
	"strings"

	"github.com/gin-gonic/gin"

	"wallet/internal/platform/lang"
)

const (
	ctxLang       = "req_lang"
	ctxPlatformID = "req_platform_id"
	ctxCurrencyID = "req_currency_id"
)

func AttachHeaders(c *gin.Context) {
	c.Set(ctxLang, lang.Normalize(c.GetHeader("lang")))
	c.Set(ctxPlatformID, c.GetHeader("FZM-PLATFORM-ID"))
	c.Set(ctxCurrencyID, c.GetHeader("FZM-CURRENCY-ID"))
}

func Lang(c *gin.Context) string {
	v, _ := c.Get(ctxLang)
	s, _ := v.(string)
	if s == "" {
		return lang.Normalize(c.GetHeader("lang"))
	}
	return s
}

func PlatformID(c *gin.Context) string {
	if v := c.GetHeader("FZM-PLATFORM-ID"); v != "" {
		return v
	}
	v, _ := c.Get(ctxPlatformID)
	s, _ := v.(string)
	return s
}

func CurrencyID(c *gin.Context) string {
	if v := c.GetHeader("FZM-CURRENCY-ID"); v != "" {
		return v
	}
	v, _ := c.Get(ctxCurrencyID)
	s, _ := v.(string)
	return s
}

func Token(c *gin.Context) string {
	return c.GetHeader("Token")
}

func QueryAll(c *gin.Context, key string) []string {
	values := c.QueryArray(key)
	if len(values) == 0 {
		values = c.PostFormArray(key)
	}
	return values
}

func Param(c *gin.Context, key string, def string) string {
	if v := c.PostForm(key); v != "" {
		return v
	}
	if v := c.Query(key); v != "" {
		return v
	}
	if body := jsonBody(c); body != nil {
		if v, ok := body[key]; ok {
			return anyToString(v)
		}
	}
	return def
}

func ParamInt(c *gin.Context, key string, def int) int {
	s := Param(c, key, "")
	if s == "" {
		return def
	}
	n, err := strconv.Atoi(s)
	if err != nil {
		return def
	}
	return n
}

func ParamAny(c *gin.Context, key string) any {
	if values := c.PostFormArray(key); len(values) > 1 {
		out := make([]any, 0, len(values))
		for _, v := range values {
			out = append(out, v)
		}
		return out
	}
	if v := c.PostForm(key); v != "" {
		return v
	}
	if values := c.QueryArray(key); len(values) > 1 {
		out := make([]any, 0, len(values))
		for _, v := range values {
			out = append(out, v)
		}
		return out
	}
	if v := c.Query(key); v != "" {
		return v
	}
	if body := jsonBody(c); body != nil {
		if v, ok := body[key]; ok {
			return v
		}
	}
	return nil
}

func ParamStringSlice(c *gin.Context, key string) []string {
	if values := c.PostFormArray(key); len(values) > 0 {
		return values
	}
	if values := c.QueryArray(key); len(values) > 0 {
		return values
	}
	if body := jsonBody(c); body != nil {
		if v, ok := body[key]; ok {
			return anyToStrings(v)
		}
	}
	return nil
}

func jsonBody(c *gin.Context) map[string]any {
	if cached, ok := c.Get("_json_body"); ok {
		m, _ := cached.(map[string]any)
		return m
	}
	ct := c.GetHeader("Content-Type")
	if !strings.Contains(ct, "application/json") {
		c.Set("_json_body", map[string]any{})
		return nil
	}
	raw, err := io.ReadAll(c.Request.Body)
	if err != nil || len(raw) == 0 {
		c.Set("_json_body", map[string]any{})
		return nil
	}
	c.Request.Body = io.NopCloser(strings.NewReader(string(raw)))
	var m map[string]any
	if err := json.Unmarshal(raw, &m); err != nil {
		c.Set("_json_body", map[string]any{})
		return nil
	}
	c.Set("_json_body", m)
	return m
}

func anyToString(v any) string {
	switch t := v.(type) {
	case string:
		return t
	case float64:
		if t == float64(int(t)) {
			return strconv.Itoa(int(t))
		}
		return strconv.FormatFloat(t, 'f', -1, 64)
	case json.Number:
		return t.String()
	case bool:
		if t {
			return "1"
		}
		return "0"
	default:
		b, _ := json.Marshal(v)
		return string(b)
	}
}

func anyToStrings(v any) []string {
	switch t := v.(type) {
	case []any:
		out := make([]string, 0, len(t))
		for _, item := range t {
			out = append(out, anyToString(item))
		}
		return out
	case []string:
		return t
	case string:
		if t == "" {
			return nil
		}
		return []string{t}
	default:
		s := anyToString(v)
		if s == "" {
			return nil
		}
		return []string{s}
	}
}
