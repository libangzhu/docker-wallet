package lang

import "testing"

func TestNormalize(t *testing.T) {
	if Normalize("") != "zh-CN" {
		t.Fatal(Normalize(""))
	}
	if Normalize("en") != "en-US" {
		t.Fatal(Normalize("en"))
	}
	if Normalize("zh-CN") != "zh-CN" {
		t.Fatal(Normalize("zh-CN"))
	}
}

func TestPick(t *testing.T) {
	raw := []byte(`{"zh-CN":"比特币","en-US":"BTC"}`)
	if Pick(raw, "zh-CN") != "比特币" {
		t.Fatal(Pick(raw, "zh-CN"))
	}
	if Pick(raw, "ja") != "" {
		t.Fatal("missing locale should be empty")
	}
}
