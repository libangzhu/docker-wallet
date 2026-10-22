package app

import (
	"wallet/internal/config"
	"wallet/internal/platform/db"
	"wallet/internal/platform/des"
	redisx "wallet/internal/platform/redis"
)

type Deps struct {
	Cfg *config.Config
	DB  *db.DBs
	RDB *redisx.Clients
	DES *des.Cipher
}

func New(cfg *config.Config, databases *db.DBs, rdb *redisx.Clients) *Deps {
	return &Deps{
		Cfg: cfg,
		DB:  databases,
		RDB: rdb,
		DES: des.New(cfg.DES.Key, cfg.DES.IV),
	}
}
