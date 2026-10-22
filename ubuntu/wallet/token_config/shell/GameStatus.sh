#!/bin/bash
while true; do php /data_wallet/token_config/yii game-bet/game-status >> /data_wallet/game-status.log 2>&1; sleep 15; done &