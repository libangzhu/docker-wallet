#!/bin/bash

apt update
apt install nginx dos2unix -y

nginx
nginx -t
nginx -s reload

echo "nginx runing success"

echo "" > /data/wallet/initdev.sh
