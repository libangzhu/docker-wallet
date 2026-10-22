<?php

namespace wallet\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use wallet\base\BaseController;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinCurrency;
use common\models\psources\CoinSupportedCurrency;
use common\service\trusteeship\TrusteeShipService;

class AssetController extends BaseController
{
    public function actionIndex()
    {
        $current_platform_id = Yii::$app->request->getPlatformId();
        if (1 === $current_platform_id) {
            $platform_id = Yii::$app->request->get('platform_id', 1);
            $platform_id = empty($platform_id) ? 1 : $platform_id;
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }
        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $uri = Yii::$app->request->post('uri', '');
        $method = Yii::$app->request->post('method', 'GET');
        $params = Yii::$app->request->post('params', ['page' => 1, 'size' => 10]);

        $time = time();
        $appKey = isset($node_params['appKey']) ? $node_params['appKey'] : null;
        $appSecret = isset($node_params['appSecret']) ? $node_params['appSecret'] : null;
        $signature = self::getSign($params, $appKey, $appSecret, $time);

        $headers = [
            'FZM-Wallet-Signature' => $signature,
            'FZM-Wallet-Timestamp' => $time,
            'FZM-Wallet-AppKey' => $appKey,
            'FZM-Wallet-AppIp' => Yii::$app->request->userIP
        ];

        $service = new TrusteeShipService($node_params, $headers);
        $result = $service->handleAsset($uri, $method, $params);
        if (200 !== $result['code']) {
            return ['code' => $result['code'], 'data' => [], 'msg' => $result['msg']];
        }
        return ['code' => 1, 'data' => $result['msg'], 'msg' => 'success'];
    }
}