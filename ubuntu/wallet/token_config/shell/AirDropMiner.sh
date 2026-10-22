#!/bin/bash
while true;
do
php /data_wallet/token_config/yii air-drop/miner-source-list >> /data_wallet/miner.log 2>&1;
sleep 5;
done &