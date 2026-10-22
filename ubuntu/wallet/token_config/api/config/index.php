<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午10:26
 */

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
define('WALLET_PROJECT_PATH', '/data_wallet/token'); //定义框架代码的目录
define('WALLET_RUNTIME_PATH', dirname(__DIR__)); //定义程序运行的目录
require_once __DIR__ . '/../common/helper/InitAppHelper.php';

//$realip = InitAppHelper::getUserIp();
//$ip_list = file_get_contents('http://172.16.0.230:8080/wallet_interface_ip_limit.txt');
//$ip_arr = explode("\n", $ip_list);
//foreach ($ip_arr as $key => $ip) {
//    if ($ip == $realip) {
//        $data = [
//            'code' => 403,
//            'msg' => '请求次数过多!'
//        ];
//        echo json_encode($data);exit;
//    }
//}

require WALLET_PROJECT_PATH . '/vendor/autoload.php';
require WALLET_PROJECT_PATH . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/config/main.php',
    require __DIR__ . '/config/main-local.php'
);

(new yii\web\Application($config))->run();
