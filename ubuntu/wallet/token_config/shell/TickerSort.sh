#!/bin/bash
while true; do php /data_wallet/token_config/yii ticker/sort >> /data_wallet/ticker-sort.log 2>&1; sleep 60; done &