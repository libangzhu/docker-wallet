#!/bin/bash
while true; do php /data_wallet/token_config/yii cross-chain/query-transaction >> /data_wallet/query-transaction.log 2>&1; sleep 5; done &