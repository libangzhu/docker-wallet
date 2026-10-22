#!/bin/bash
while true; do php /data/wallet/token_config/yii exchange/quotation >> /data/wallet/ticker-update.log 2>&1; sleep 180; done &
