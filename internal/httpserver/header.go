package httpserver

import (
	"github.com/gin-gonic/gin"

	"wallet/internal/platform/req"
)

func HeaderMiddleware() gin.HandlerFunc {
	return func(c *gin.Context) {
		req.AttachHeaders(c)
		c.Header("Access-Control-Allow-Origin", "*")
		c.Header("Access-Control-Allow-Methods", "POST,GET,PUT,DELETE,OPTIONS")
		c.Header("Access-Control-Allow-Headers", "Authorization,FZM-REQUEST-OS,FZM-USER-IP,FZM-REQUEST-UUID,FZM-PLATFORM-ID,Content-Type,Content-Length")
		c.Header("Access-Control-Allow-Credentials", "true")
		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(200)
			return
		}
		c.Next()
	}
}
