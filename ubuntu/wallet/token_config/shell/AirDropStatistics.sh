#!/bin/bash
while true;
do
php /data_wallet/token_config/yii air-drop/statistics >> /data_wallet/statistics.log 2>&1;
sleep 5;
done &