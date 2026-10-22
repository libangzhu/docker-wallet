package main

import (
	"context"
	"flag"
	"net/http"
	"os"
	"os/signal"
	"path/filepath"
	"syscall"
	"time"

	"wallet/internal/app"
	"wallet/internal/config"
	"wallet/internal/httpserver"
	"wallet/internal/job"
	"wallet/internal/platform/db"
	"wallet/internal/platform/log"
	redisx "wallet/internal/platform/redis"
	"wallet/internal/ws"
)

func main() {
	cfgPath := flag.String("config", "configs/config.yaml", "config file")
	mode := flag.String("mode", "serve", "serve or job")
	jobName := flag.String("job", "", "job name when mode=job")
	flag.Parse()

	cfg, err := config.Load(*cfgPath)
	if err != nil {
		log.Error.Printf("load config failed path=%s err=%v", *cfgPath, err)
		os.Exit(1)
	}
	if err := log.Init(cfg.Log.Dir); err != nil {
		log.Error.Printf("init log failed dir=%s err=%v", cfg.Log.Dir, err)
	}

	databases, err := db.Open(cfg.MySQL)
	if err != nil {
		log.Error.Printf("open mysql failed err=%v", err)
		os.Exit(1)
	}
	defer databases.Close()
	rdb := redisx.Open(cfg.Redis)
	defer rdb.Close()

	deps := app.New(cfg, databases, rdb)
	sched := job.New(deps)

	if *mode == "job" {
		if *jobName == "" {
			log.Error.Printf("job name required")
			os.Exit(1)
		}
		sched.RunNamed(context.Background(), *jobName, flag.Args())
		return
	}

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()
	sched.Start(ctx)
	sched.ConsumeQueue(ctx)
	ws.New(cfg.Websocket.Addr).Start()

	viewDir := filepath.Join("web", "admin")
	srv := httpserver.Listen(deps, viewDir)
	go func() {
		if err := srv.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Error.Printf("http server failed addr=%s err=%v", cfg.HTTP.Addr, err)
			os.Exit(1)
		}
	}()

	ch := make(chan os.Signal, 1)
	signal.Notify(ch, syscall.SIGINT, syscall.SIGTERM)
	<-ch
	cancel()
	shutdown, done := context.WithTimeout(context.Background(), 10*time.Second)
	defer done()
	if err := srv.Shutdown(shutdown); err != nil {
		log.Error.Printf("http shutdown failed err=%v", err)
	}
}
