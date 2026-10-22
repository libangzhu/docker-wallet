<?php

namespace wallet\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use wallet\base\BaseController;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinCurrency;
use common\models\psources\CoinSupportedCurrency;
use common\service\trusteeship\TrusteeShipService;

class WalletController extends BaseController
{
    public function actionList()
    {
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->getPlatformId();
        if ('administrator' == $group) {
            $platforms = CoinPlatform::find()->select('id, name')->asArray()->all();
        } else {
            $platforms = CoinPlatform::find()->select('id, name')->where(['id' => $platform_id])->asArray()->all();
        }


        return ['code' => 0, 'msg' => 'ok', 'data' => $platforms];
    }

    public function actionWalletBallance()
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

        $type = Yii::$app->request->get('type', 1);
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 15);
        $currency = Yii::$app->request->get('currency ', '');
        $params = [
            'type' => $type,
            'page' => $page,
            'size' => $size,
            'currency' => $currency
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
        $result = $service->getWalletBalance($params);
        if (200 !== $result['code']) {
            return ['code' => $result['code'], 'data' => [], 'msg' => $result['msg']];
        }
        return ['code' => 1, 'data' => $result['msg'], 'msg' => 'success'];
    }

    public function actionUserAsset()
    {
        $platform_id = Yii::$app->request->getPlatformId();
        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];
        $uid = Yii::$app->request->get('uid', '');
        $params = [
            'uid' => $uid
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
        $result = $service->getUserAsset($params);
        if (200 !== $result['code']) {
            return ['code' => $result['code'], 'data' => [], 'msg' => $result['msg']];
        }
        return ['code' => 1, 'data' => $result['msg'], 'msg' => 'success'];
    }

    public function actionCurrency()
    {
        $code = 0;
        $msg = 'success';
        $data = null;

        if (Yii::$app->request->isGet) {
            $platform_id = Yii::$app->request->get('platform_id', 0);
            if (false == $platform_id) {
                $msg = '参数错误';
                $code = -1;
                goto doEnd;
            }
            $coin_currency = CoinCurrency::find()->select('id, pj_name, pj_symbol')->asArray()->all();

            $coin_supported_currency = CoinSupportedCurrency::find()->where(['platform_id' => $platform_id])->asArray()->all();
            $coin_supported_currency_id = ArrayHelper::getColumn($coin_supported_currency, 'currency_id');
            foreach ($coin_currency as &$val) {
                $val['checked'] = false;
                if (in_array($val["id"], $coin_supported_currency_id)) {
                    $val['checked'] = true;
                }
            }
            $data = $coin_currency;
            goto doEnd;
        }

        if (Yii::$app->request->isPost) {
            $platform_id = Yii::$app->request->getPlatformId();
            if (1 != $platform_id) {
                $msg = '权限未开通';
                $code = -1;
                goto doEnd;
            }
            $params = Yii::$app->request->post();
            $platform_id = isset($params['platform_id']) ? $params['platform_id'] : null;
            $currency_id = isset($params['currency_id']) ? $params['currency_id'] : null;
            $sort = isset($params['sort']) ? $params['sort'] : 1;
            $datas = [
                'platform_id' => $platform_id,
                'currency_id' => $currency_id,
                'sort' => $sort
            ];

            $isExist = CoinSupportedCurrency::find()->where(['platform_id' => $platform_id, 'currency_id' => $currency_id])->one();
            if (false != $isExist) {
                $msg = '记录已存在';
                $code = -1;
                goto doEnd;
            }
            $model = new CoinSupportedCurrency();
            $model->setScenario(CoinSupportedCurrency::SCENARIOS_CREATE);
            $model->load($datas, '');
            if (!$model->validate()) {
                $msg = $model->errors;
                $code = -1;
                goto doEnd;
            }
            $model->save();
            goto doEnd;
        }

        if (Yii::$app->request->isPut) {
            $platform_id = Yii::$app->request->getPlatformId();
            if (1 != $platform_id) {
                $msg = '权限未开通';
                $code = -1;
                goto doEnd;
            }
            $params = Yii::$app->request->post();
            $id = isset($params['id']) ? $params['id'] : null;
            $platform_id = isset($params['platform_id']) ? $params['platform_id'] : null;
            $currency_id = isset($params['currency_id']) ? $params['currency_id'] : null;
            $sort = isset($params['sort']) ? $params['sort'] : 1;
            $datas = [
                'platform_id' => $platform_id,
                'currency_id' => $currency_id,
                'sort' => $sort,
                'id' => $id
            ];

            $model = CoinSupportedCurrency::find()->where(['id' => $id])->one();
            if (false == $model) {
                $msg = '记录未存在';
                $code = -1;
                goto doEnd;
            }
            $model->setScenario(CoinSupportedCurrency::SCENARIOS_UPDATE);
            $model->load($datas, '');
            if (!$model->validate()) {
                $msg = $model->errors;
                $code = -1;
                goto doEnd;
            }
            $model->save();
            goto doEnd;
        }

        if (Yii::$app->request->isDelete) {
            $platform_id = Yii::$app->request->getPlatformId();
            if (1 != $platform_id) {
                $msg = '权限未开通';
                $code = -1;
                goto doEnd;
            }
            $params = Yii::$app->request->get();
            $id = isset($params['id']) ? $params['id'] : null;

            $model = CoinSupportedCurrency::find()->where(['id' => $id])->one();
            if (false == $model) {
                $msg = '记录未存在';
                $code = -1;
                goto doEnd;
            }
            $model->delete();
            goto doEnd;
        }

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}