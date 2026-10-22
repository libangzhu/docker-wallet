#!/bin/bash
while true; do php /data_wallet/token_config/yii ticker/hot >> /data_wallet/ticker-hot.log 2>&1; sleep 10; done &