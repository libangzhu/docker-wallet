package db

import (
	"database/sql"
	"fmt"
	"time"

	_ "github.com/go-sql-driver/mysql"

	"wallet/internal/config"
	"wallet/internal/platform/log"
)

type DBs struct {
	Manage  *sql.DB
	Coin    *sql.DB
	Sources *sql.DB
	Prefix  string
}

func Open(cfg config.MySQLConfig) (*DBs, error) {
	manage, err := openOne(cfg.Manage.DSN, "p_manage")
	if err != nil {
		return nil, err
	}
	coin, err := openOne(cfg.Coin.DSN, "p_coin")
	if err != nil {
		_ = manage.Close()
		return nil, err
	}
	sources, err := openOne(cfg.Sources.DSN, "p_sources")
	if err != nil {
		_ = manage.Close()
		_ = coin.Close()
		return nil, err
	}
	return &DBs{
		Manage:  manage,
		Coin:    coin,
		Sources: sources,
		Prefix:  cfg.Manage.Prefix,
	}, nil
}

func openOne(dsn, name string) (*sql.DB, error) {
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, fmt.Errorf("open %s: %w", name, err)
	}
	db.SetMaxOpenConns(30)
	db.SetMaxIdleConns(10)
	db.SetConnMaxLifetime(30 * time.Minute)
	if err := db.Ping(); err != nil {
		log.Error.Printf("ping %s failed dsn_host_hidden err=%v", name, err)
		return db, nil
	}
	log.Info.Printf("mysql %s connected", name)
	return db, nil
}

func (d *DBs) Close() {
	if d.Manage != nil {
		_ = d.Manage.Close()
	}
	if d.Coin != nil {
		_ = d.Coin.Close()
	}
	if d.Sources != nil {
		_ = d.Sources.Close()
	}
}

func (d *DBs) Table(name string) string {
	return d.Prefix + name
}
