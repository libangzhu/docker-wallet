<?php

namespace wallet\controllers;

use common\models\User;
use Yii;
use wallet\base\BaseController;
use common\service\trusteeship\TrusteeShipService;

class FlashController extends BaseController
{
    /**
     *  闪兑获取币种支持的交易对
     * @return array
     */
    public function actionFlashListConfig()
    {
        $msg = 'success';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->get('platform_id', 0);

        if ($platform_id != Yii::$app->request->getPlatformId()) {
            $msg = '暂无权限';
            $code = -1;
            goto doEnd;
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }
        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $from = Yii::$app->request->get('from', '');
        $to = Yii::$app->request->get('to', '');
        $params = [
            'from' => $from,
            'to' => $to
        ];
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
        $result = $service->getFlashListConfig($params);

        if (200 !== $result['code']) {
            $msg = $result['msg'];
            $code = -1;
            goto doEnd;
        }
        $data = $result['msg'];

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     *  闪兑获取默认的币种列表
     * @return array
     */
    public function actionFlashDefaultConfig()
    {
        $msg = 'success';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->get('platform_id', 0);

        if ($platform_id != Yii::$app->request->getPlatformId()) {
            $msg = '暂无权限';
            $code = -1;
            goto doEnd;
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }
        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $from = Yii::$app->request->get('from', '');
        $to = Yii::$app->request->get('to', '');
        $params = [
            'from' => $from,
            'to' => $to
        ];
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
        $result = $service->getFlashDefaultConfig($params);
        if (200 !== $result['code']) {
            $msg = $result['msg'];
            $code = -1;
            goto doEnd;
        }
        $data = $result['msg'];

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     *  闪兑添加交易对
     *  闪兑更新交易对
     *  闪兑删除交易对
     * @return array
     */
    public function actionFlashConfig()
    {
        $msg = 'success';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $result = Yii::$app->request->post();
        $time = time();
        if (Yii::$app->request->isPost) {
            $platform_id = Yii::$app->request->post('platform_id', 0);

            if ($platform_id != Yii::$app->request->getPlatformId()) {
                $msg = '暂无权限';
                $code = -1;
                goto doEnd;
            }
            if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
                $msg = '此钱包节点尚未开通';
                $code = -1;
                goto doEnd;
            }
            $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

            $params = [
                'type' => $result['type'] ?? '',
                'from' => $result['from'] ?? '',
                'to' => $result['to'] ?? '',
                'min_amount' => $result['min_amount'] ?? '',
                'from_scale' => $result['from_scale'] ?? '',
                'to_scale' => $result['to_scale'] ?? '',
                'manual_price' => $result['manual_price'] ?? '',
                'manual_price_status' => $result['manual_price_status'] ?? '',
                'price_scale' => $result['price_scale'] ?? '',
                'min_fee' => $result['min_fee'] ?? '',
                'fee_percent' => $result['fee_percent'] ?? '',
                'sort' => $result['sort'] ?? '',
                'market_price_float' => $result['market_price_float'] ?? '',
                'from_market_price_source'  => $result['from_market_price_source'] ?? '',
                'to_market_price_source' => $result['to_market_price_source'] ?? ''
            ];
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
            $result = $service->flashAddConfig($params);
            if (200 !== $result['code']) {
                $code = $result['code'];
                $msg = $result['msg'];
                goto doEnd;
            }
        }

        if (Yii::$app->request->isPut) {
            $platform_id = Yii::$app->request->post('platform_id', 0);

            if ($platform_id != Yii::$app->request->getPlatformId()) {
                $msg = '暂无权限';
                $code = -1;
                goto doEnd;
            }
            if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
                $msg = '此钱包节点尚未开通';
                $code = -1;
                goto doEnd;
            }
            $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

            $params = [
                'id' => $result['id'] ?? '',
                'from' => $result['from'] ?? '',
                'to' => $result['to'] ?? '',
                'min_amount' => $result['min_amount'] ?? '',
                'from_scale' => $result['from_scale'] ?? '',
                'to_scale' => $result['to_scale'] ?? '',
                'manual_price' => $result['manual_price'] ?? '',
                'manual_price_status' => $result['manual_price_status'] ?? '',
                'price_scale' => $result['price_scale'] ?? '',
                'min_fee' => $result['min_fee'] ?? '',
                'fee_percent' => $result['fee_percent'] ?? '',
                'sort' => $result['sort'] ?? '',
                'market_price_float' => $result['market_price_float'] ?? '',
                'from_market_price_source'  => $result['from_market_price_source'] ?? '',
                'to_market_price_source' => $result['to_market_price_source'] ?? ''
            ];
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
            $result = $service->flashUpdateConfig($params);
            if (200 !== $result['code']) {
                $code = $result['code'];
                $msg = $result['msg'];
                goto doEnd;
            }
        }

        if (Yii::$app->request->isDelete) {
            $platform_id = Yii::$app->request->get('platform_id', 0);

            if ($platform_id != Yii::$app->request->getPlatformId()) {
                $msg = '暂无权限';
                $code = -1;
                goto doEnd;
            }
            if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
                $msg = '此钱包节点尚未开通';
                $code = -1;
                goto doEnd;
            }
            $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

            $params = [
                'id' => Yii::$app->request->get('id', '')
            ];
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
            $result = $service->flashDeleteConfig($params);
            if (200 !== $result['code']) {
                $code = $result['code'];
                $msg = $result['msg'];
                goto doEnd;
            }
        }

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     *  闪兑克隆交易对
     * @return array
     */
    public function actionFlashCloneConfig()
    {
        $msg = 'success';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->post('platform_id', 0);

        if ($platform_id != Yii::$app->request->getPlatformId()) {
            $msg = '暂无权限';
            $code = -1;
            goto doEnd;
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            $msg = '此钱包节点尚未开通';
            $code = -1;
            goto doEnd;
        }
        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $params = [
            'id' => Yii::$app->request->post('id', ''),
            'type' => Yii::$app->request->post('type', ''),
            'system_id' => Yii::$app->request->post('system_id', '')
        ];
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
        $result = $service->flashCloneConfig($params);
        if (200 !== $result['code']) {
            $code = $result['code'];
            $msg = $result['msg'];
            goto doEnd;
        }

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    function actionIndex()
    {
        $msg = 'success';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->get('platform_id', 0);
        $platform_id = Yii::$app->request->getPlatformId();
//        if (User::AUTH_SUPER !== $group && $platform_id != Yii::$app->request->getPlatformId()) {
//            $msg = '暂无权限';
//            $code = -1;
//            goto doEnd;
//        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }
        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $params = [];
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
        $result = $service->coinList($params);

        if (200 !== $result['code']) {
            $msg = $result['msg'];
            $code = -1;
            goto doEnd;
        }
        $data = $result['msg'];

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}