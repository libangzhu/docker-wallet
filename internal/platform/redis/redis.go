package redisx

import (
	"context"
	"fmt"
	"time"

	"github.com/redis/go-redis/v9"

	"wallet/internal/config"
	"wallet/internal/platform/log"
)

type Clients struct {
	Cache    *redis.Client
	Default  *redis.Client
	Queue    *redis.Client
	App      *redis.Client
	Ticker   *redis.Client
	Currency *redis.Client
	ES       *redis.Client
	Channel  string
}

func Open(cfg config.RedisConfig) *Clients {
	return &Clients{
		Cache:    newClient(cfg, cfg.DBCache, "cache"),
		Default:  newClient(cfg, cfg.DBDefault, "default"),
		Queue:    newClient(cfg, cfg.DBQueue, "queue"),
		App:      newClient(cfg, cfg.DBApp, "app"),
		Ticker:   newClient(cfg, cfg.DBTicker, "ticker"),
		Currency: newClient(cfg, cfg.DBCurrency, "currency"),
		ES:       newClient(cfg, cfg.DBES, "es"),
		Channel:  cfg.QueueChannel,
	}
}

func newClient(cfg config.RedisConfig, db int, name string) *redis.Client {
	c := redis.NewClient(&redis.Options{
		Addr:         cfg.Addr,
		Password:     cfg.Password,
		DB:           db,
		DialTimeout:  3 * time.Second,
		ReadTimeout:  3 * time.Second,
		WriteTimeout: 3 * time.Second,
	})
	ctx, cancel := context.WithTimeout(context.Background(), 2*time.Second)
	defer cancel()
	if err := c.Ping(ctx).Err(); err != nil {
		log.Error.Printf("redis %s ping failed addr=%s db=%d err=%v", name, cfg.Addr, db, err)
	} else {
		log.Info.Printf("redis %s connected db=%d", name, db)
	}
	return c
}

func (c *Clients) Close() {
	for _, client := range []*redis.Client{c.Cache, c.Default, c.Queue, c.App, c.Ticker, c.Currency, c.ES} {
		if client != nil {
			_ = client.Close()
		}
	}
}

func QuotationKey(name string) string {
	return fmt.Sprintf("quotation_%s", name)
}
