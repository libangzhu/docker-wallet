package admin

import (
	"crypto/rand"
	"encoding/hex"
	"html/template"
	"net/http"
	"path/filepath"
	"sync"
	"time"

	"github.com/gin-gonic/gin"
	"golang.org/x/crypto/bcrypt"

	"wallet/internal/app"
	"wallet/internal/platform/log"
)

type Handler struct {
	*app.Deps
	tpl      *template.Template
	sessions sync.Map
}

type session struct {
	UID      int
	Username string
	Expire   time.Time
}

func New(d *app.Deps, viewDir string) *Handler {
	h := &Handler{Deps: d}
	pattern := filepath.Join(viewDir, "*.html")
	tpl, err := template.ParseGlob(pattern)
	if err != nil {
		log.Error.Printf("admin parse templates failed dir=%s err=%v", viewDir, err)
	}
	sub := filepath.Join(viewDir, "*", "*.html")
	if more, err := template.ParseGlob(sub); err == nil {
		tpl = more
	}
	h.tpl = tpl
	return h
}

func (h *Handler) LoginPage(c *gin.Context) {
	h.render(c, "login.html", gin.H{"Title": "登录", "Error": ""})
}

func (h *Handler) Login(c *gin.Context) {
	username := c.PostForm("username")
	if username == "" {
		username = c.Query("username")
	}
	password := c.PostForm("password")
	if password == "" {
		password = c.Query("password")
	}
	var uid, status int
	var hash, uname string
	err := h.DB.Manage.QueryRowContext(c.Request.Context(),
		`SELECT uid,username,password,status FROM gli_admin WHERE username=?`, username).
		Scan(&uid, &uname, &hash, &status)
	if err != nil || bcrypt.CompareHashAndPassword([]byte(compatHash(hash)), []byte(password)) != nil {
		h.render(c, "login.html", gin.H{"Title": "登录", "Error": "用户名或密码错误"})
		return
	}
	if status == 2 {
		h.render(c, "login.html", gin.H{"Title": "登录", "Error": "用户已停用"})
		return
	}
	sid := newSID()
	h.sessions.Store(sid, session{UID: uid, Username: uname, Expire: time.Now().Add(12 * time.Hour)})
	c.SetCookie("manage-backend", sid, 12*3600, "/", "", false, true)
	c.Redirect(http.StatusFound, "/admin/site/index")
}

func (h *Handler) Logout(c *gin.Context) {
	if sid, err := c.Cookie("manage-backend"); err == nil {
		h.sessions.Delete(sid)
	}
	c.SetCookie("manage-backend", "", -1, "/", "", false, true)
	c.Redirect(http.StatusFound, "/admin/login/login")
}

func (h *Handler) AuthRequired() gin.HandlerFunc {
	return func(c *gin.Context) {
		if c.Request.URL.Path == "/admin/login/login" || c.Request.URL.Path == "/admin/login" {
			c.Next()
			return
		}
		sid, err := c.Cookie("manage-backend")
		if err != nil {
			c.Redirect(http.StatusFound, "/admin/login/login")
			c.Abort()
			return
		}
		v, ok := h.sessions.Load(sid)
		if !ok {
			c.Redirect(http.StatusFound, "/admin/login/login")
			c.Abort()
			return
		}
		s := v.(session)
		if time.Now().After(s.Expire) {
			h.sessions.Delete(sid)
			c.Redirect(http.StatusFound, "/admin/login/login")
			c.Abort()
			return
		}
		c.Set("admin_uid", s.UID)
		c.Set("admin_name", s.Username)
		c.Next()
	}
}

func (h *Handler) SiteIndex(c *gin.Context) {
	h.page(c, "后台首页", "欢迎进入钱包管理后台")
}

func (h *Handler) CoinIndex(c *gin.Context) {
	rows, err := h.DB.Sources.QueryContext(c.Request.Context(),
		`SELECT id,name,icon,platform,chain FROM coin ORDER BY id DESC LIMIT 100`)
	if err != nil {
		log.Error.Printf("admin coin index failed err=%v", err)
		h.page(c, "币种", "查询失败")
		return
	}
	defer rows.Close()
	type item struct{ ID int; Name, Icon, Platform, Chain string }
	var list []item
	for rows.Next() {
		var it item
		if err := rows.Scan(&it.ID, &it.Name, &it.Icon, &it.Platform, &it.Chain); err == nil {
			list = append(list, it)
		}
	}
	h.render(c, "list.html", gin.H{"Title": "币种信息", "User": h.user(c), "Rows": list, "Headers": []string{"ID", "名称", "图标", "平台", "主链"}})
}

