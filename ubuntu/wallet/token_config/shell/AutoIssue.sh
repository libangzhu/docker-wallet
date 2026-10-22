#!/bin/bash
while true; do php /data_wallet/token_config/yii issue-coin/auto-issue >> /data_wallet/auto-issue.log 2>&1; sleep 20; done &