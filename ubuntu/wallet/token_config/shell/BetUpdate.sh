#!/bin/bash
while true; do php /data_wallet/token_config/yii game-bet/bet-update >> /data_wallet/bet-update.log 2>&1; sleep 10; done &