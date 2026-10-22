#!/bin/bash
while true; do php /data/wallet/token_config/yii ticker/currency >> /data/wallet/ticker-currency.log 2>&1; sleep 60; done &
