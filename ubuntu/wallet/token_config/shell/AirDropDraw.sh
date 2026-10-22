#!/bin/bash
while true;
do
php /data_wallet/token_config/yii air-drop/draw >> /data_wallet/draw.log 2>&1;
sleep 5;
done &