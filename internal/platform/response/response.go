package response

import (
	"encoding/json"
	"net/http"
	"reflect"
	"strconv"

	"github.com/gin-gonic/gin"
)

type Envelope map[string]any

func WriteAPI(c *gin.Context, data any) {
	writeWithWrap(c, data, true)
}

func WriteH5(c *gin.Context, code int, msg string, data any) {
	if msg == "" {
		switch code {
		case 0:
			msg = "SUCCEED"
		case 1:
			msg = "PARAMS NOT VALIDATE"
		case 2:
			msg = "INTERNAL ERROR"
		}
	}
	if data == nil {
		data = []any{}
	}
	payload := Envelope{"code": code, "msg": msg, "data": data}
	writeJSON(c, payload)
}

func WriteH5Error(c *gin.Context, code int, msg string) {
	writeJSON(c, Envelope{"code": code, "msg": msg})
}

func WriteException(c *gin.Context, code int, msg string, line int, file string) {
	writeJSON(c, Envelope{"code": code, "msg": msg, "line": line, "file": file})
}

func writeWithWrap(c *gin.Context, data any, wrapEmpty bool) {
	if data == nil {
		if wrapEmpty {
			writeJSON(c, Envelope{"code": 1, "msg": "数据为空"})
			return
		}
		writeJSON(c, Envelope{"code": 0, "data": nil})
		return
	}
	if env, ok := asEnvelope(data); ok {
		if _, has := env["code"]; has {
			writeJSON(c, env)
			return
		}
	}
	if isEmpty(data) {
		writeJSON(c, Envelope{"code": 1, "msg": "数据为空"})
		return
	}
	if isSlice(data) {
		writeJSON(c, Envelope{"code": 0, "count": sliceLen(data), "data": data})
		return
	}
	writeJSON(c, data)
}

func asEnvelope(data any) (Envelope, bool) {
	switch v := data.(type) {
	case Envelope:
		return v, true
	case map[string]any:
		return Envelope(v), true
	case gin.H:
		return Envelope(v), true
	default:
		return nil, false
	}
}

func isEmpty(data any) bool {
	if data == nil {
		return true
	}
	rv := reflect.ValueOf(data)
	switch rv.Kind() {
	case reflect.Slice, reflect.Array, reflect.Map, reflect.String:
		return rv.Len() == 0
	}
	return false
}

func isSlice(data any) bool {
	rv := reflect.ValueOf(data)
	return rv.Kind() == reflect.Slice || rv.Kind() == reflect.Array
}

func sliceLen(data any) int {
	return reflect.ValueOf(data).Len()
}

func writeJSON(c *gin.Context, payload any) {
	callback := c.Query("callback")
	c.Header("Content-Type", "application/json; charset=UTF-8")
	if callback != "" {
		body, err := json.Marshal(payload)
		if err != nil {
			c.Status(http.StatusOK)
			return
		}
		c.Header("Content-Type", "application/javascript; charset=UTF-8")
		c.String(http.StatusOK, "%s(%s)", callback, string(body))
		return
	}
	c.JSON(http.StatusOK, payload)
}

func SetPagination(c *gin.Context, total, page, size int) {
	pageCount := 0
	if size > 0 {
		pageCount = (total + size - 1) / size
	}
	c.Header("X-Pagination-Total-Count", itoa(total))
	c.Header("X-Pagination-Page-Count", itoa(pageCount))
	c.Header("X-Pagination-Current-Page", itoa(page))
	c.Header("X-Pagination-Per-Page", itoa(size))
}

func itoa(n int) string {
	return strconv.Itoa(n)
}
