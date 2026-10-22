#!/bin/bash

# mysql用户信息
DB_USER="root"
DB_PASS="pu2Gfxnj"

# 初始化数据库
mysql -u $DB_USER --password=$DB_PASS -e "CREATE DATABASE IF NOT EXISTS p_coin DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci;"
mysql -u $DB_USER --password=$DB_PASS -e "CREATE DATABASE IF NOT EXISTS p_manage DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci;"
mysql -u $DB_USER --password=$DB_PASS -e "CREATE DATABASE IF NOT EXISTS p_sources DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci;"
mysql -u $DB_USER --password=$DB_PASS p_coin < /data/p_coin.sql
mysql -u $DB_USER --password=$DB_PASS p_manage < /data/p_manage.sql
mysql -u $DB_USER --password=$DB_PASS p_sources < /data/p_sources.sql

rm /data/p_coin.sql
rm /data/p_manage.sql
rm /data/p_sources.sql

echo "" > /data/initSQL.sh
