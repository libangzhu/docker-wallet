#!/bin/bash
while true;
do
php /data_wallet/token_config/yii article >> /data_wallet/article.log 2>&1;
sleep 60;
done &