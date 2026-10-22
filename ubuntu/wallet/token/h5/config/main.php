<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午10:26
 */

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id'                  => 'app-api',
    'basePath'            => dirname(__DIR__),
    'timeZone'            => 'Etc/GMT-8',
    'controllerNamespace' => 'api\controllers',
    'bootstrap'           => ['log'],
    'defaultRoute'        => 'site/index',
    'layout'              => false,
    'modules'             => [],
    'components'          => [
        'request'      => [
            'baseUrl'              => '/interface',
            'cookieValidationKey'  => '123456',
            'enableCsrfValidation' => false,
            'parsers'              => [
                'application/json' => [
                    'class'          => 'yii\web\JsonParser',
                    'throwException' => false,//json格式错误不报错
                ],
            ],
        ],
        'response'     => [
            'class'   => 'api\base\BaseResponse',
            'format'  => 'json',
            'charset' => 'UTF-8',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
        ],

    ],
    'params'              => $params,
];
