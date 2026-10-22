#!/bin/bash
while true; do php /data_wallet/token_config/yii release-check/confirm-hash >> /data_wallet/confirm_hash.log 2>&1; sleep 300; done &