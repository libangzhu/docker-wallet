package api

import (
	"github.com/gin-gonic/gin"

	"wallet/internal/platform/response"
)

func (h *Handler) SiteIndex(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": "0", "msg": "welcome to api!"})
}

func (h *Handler) SiteError(c *gin.Context) {
	response.WriteAPI(c, gin.H{"code": -1, "msg": "error"})
}
