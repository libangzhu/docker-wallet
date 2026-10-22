<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
	'timeZone' => 'Etc/GMT-8',
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
	'defaultRoute' => 'site/index',
	'layout' => 'main',
	'modules' => [],
	'components' => [
		'request' => [
			'class' => 'common\core\Request',
			'baseUrl' => '/admin',
			'cookieValidationKey' => 'Yr4XDePew55tutE3CVZ7vBUqVO3iorN6',
			//'csrfParam' => '_csrf-backend',
		],
        'session' => [
            'name' => 'manage-backend',
        ],
		'errorHandler' => [
			'errorAction' => 'public/error',
		],
		'urlManager' => [
			'class' => 'common\core\UrlManager',
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
			],
		]
    ],
    'params' => $params,
];
