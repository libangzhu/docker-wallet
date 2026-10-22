#!/bin/bash
while true;
do
php /data_wallet/token_config/yii issue-chain-transfer/update-chain >> /data_wallet/update-chain.log 2>&1;
sleep 60;
done &