#!/bin/bash

chmod -R 777 /data/wallet

cd /data/wallet/bwallet_go/
nohup ./bwallet >>nohup.log 2>&1 &
echo "go running success"

cd /data/wallet/token_config/shell/
dos2unix ./*
sh TickerCurrency.sh
sh TickerAll.sh
echo "sh running success"

nginx -s reload
echo "nginx running success"

echo "" > /data/wallet/runstart.sh
