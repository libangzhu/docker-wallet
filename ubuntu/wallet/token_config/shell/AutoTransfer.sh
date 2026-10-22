#!/bin/bash
while true;
do
php /data_wallet/token_config/yii cross-chain/auto-transfer 1 0 >> /data_wallet/auto-transfer.log 2>&1;
php /data_wallet/token_config/yii cross-chain/auto-transfer 2 0 >> /data_wallet/auto-transfer.log 2>&1;
php /data_wallet/token_config/yii cross-chain/auto-transfer 3 0 >> /data_wallet/auto-transfer.log 2>&1;
php /data_wallet/token_config/yii cross-chain/auto-transfer 1 1 >> /data_wallet/auto-transfer.log 2>&1;
php /data_wallet/token_config/yii cross-chain/auto-transfer 2 1 >> /data_wallet/auto-transfer.log 2>&1;
php /data_wallet/token_config/yii cross-chain/auto-transfer 3 1 >> /data_wallet/auto-transfer.log 2>&1;
sleep 15;
done &