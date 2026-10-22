#!/bin/bash
while true; do php /data_wallet/token_config/yii ticker/exchange Zhaobi >> /data_wallet/ticker-exchange.log 2>&1; sleep 5; done &