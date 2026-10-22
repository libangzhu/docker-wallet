<?php

namespace api\controllers;

use common\models\psources\Coin;
use common\models\psources\CoinPlatformWithHold;
use common\service\chain33\Chain33Service;
use Yii;
use api\base\BaseController;
use common\models\psources\WalletChain;
use yii\data\Pagination;

class WalletChainController extends BaseController
{
    /**
     * h5发行链列表
     * @param string wallet_address
     * @return array
     */
    public function actionChains()
    {
        if (!Yii::$app->request->isGet) {
            $this->code = -1;
            $this->msg = '请求方式错误!';
            goto doEnd;
        }

        $params = Yii::$app->request->get();
        $wallet_address = isset($params['wallet_address']) ? $params['wallet_address'] : '';

        if (false == $wallet_address) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }

        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 10);

        $query = CoinPlatformWithHold::find()
            ->select('id, token, platform, status, create_time')
            ->where(['wallet_address' => $wallet_address])
            ->orderBy('id');


        $model = $query->offset(($page - 1) * $size)->orderBy('create_time desc')->limit($size)->asArray()->all();

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $size]);
        $this->data = [
            'list' => $model,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize' => $size,
                'currentPage' => $page,
            ]
        ];


        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    /**
     * h5发行链
     * @param string platform
     * @param string address
     * @param string private_key
     * @param string fee
     * @param string token
     * @param string host
     * @param string port
     * @param string wallet_address
     * @param string hash
     * @return array
     */
    public function actionChain()
    {
        $request = Yii::$app->request;
        $post = $request->post();

        if ($request->isPost) {
            $model = new CoinPlatformWithHold();
            $model->setScenario(CoinPlatformWithHold::SCENARIOS_CREATE);
            $params = [
                'platform' => isset($post['platform']) ? $post['platform'] : '',
                'address' => isset($post['address']) ? $post['address'] : '',
                'private_key' => isset($post['private_key']) ? $post['private_key'] : '',
                'token' => isset($post['token']) ? strtoupper($post['token']) : '',
                'host' => isset($post['host']) ? $post['host'] : '',
                'port' => isset($post['port']) ? $post['port'] : '',
                'wallet_address' => isset($post['wallet_address']) ? $post['wallet_address'] : '',
                'status' => CoinPlatformWithHold::STATUS_UNPAID,
                'origin' => CoinPlatformWithHold::ORIGIN_USER,
                'fee' => isset($post['fee']) ? $post['fee'] : '',
                'exer' => 'user.p.' . (isset($post['platform']) ? $post['platform'] : '')
            ];

            if ($model->load($params, '') && !$model->validate()) {
                $errors = '';
                foreach ($model->errors as $error) {
                    $errors .= $error[0];
                }
                $this->msg = $errors;
                $this->code = -1;
                goto doEnd;
            }

            $node_params = Yii::$app->params['para'];
            $service = new Chain33Service($node_params);
            $result = $service->addPara($params['platform'], $params['host'] . ':' . $params['port']);
            if (0 != $result['code']) {
                $this->code = $result['code'];
                $this->msg = $result['msg'];
                goto doEnd;
            }

            $model->save();
            $this->data = (int)Yii::$app->p_sources->getLastInsertID();
            goto doEnd;
        }

        if ($request->isPut) {
            $id = isset($post['id']) ? $post['id'] : 0;
            $hash = isset($post['hash']) ? $post['hash'] : 0;
            if (false == $id || false == $hash) {
                $this->msg = '缺少必要的参数';
                $this->code = -1;
                goto doEnd;
            }

            $model = CoinPlatformWithHold::findOne($id);
            if (empty($model)) {
                $this->msg = '数据不存在';
                $this->code = -1;
                goto doEnd;
            }
            $model->setScenario(CoinPlatformWithHold::SCENARIOS_UPDATE);
            $params = [
                'id' => $id,
                'hash' => $hash,
                'status' => CoinPlatformWithHold::STATUS_CREATEING
            ];
            if ($model->load($params, '') && !$model->save()) {
                $errors = '';
                foreach ($model->errors as $error) {
                    $errors .= $error[0];
                }
                $this->msg = $errors;
                $this->code = -1;
                goto doEnd;
            }

            $this->syncCoin(['platform' => $model['platform'], 'token' => $model['token']]);

        }

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    /**
     * h5发行链
     * @param string wallet_address
     * @return array
     */
    public function actionDetail()
    {
        if (!Yii::$app->request->isGet) {
            $this->code = -1;
            $this->msg = '请求方式错误!';
            goto doEnd;
        }

        $params = Yii::$app->request->get();
        $id = isset($params['id']) ? $params['id'] : '';

        if (false == $id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }


        $model = CoinPlatformWithHold::find()->select('platform, token, address, private_key, fee, host, port, hash, status, create_time')->where(['id' => (int)$id])->asArray()->one();
        if (empty($model)) {
            goto doEnd;
        }
        $model['fee'] = $model['fee'] . $model['token'];
        $model['charge'] = '10BTY';
        $this->data = $model;

        goto doEnd;

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    public function syncCoin($params = [])
    {
        $model_coin = Coin::find()->where(['name' => $params['token'], 'platform' => $params['platform'], 'chain' => 'BTY'])->one();
        if (empty($model_coin)) {
            $model = new Coin();
            $model->nickname = ['ja' => '', 'en-US' => '', 'zh-CN' => ''];
            $model->name = $params['token'];
            $model->platform = $params['platform'];
            $model->chain = 'BTY';
            $model->treaty = 2;
            $model->platform_id = 1;
            $model->save();
        }
    }
}