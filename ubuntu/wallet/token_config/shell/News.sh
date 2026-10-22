#!/bin/bash
while true;
do
php /data_wallet/token_config/yii news >> /data_wallet/news.log 2>&1;
sleep 60;
done &