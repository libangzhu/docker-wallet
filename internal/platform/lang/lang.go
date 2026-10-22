package lang

import (
	"encoding/json"
	"strings"
)

func Normalize(raw string) string {
	if raw == "" {
		return "zh-CN"
	}
	if strings.EqualFold(raw, "en") {
		return "en-US"
	}
	return raw
}

func Pick(raw []byte, locale string) string {
	if len(raw) == 0 {
		return ""
	}
	var m map[string]any
	if err := json.Unmarshal(raw, &m); err != nil {
		return string(raw)
	}
	if v, ok := m[locale]; ok {
		return toString(v)
	}
	return ""
}

func PickString(raw string, locale string) string {
	return Pick([]byte(raw), locale)
}

func toString(v any) string {
	if v == nil {
		return ""
	}
	s, ok := v.(string)
	if !ok {
		return ""
	}
	return s
}
