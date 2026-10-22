#!/bin/bash
while true;
do
php /data_wallet/token_config/yii issue-chain-transfer/query-transaction 1 >> /data_wallet/issue-query.log 2>&1;
php /data_wallet/token_config/yii issue-chain-transfer/query-transaction 2 >> /data_wallet/issue-query.log 2>&1;
sleep 5;
done &