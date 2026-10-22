#!/bin/bash

mkdir -p /data/wallet/runtime/logs

nginx
echo "nginx running success"

cd /data/wallet/bwallet_go/
nohup ./bwallet >>nohup.log 2>&1 &
echo "bwallet runing success"

cd /data/wallet
nohup ./bin/wallet -config /data/wallet/configs/config.yaml >> /data/wallet/runtime/logs/wallet.nohup.log 2>&1 &
echo "wallet running success"
