package response

import (
	"encoding/json"
	"net/http/httptest"
	"testing"

	"github.com/gin-gonic/gin"
)

func TestWriteAPIKeepsCode(t *testing.T) {
	w, c := recorder()
	WriteAPI(c, gin.H{"code": -1, "msg": "参数错误", "data": nil})
	var got map[string]any
	if err := json.Unmarshal(w.Body.Bytes(), &got); err != nil {
		t.Fatal(err)
	}
	if got["code"].(float64) != -1 || got["msg"] != "参数错误" {
		t.Fatalf("unexpected %+v", got)
	}
}

func TestWriteAPIWrapsEmpty(t *testing.T) {
	w, c := recorder()
	WriteAPI(c, []any{})
	var got map[string]any
	if err := json.Unmarshal(w.Body.Bytes(), &got); err != nil {
		t.Fatal(err)
	}
	if got["code"].(float64) != 1 || got["msg"] != "数据为空" {
		t.Fatalf("unexpected %+v", got)
	}
}

func TestWriteAPIStringCode(t *testing.T) {
	w, c := recorder()
	WriteAPI(c, gin.H{"code": "0", "msg": "welcome to api!"})
	var got map[string]any
	if err := json.Unmarshal(w.Body.Bytes(), &got); err != nil {
		t.Fatal(err)
	}
	if got["code"] != "0" {
		t.Fatalf("code type changed: %+v", got)
	}
}

func TestWriteH5DefaultMsg(t *testing.T) {
	w, c := recorder()
	WriteH5(c, 1, "", nil)
	var got map[string]any
	if err := json.Unmarshal(w.Body.Bytes(), &got); err != nil {
		t.Fatal(err)
	}
	if got["msg"] != "PARAMS NOT VALIDATE" {
		t.Fatalf("unexpected %+v", got)
	}
}

func recorder() (*httptest.ResponseRecorder, *gin.Context) {
	gin.SetMode(gin.TestMode)
	w := httptest.NewRecorder()
	c, _ := gin.CreateTestContext(w)
	c.Request = httptest.NewRequest("GET", "/", nil)
	return w, c
}
