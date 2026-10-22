<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id'                  => 'app-wallet',
    'basePath'            => '@wallet',
    'controllerNamespace' => 'wallet\controllers',
    'defaultRoute'        => 'site/index',
    'layout'              => false,
    'components'          => [
        'request'      => [
            'baseUrl'              => '/api',
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
            'class'   => 'wallet\base\BaseResponse',
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
