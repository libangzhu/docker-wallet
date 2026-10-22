### README ###

#### 目录结构说明 ####
```
_
|
|token
|   |____ api: 钱包APP接口项目
|   |____ backend: 钱包管理后台
|   |____ h5: 钱包H5 APP接口
|   |____ console: cli项目
|   |____ common: 公共代码库
|   |____ vendor:  依赖库
|
|token_config: web 根目录 index.php 为入口文件
    |____ build.sh: web 目录初始化脚步
```

#### 队列任务 ####
1. 行情更新任务: 使用系统 crontab
```
# m h  dom mon dow   command
* * * * * /var/www/html/token_config/yii exchange/quotation &>/dev/null
```

2. yii 队列任务监听: 使用 supervisor
```
# P钱包
[program:yii-queue-pwallet]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php7.1 /var/www/html/token_pwallet/yii queue/listen --verbose=0 --color=0
autostart=true
autorestart=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/token_pwallet/console/runtime/logs/yii-queue.log
```