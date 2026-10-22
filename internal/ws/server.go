package ws

import (
	"net/http"
	"sync"

	"github.com/gorilla/websocket"

	"wallet/internal/platform/log"
)

type Server struct {
	addr     string
	upgrader websocket.Upgrader
	mu       sync.Mutex
	conns    map[*websocket.Conn]struct{}
}

func New(addr string) *Server {
	return &Server{
		addr: addr,
		upgrader: websocket.Upgrader{
			CheckOrigin: func(r *http.Request) bool { return true },
		},
		conns: map[*websocket.Conn]struct{}{},
	}
}

func (s *Server) Start() {
	mux := http.NewServeMux()
	mux.HandleFunc("/", s.handle)
	log.Info.Printf("websocket listen %s", s.addr)
	go func() {
		if err := http.ListenAndServe(s.addr, mux); err != nil {
			log.Error.Printf("websocket listen failed addr=%s err=%v", s.addr, err)
		}
	}()
}

func (s *Server) handle(w http.ResponseWriter, r *http.Request) {
	conn, err := s.upgrader.Upgrade(w, r, nil)
	if err != nil {
		log.Error.Printf("websocket upgrade failed err=%v", err)
		return
	}
	s.mu.Lock()
	s.conns[conn] = struct{}{}
	s.mu.Unlock()
	defer func() {
		s.mu.Lock()
		delete(s.conns, conn)
		s.mu.Unlock()
		_ = conn.Close()
	}()
	for {
		mt, msg, err := conn.ReadMessage()
		if err != nil {
			return
		}
		if err := conn.WriteMessage(mt, msg); err != nil {
			return
		}
	}
}
