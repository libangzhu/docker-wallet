<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 15:55:20
 * @authors rlgy <rlgyzhcn@qq.com>
 */

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id'                  => 'app-backend',
    'basePath'            => '@backend',
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute'        => 'site/index',
    'layout'              => 'main',
    'components'          => [
        'user'         => [
            'class'           => 'yii\web\User',
            'loginUrl'        => ['login/login'],
            'enableAutoLogin' => true,
            'identityClass'   => 'common\models\Admin',
            'identityCookie'  => [
                'name'     => '_identity-backend',
                'httpOnly' => true,
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
                    'depends'    => [],
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                ],
            ],
            'linkAssets' => true, //true 清除缓存
        ],
        'request'      => [
            'class'               => 'common\core\Request',
            'baseUrl'             => '/admin',
            'cookieValidationKey' => 'Yr4XDePew55tutE3CVZ7vBUqVO3iorN6',
        ],
        'session'      => [
            'name' => 'manage-backend',
        ],
        'errorHandler' => [
            'errorAction' => 'public/error',
        ],
        'urlManager'   => [
            'class'           => 'common\core\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
            ],
        ],
    ],
    /**
     * 通过配置文件附加行为，全局
     */
    'as rbac'             => [
        'class'        => 'common\behaviors\RbacBehavior',
        'allowActions' => [
            'login/login',
            'login/logout',
            'public/*',
            'chat/*',
            'debug/*',
            'gii/*', // 不需要权限检测
        ],
    ],
    /**
     * 只允许一处登陆
     */
    'as check'            => [
        'class'  => 'common\filter\LoginFilter',
        'filter' => false, //[true：允许单 false：允许多]
    ],
    'params'              => $params,
];
