<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 15:55:20
 * @authors rlgy <rlgyzhcn@qq.com>
 */

return [
    'vendorPath'     => '@vendor',
    'sourceLanguage' => 'en',
    'language'       => 'zh-CN',
    'timeZone'       => 'Etc/GMT-8',
    'aliases'        => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'bootstrap'      => [
        'log',
        'queue',
    ],
    'components'     => [
        'db'          => [
            'class'       => 'yii\db\Connection',
            'dsn'         => 'mysql:host=127.0.0.1;dbname=manage',
            'username'    => 'root',
            'password'    => '123456',
            'charset'     => 'utf8',
            'tablePrefix' => 'gli_',
        ],
        'db_pwallet'  => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=127.0.0.1;dbname=coin',
            'username' => 'root',
            'password' => '123456',
            'charset'  => 'utf8',
        ],
        'p_sources'   => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=127.0.0.1;dbname=p_sources',
            'username' => 'root',
            'password' => '123456',
            'charset'  => 'utf8',
        ],
        'cache'       => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port'     => 6379,
                'database' => 0,
            ],
        ],
        'redis'       => [
            'class'    => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port'     => 6379,
            'database' => 1,
        ],
        'redis-queue' => [
            'class'    => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port'     => 6379,
            'database' => 2,
        ],
        'queue'       => [
            'class'   => \yii\queue\redis\Queue::class,
            'redis'   => 'redis-queue', // Redis connection component or its config
            'channel' => 'queue', // Queue channel key
        ],
        'i18n'        => [
            'translations' => [
                '*' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'fileMap'  => [
                        'common' => 'common.php',
                        'error'  => 'errorCode.php',
                    ],
                ],
            ],
        ],
        'uploader'    => [
            'class'    => 'common\components\uploader\Uploader',
            'baseuri'  => 'http://127.0.0.1:8900/admin/upload',
            'absolute' => dirname(dirname(__DIR__)) . '/backend/upload',
        ],
        'log'         => [
            'targets' => [
                [
                    'class'      => 'yii\log\FileTarget',
                    'levels'     => ['info', 'warning'],
                    'categories' => [
                        'backend\*',
                        'api\*',
                        'common\*',
                        'console\*',
                        'h5\*',
                    ],
                    'logVars'    => [],
                ],
            ],
        ],
    ],
];
