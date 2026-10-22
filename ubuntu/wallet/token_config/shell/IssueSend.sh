#!/bin/bash
while true;
do
php /data_wallet/token_config/yii issue-chain-transfer/send-transaction 2 0 >> /data_wallet/issue-send.log 2>&1;
php /data_wallet/token_config/yii issue-chain-transfer/send-transaction 2 1 >> /data_wallet/issue-send.log 2>&1;
sleep 5;
done &