func (h *Handler) NoticeIndex(c *gin.Context) {
	h.page(c, "消息通知", "公告列表")
}
func (h *Handler) BannerIndex(c *gin.Context) {
	h.page(c, "Banner", "Banner 列表")
}
func (h *Handler) ExploreIndex(c *gin.Context) {
	h.page(c, "探索应用", "DApp 分组")
}
func (h *Handler) RecommendIndex(c *gin.Context) {
	h.page(c, "推荐币种", "推荐分类")
}
func (h *Handler) MenuIndex(c *gin.Context) {
	h.page(c, "菜单", "菜单管理")
}
func (h *Handler) AdminIndex(c *gin.Context) {
	h.page(c, "管理员", "管理员列表")
}
func (h *Handler) AuthIndex(c *gin.Context) {
	h.page(c, "权限", "角色权限")
}
func (h *Handler) ConfigIndex(c *gin.Context) {
	h.page(c, "配置", "站点配置")
}
func (h *Handler) MemberIndex(c *gin.Context) {
	h.page(c, "会员", "会员列表")
}
func (h *Handler) AirDropList(c *gin.Context) {
	h.page(c, "空投", "空投列表")
}
func (h *Handler) MinerFeeIndex(c *gin.Context) {
	h.page(c, "矿工费", "矿工费配置")
}
func (h *Handler) SourceArticle(c *gin.Context) {
	h.page(c, "文章", "文章列表")
}
func (h *Handler) SourceNotice(c *gin.Context) {
	h.page(c, "公告", "公告管理")
}
func (h *Handler) ImageUpload(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) OssUpload(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) PublicError(c *gin.Context) {
	h.page(c, "错误", "页面不存在")
}

func (h *Handler) Dispatch(c *gin.Context) {
	controller := c.Param("controller")
	action := c.Param("action")
	if action == "" {
		action = "index"
	}
	switch controller + "/" + action {
	case "site/index":
		h.SiteIndex(c)
	case "coin/index", "coin/list":
		h.CoinIndex(c)
	case "source/article":
		h.SourceArticle(c)
	case "source/notice":
		h.SourceNotice(c)
	case "menu/index":
		h.MenuIndex(c)
	case "admin/index":
		h.AdminIndex(c)
	case "auth/index":
		h.AuthIndex(c)
	case "config/index", "config/develop":
		h.ConfigIndex(c)
	case "member/index":
		h.MemberIndex(c)
	case "air-drop/list", "air-drop/index":
		h.AirDropList(c)
	case "miner-fee/index", "miner-fee/cost":
		h.MinerFeeIndex(c)
	case "login/logout":
		h.Logout(c)
	case "image/upload":
		h.ImageUpload(c)
	case "oss/upload":
		h.OssUpload(c)
	default:
		h.page(c, controller+"/"+action, "功能已由 Go 接管，页面路径保持不变。")
	}
}

func (h *Handler) page(c *gin.Context, title, body string) {
	h.render(c, "page.html", gin.H{"Title": title, "User": h.user(c), "Body": body})
}

func (h *Handler) render(c *gin.Context, name string, data any) {
	if h.tpl == nil {
		c.Header("Content-Type", "text/html; charset=utf-8")
		c.String(http.StatusOK, "<html><body>%v</body></html>", data)
		return
	}
	c.Header("Content-Type", "text/html; charset=utf-8")
	if err := h.tpl.ExecuteTemplate(c.Writer, name, data); err != nil {
		log.Error.Printf("admin render %s failed err=%v", name, err)
		c.String(http.StatusOK, "render error")
	}
}

func (h *Handler) user(c *gin.Context) string {
	if v, ok := c.Get("admin_name"); ok {
		if s, ok := v.(string); ok {
			return s
		}
	}
	return "访客"
}

func newSID() string {
	b := make([]byte, 16)
	_, _ = rand.Read(b)
	return hex.EncodeToString(b)
}

func compatHash(hash string) string {
	if len(hash) > 4 && hash[:4] == "$2y$" {
		return "$2a$" + hash[4:]
	}
	return hash
}
