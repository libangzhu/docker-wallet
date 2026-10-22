<?php
return [
    'aliases'        => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset'
    ],
    'vendorPath'     => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => 'en',
    'language'       => 'zh-CN',
    'components'     => [
        'user'         => [
            'class'           => 'yii\web\User',
            'loginUrl'        => ['login/login'],
            'enableAutoLogin' => true,
            'identityClass'   => 'common\models\Admin',
            'identityCookie'  => [
                'name'     => '_identity-backend',
                'httpOnly' => true
            ],
        ],
        'authManager'  => [
            'class' => 'common\core\rbac\DbManager',
        ],
        'assetManager' => [
            'bundles'    => [
                'yii\web\JqueryAsset'          => [
                    'sourcePath' => null,
                    'js'         => [],
                    'depends'    => []
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => []
                ],
            ],
            'linkAssets' => false,//true 清除缓存
        ],
        'db'           => [
            'class'       => 'yii\db\Connection',
            'dsn'         => 'mysql:host=127.0.0.1;dbname=manage',
            'username'    => 'root',
            'password'    => '123456',
            'charset'     => 'utf8',
            'tablePrefix' => 'gli_',
        ],
        'db_pwallet'   => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=127.0.0.1;dbname=api',
            'username' => 'root',
            'password' => '123456',
            'charset'  => 'utf8',
        ],
        'p_sources'    => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=127.0.0.1;dbname=p_sources',
            'username' => 'root',
            'password' => '123456',
            'charset'  => 'utf8',
        ],
        'cache'        => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port'     => 6379,
                'database' => 0,
            ],
        ],
        'redis'        => [
            'class'    => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port'     => 6379,
            'database' => 1,
        ],
        'redis-queue'  => [
            'class'    => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port'     => 6379,
            'database' => 2,
        ],
        'queue'        => [
            'class'   => \yii\queue\redis\Queue::class,
            'redis'   => 'redis-queue', // Redis connection component or its config
            'channel' => 'queue', // Queue channel key
        ],
        'i18n'         => [
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
        'uploader'     => [
            'class'    => 'common\components\uploader\Uploader',
            'baseuri'  => 'http://127.0.0.1:8082/admin/upload',
            'absolute' => dirname(dirname(__DIR__)) . '/backend/web/upload',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => [
                        'backend\*',
                        'api\*',
                        'common\*',
                        'console\*',
                    ],
                    'logVars' => [],
                ]
            ],
        ]
    ],
    /**
     * 通过配置文件附加行为，全局
     */
    'as rbac'        => [
        'class'        => 'common\behaviors\RbacBehavior',
        'allowActions' => [
            'login/login',
            'login/logout',
            'public/*',
            'chat/*',
            'debug/*',
            'gii/*', // 不需要权限检测
        ]
    ],
    /**
     * 只允许一处登陆
     */
    'as check'       => [
        'class'  => 'common\filter\LoginFilter',
        'filter' => false,    //[true：允许单 false：允许多]
    ],
];
