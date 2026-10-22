package config

import (
	"fmt"
	"os"
	"time"

	"gopkg.in/yaml.v3"
)

type Config struct {
	HTTP      HTTPConfig      `yaml:"http"`
	Websocket WebsocketConfig `yaml:"websocket"`
	JWT       JWTConfig       `yaml:"jwt"`
	DES       DESConfig       `yaml:"des"`
	Uploader  UploaderConfig  `yaml:"uploader"`
	MySQL     MySQLConfig     `yaml:"mysql"`
	Redis     RedisConfig     `yaml:"redis"`
	Log       LogConfig       `yaml:"log"`
}

type HTTPConfig struct {
	Addr         string        `yaml:"addr"`
	ReadTimeout  time.Duration `yaml:"read_timeout"`
	WriteTimeout time.Duration `yaml:"write_timeout"`
}

type WebsocketConfig struct {
	Addr string `yaml:"addr"`
}

type JWTConfig struct {
	Key         string `yaml:"key"`
	Iss         string `yaml:"iss"`
	Aud         string `yaml:"aud"`
	ExpireHours int    `yaml:"expire_hours"`
}

type DESConfig struct {
	Key string `yaml:"key"`
	IV  string `yaml:"iv"`
}

type UploaderConfig struct {
	BaseURI  string `yaml:"base_uri"`
	Absolute string `yaml:"absolute"`
}

type MySQLConfig struct {
	Manage  DBItem `yaml:"manage"`
	Coin    DBItem `yaml:"coin"`
	Sources DBItem `yaml:"sources"`
}

type DBItem struct {
	DSN    string `yaml:"dsn"`
	Prefix string `yaml:"prefix"`
}

type RedisConfig struct {
	Addr         string `yaml:"addr"`
	Password     string `yaml:"password"`
	DBCache      int    `yaml:"db_cache"`
	DBDefault    int    `yaml:"db_default"`
	DBQueue      int    `yaml:"db_queue"`
	DBApp        int    `yaml:"db_app"`
	DBTicker     int    `yaml:"db_ticker"`
	DBCurrency   int    `yaml:"db_currency"`
	DBES         int    `yaml:"db_es"`
	QueueChannel string `yaml:"queue_channel"`
}

type LogConfig struct {
	Dir string `yaml:"dir"`
}

func Load(path string) (*Config, error) {
	raw, err := os.ReadFile(path)
	if err != nil {
		return nil, fmt.Errorf("read config %s: %w", path, err)
	}
	var cfg Config
	if err := yaml.Unmarshal(raw, &cfg); err != nil {
		return nil, fmt.Errorf("parse config %s: %w", path, err)
	}
	if cfg.HTTP.Addr == "" {
		cfg.HTTP.Addr = "127.0.0.1:8090"
	}
	if cfg.Websocket.Addr == "" {
		cfg.Websocket.Addr = "0.0.0.0:8080"
	}
	if cfg.JWT.ExpireHours == 0 {
		cfg.JWT.ExpireHours = 2
	}
	return &cfg, nil
}
