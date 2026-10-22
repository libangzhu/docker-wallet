#!/bin/bash
while true; do php /data_wallet/token_config/yii release-check/check >> /data_wallet/auto_check.log 2>&1; sleep 120; done &