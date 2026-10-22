package des

import (
	"bytes"
	"crypto/des"
	"encoding/base64"
	"fmt"
)

type Cipher struct {
	key []byte
	iv  []byte
}

func New(key, iv string) *Cipher {
	return &Cipher{key: []byte(key), iv: []byte(iv)}
}

func (c *Cipher) Decrypt(encrypted string) (string, error) {
	raw, err := base64.StdEncoding.DecodeString(encrypted)
	if err != nil {
		return "", fmt.Errorf("base64 decode: %w", err)
	}
	block, err := des.NewCipher(normalizeKey(c.key))
	if err != nil {
		return "", fmt.Errorf("des cipher: %w", err)
	}
	if len(raw)%des.BlockSize != 0 {
		return "", fmt.Errorf("ciphertext length %d not multiple of block", len(raw))
	}
	out := make([]byte, len(raw))
	prev := c.iv
	if len(prev) < des.BlockSize {
		return "", fmt.Errorf("iv length %d", len(prev))
	}
	prev = prev[:des.BlockSize]
	for i := 0; i < len(raw); i += des.BlockSize {
		block.Decrypt(out[i:i+des.BlockSize], raw[i:i+des.BlockSize])
		for j := 0; j < des.BlockSize; j++ {
			out[i+j] ^= prev[j]
		}
		prev = raw[i : i+des.BlockSize]
	}
	plain, err := unpad(out)
	if err != nil {
		return "", err
	}
	return string(bytes.TrimRight(plain, "\x00")), nil
}

func normalizeKey(key []byte) []byte {
	if len(key) >= 8 {
		return key[:8]
	}
	out := make([]byte, 8)
	copy(out, key)
	return out
}

func unpad(b []byte) ([]byte, error) {
	if len(b) == 0 {
		return nil, fmt.Errorf("empty plaintext")
	}
	pad := int(b[len(b)-1])
	if pad == 0 || pad > len(b) {
		return nil, fmt.Errorf("invalid padding")
	}
	return b[:len(b)-pad], nil
}
