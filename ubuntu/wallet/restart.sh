#!/bin/bash


php-fpm7.3 --fpm-config /etc/php/7.3/fpm/pool.d/www.conf
echo "php running success"

nginx
echo "nginx running success"

cd /data/wallet/bwallet_go/
nohup ./bwallet >>nohup.log 2>&1 &
echo "bwallet runing success"

cd /data/wallet/token_config/shell/
bash TickerCurrency.sh
bash TickerAll.sh
echo "sh runing success"
