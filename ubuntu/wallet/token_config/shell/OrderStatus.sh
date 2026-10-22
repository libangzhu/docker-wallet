#!/bin/bash
while true; do php /data_wallet/token_config/yii order/order-status >> /data_wallet/order-status.log 2>&1; sleep 5; done &