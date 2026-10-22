#!/bin/bash
while true;
do
php /data_wallet/token_config/yii air-drop/reach >> /data_wallet/reach.log 2>&1;
sleep 3600;
done &