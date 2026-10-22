#!/bin/bash

chmod -R 777 ./redis/log

timelog=$(date "+%Y-%m-%d %H:%M:%s")
a1=$( systemctl status docker | grep running | grep -v "grep" | wc -l )
if [[ $a1 -ge 1 ]];then
	echo "$timelog Docker status is normal, create and start container" >> ./runing.log
        docker-compose up -d
	sleep 20
	docker exec wallet-mysql /bin/sh -c "/data/initSQL.sh"
	docker exec wallet-ubuntu /bin/bash -c "/data/wallet/initdev.sh"
	sleep 5
	docker exec wallet-ubuntu /bin/bash -c "/data/wallet/runstart.sh"
	echo "$timelog Service startup successful, ready to clean up and create scripts" >> ./runing.log
	sleep 3
	echo "The initial creation script of the service is only valid during the first creation run" > ./SerCreate.sh 
else
	echo "$timelog Docker status abnormal, please check Docker service" >> ./runing.log
	echo "Docker status abnormal, please check Docker service"
fi

