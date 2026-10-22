package walletapi

import (
	"time"

	"github.com/gin-gonic/gin"
	"github.com/golang-jwt/jwt/v5"

	"golang.org/x/crypto/bcrypt"
	"wallet/internal/app"
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

func (h *Handler) UserLogin(c *gin.Context) {
	username := req.Param(c, "username", "")
	password := req.Param(c, "password", "")
	var uid, status, platformID int
	var hash, group, uname string
	err := h.DB.Manage.QueryRowContext(c.Request.Context(),
		`SELECT uid,username,password,status,platform_id,`+"`group`"+` FROM gli_admin WHERE username=?`, username).
		Scan(&uid, &uname, &hash, &status, &platformID, &group)
	if err != nil {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "Incorrect username or password.", "data": nil})
		return
	}
	if err := bcrypt.CompareHashAndPassword([]byte(compatHash(hash)), []byte(password)); err != nil {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "Incorrect username or password.", "data": nil})
		return
	}
	if status == 2 {
		response.WriteAPI(c, gin.H{"code": -1, "msg": "用户已停用", "data": nil})
		return
	}
	now := time.Now()
	tok := jwt.NewWithClaims(jwt.SigningMethodHS256, jwt.MapClaims{
		"iss":  h.Cfg.JWT.Iss,
		"aud":  h.Cfg.JWT.Aud,
		"iat":  now.Unix(),
		"nbf":  now.Unix(),
		"exp":  now.Add(time.Duration(h.Cfg.JWT.ExpireHours) * time.Hour).Unix(),
		"user": map[string]any{"uid": uid, "username": uname, "platform_id": platformID, "group": group},
	})
	s, err := tok.SignedString([]byte(h.Cfg.JWT.Key))
	if err != nil {
		log.Error.Printf("wallet login jwt sign failed uid=%d err=%v", uid, err)
		response.WriteAPI(c, gin.H{"code": -1, "msg": "登录失败", "data": nil})
		return
	}
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{"access_token": s}})
}

func (h *Handler) AuthRequired() gin.HandlerFunc {
	return func(c *gin.Context) {
		token := req.Token(c)
		if token == "" {
			response.WriteAPI(c, gin.H{"code": "40001", "msg": "platform auth error", "data": nil})
			c.Abort()
			return
		}
		key := ""
		if h.Cfg != nil {
			key = h.Cfg.JWT.Key
		}
		parsed, err := jwt.Parse(token, func(t *jwt.Token) (any, error) {
			return []byte(key), nil
		})
		if err != nil || !parsed.Valid {
			log.Error.Printf("wallet token invalid err=%v", err)
			response.WriteAPI(c, gin.H{"code": "40001", "msg": "platform auth error", "data": nil})
			c.Abort()
			return
		}
		claims, ok := parsed.Claims.(jwt.MapClaims)
		if !ok {
			response.WriteAPI(c, gin.H{"code": "40001", "msg": "platform auth error", "data": nil})
			c.Abort()
			return
		}
		c.Set("jwt_user", claims["user"])
		c.Next()
	}
}

func (h *Handler) UserInfo(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{}})
}
func (h *Handler) UserSync(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) UserList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) ManualList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) Verify(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) EditPwd(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) Add(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}

func (h *Handler) WalletList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) WalletBallance(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 1, "data": "0", "msg": "success"})
}
func (h *Handler) UserAsset(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 1, "data": []any{}, "msg": "success"})
}
func (h *Handler) Currency(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) AssetIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 1, "data": []any{}, "msg": "success"})
}
func (h *Handler) MonitorList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) CoinRelationIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": []any{}})
}
func (h *Handler) BannerType(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": []any{}})
}
func (h *Handler) BannerIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": []any{}})
}
func (h *Handler) BannerUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
}
func (h *Handler) BannerRemove(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "success", "data": nil})
}
func (h *Handler) NoticeIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) NoticeUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) NoticeRemove(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) ExploreAppCategory(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) ExploreAppCategoryUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) ExploreAppCategoryRemove(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) ExploreAppApps(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) ExploreAppAppUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) ExploreAppAppRemove(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) ExploreBannerIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) ExploreBannerUpdate(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) ExploreBannerRemove(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) AirDropGetBalance(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": "0"})
}
func (h *Handler) AirDropExpenses(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) AirDropIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) AirDropApply(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) AirDropStop(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) IssueCoinApplyList(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) IssueCoinApplyDetail(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{}})
}
func (h *Handler) IssueCoinVerify(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) IssueChainIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) IssueChainChainInfo(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{}})
}
func (h *Handler) IssueChainSetCharge(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) IssueChainManageReview(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) IssueChainGetTokens(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) FlashFlashListConfig(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) FlashFlashDefaultConfig(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{}})
}
func (h *Handler) FlashFlashConfig(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) FlashFlashCloneConfig(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok"})
}
func (h *Handler) FlashIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": []any{}})
}
func (h *Handler) OssUpload(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": 0, "msg": "ok", "data": gin.H{}})
}

func compatHash(hash string) string {
	if len(hash) > 4 && hash[:4] == "$2y$" {
		return "$2a$" + hash[4:]
	}
	return hash
}
