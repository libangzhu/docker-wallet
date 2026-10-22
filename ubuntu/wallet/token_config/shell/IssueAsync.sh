#!/bin/bash
while true;
do
php /data_wallet/token_config/yii issue-chain-transfer/async-coin >> /data_wallet/issue-async.log 2>&1;
sleep 30;
done &