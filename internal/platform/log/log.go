package log

import (
	"log"
	"os"
	"path/filepath"
)

var (
	Info  = log.New(os.Stdout, "[INFO] ", log.LstdFlags|log.Lshortfile)
	Error = log.New(os.Stderr, "[ERROR] ", log.LstdFlags|log.Lshortfile)
)

func Init(dir string) error {
	if dir == "" {
		return nil
	}
	if err := os.MkdirAll(dir, 0o755); err != nil {
		return err
	}
	f, err := os.OpenFile(filepath.Join(dir, "wallet.log"), os.O_CREATE|os.O_APPEND|os.O_WRONLY, 0o644)
	if err != nil {
		return err
	}
	Info = log.New(f, "[INFO] ", log.LstdFlags|log.Lshortfile)
	Error = log.New(f, "[ERROR] ", log.LstdFlags|log.Lshortfile)
	return nil
}
