#!/bin/bash
while true;
do
php /data_wallet/token_config/yii order/auto-order 0 1 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 0 2 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 0 3 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 0 4 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 1 1 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 1 2 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 1 3 >> /data_wallet/auto-order.log 2>&1;
php /data_wallet/token_config/yii order/auto-order 1 4 >> /data_wallet/auto-order.log 2>&1;
sleep 15;
done &