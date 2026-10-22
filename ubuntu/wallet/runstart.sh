#!/bin/bash

chmod -R 777 /data/wallet
mkdir -p /data/wallet/runtime/logs

cd /data/wallet/bwallet_go/
nohup ./bwallet >>nohup.log 2>&1 &
echo "go running success"

cd /data/wallet
nohup ./bin/wallet -config /data/wallet/configs/config.yaml >> /data/wallet/runtime/logs/wallet.nohup.log 2>&1 &
echo "wallet running success"

nginx -s reload
echo "nginx running success"

echo "" > /data/wallet/runstart.sh
