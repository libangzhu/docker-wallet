#!/bin/bash

apt update

apt-get install software-properties-common -y
add-apt-repository ppa:ondrej/php
apt update
apt install php7.3 php7.3-fpm php7.3-cli php7.3-common php7.3-json php7.3-opcache php7.3-xml php7.3-mbstring php7.3-zip php7.3-gd php7.3-mysql php7.3-curl php7.3-intl php7.3-readline -y

sed '36s/^/; /' -i /etc/php/7.3/fpm/pool.d/www.conf
sed '37i listen = 127.0.0.1:9000' -i /etc/php/7.3/fpm/pool.d/www.conf

php-fpm7.3 --fpm-config /etc/php/7.3/fpm/pool.d/www.conf

echo "php running success"

apt install nginx -y

nginx

nginx -t

nginx -s reload

echo "nginx runing success"

apt install dos2unix -y

echo "" > /data/wallet/initdev.sh
