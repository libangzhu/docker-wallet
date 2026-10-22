#!/bin/bash


timelog=$(date "+%Y-%m-%d %H:%M:%s")
a1=$( systemctl status docker | grep running | grep -v "grep" | wc -l )
if [[ $a1 -ge 1 ]];then
	echo "$timelog Docker status is normal, starting to restart the container" >> ./runing.log
        docker-compose restart
	sleep 10
	docker exec wallet-ubuntu /bin/bash -c "/data/wallet/restart.sh"
else
	echo "$timelog Docker status is abnormal, please check if the Docker service is functioning properly" >> ./runing.log
fi

