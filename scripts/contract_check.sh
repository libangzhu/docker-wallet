#!/bin/bash
# Compare Go service contracts for the locked App APIs.
set -euo pipefail
BASE="${1:-http://127.0.0.1:8090}"
fail=0
check() {
  local name="$1" url="$2" expect="$3"
  body=$(curl -sS -m 5 "$url" || true)
  if echo "$body" | grep -q "$expect"; then
    echo "OK $name"
  else
    echo "FAIL $name body=$body"
    fail=1
  fi
}
check "site" "$BASE/interface/site/index" 'welcome to api!'
check "health" "$BASE/health" '"status":"ok"'
check "supported-chain-missing-header" "$BASE/interface/supported-chain/index" '参数错误'
check "banner-missing" "$BASE/interface/banner/index" '参数错误'
check "notice-missing" "$BASE/interface/notice/list" '缺少必要的参数'
check "h5-icon-missing" "$BASE/h5/coin/get-icon-by-names" 'PARAMS NOT VALIDATE'
exit $fail
