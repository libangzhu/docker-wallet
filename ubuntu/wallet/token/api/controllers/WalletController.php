<?php

namespace api\controllers;

use api\base\BaseController;
use common\models\psources\Coin;
use common\models\psources\CoinAirDropTrade;
use common\models\psources\CoinIssueTransfer;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinPlatformWithHold;
use common\models\psources\WalletSupportedTokenCategory;
use common\service\chain33\Chain33Service;
use Yii;


class WalletController extends BaseController
{
    public function actionTokenCategory()
    {
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        if (empty($platform_id)) {
            $this->msg = '缺少必要参数';
            $this->code = -1;
            goto doEnd;
        }
        $this->data = [
            [
                'category' => '首页',
                'chain' => null
            ],
            [
                'category' => '首页',
                'chain' => null
            ]
        ];
        $data = WalletSupportedTokenCategory::find()->select('category, chain')->where(['platform_id' => (int)$platform_id])->asArray()->all();
        if (empty($data)) {
            goto doEnd;
        }
        $this->data = array_merge($this->data, $data);

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    public function actionInfo()
    {
        $code = 0;
        $msg = 'success';
        $platform_id = Yii::$app->request->get('platform_id', '');
        if (empty($platform_id)) {
            $msg = '参数不能为空';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $data = CoinPlatform::find()->select("name, download_url, introduce, summary,")->where(['id' => $platform_id])->asArray()->one();
        if (empty($data)) {
            $msg = '数据不存在';
            $data = null;
            $code = -1;
            goto doEnd;
        }
        $data['title'] = $data['name'];
        unset($data['name']);

        doEnd :
        return ['code' => $code, 'data' => $data, 'msg' => $msg];
    }

    public function actionGameTradeUpdate()
    {
        $coinAirDropTrade = CoinAirDropTrade::find()->where(['attach' => 2, 'msg' => '0'])->limit(30)->all();
        foreach ($coinAirDropTrade as $val) {
            $fee = 100000;
            $amount = 1 * 1e8;
            $execer = 'coins';
            $note = '';

            $service = new Chain33Service();
            $createRawTransaction = $service->createRawTransaction($val->coins_address, $amount, $fee, $note, $execer);
            if (0 != $createRawTransaction['code']) {
                continue;
            }

            $txHex = $createRawTransaction['result'];
            $privkey = '72c3879f1f9b523f266a9545b69bd41c0251483a93e21e348e85118afe17a5e2';
            $expire = '1m';

            $signRawTx = $service->signRawTx($privkey, $txHex, $expire);
            if (0 != $signRawTx['code']) {
                continue;
            }
            $sign_str = $signRawTx['result'];
            $result = $service->sendTransaction($sign_str);

            if (0 != $result['code']) {
                continue;
            }
            $currentModel = CoinAirDropTrade::findOne($val->id);
            $currentModel->attach = 2;
            $currentModel->balance = 0;
            $currentModel->msg = $result['result'];
            $currentModel->save();
        }
        return ['code' => 1, 'msg' => 'ok'];
    }

    public function actionGetBalance()
    {
        $platform_id = Yii::$app->request->get('platform_id', '');
        $token = Yii::$app->request->get('address', '');
        $symbol = Yii::$app->request->get('symbol', '');
        if (empty($platform_id) || empty($token) || empty($symbol)) {
            $this->msg = '参数不能为空';
            $this->code = -1;
            goto doEnd;
        }

        $chain_model = CoinPlatform::find()->select('chain_id')->where(['id' => $platform_id])->andWhere(['<>', 'chain_id', 0])->one();
        if (empty($chain_model)) {
            $this->msg = '此功能为付费功能，有兴趣请与销售联系';
            $this->code = -1;
            goto doEnd;
        }
        $platform = CoinPlatformWithHold::find()->select('exer, platform')->where(['id' => $chain_model->chain_id])->one();
        if (empty($platform)) {
            $this->msg = '此功能为付费功能，有兴趣请与销售联系';
            $this->code = -1;
            goto doEnd;
        }

        $node = Yii::$app->params['chain_nodes'][strtoupper($platform->platform)] ?? null;
        if (empty($node)) {
            $this->msg = '此功能为付费功能，有兴趣请与销售联系';
            $this->code = -1;
            goto doEnd;
        }

        $coin_model = Coin::find()->select('name, treaty')->where(['name' => strtoupper($symbol), 'platform' => $platform->platform])->one();

        $switch = false;
        if (empty($coin_model)) {
            $execer = $platform->exer;
        } else {
            if (1 == $coin_model->treaty) {
                $execer = 'user.p.' . $chain_model->chain->platform . '.token';
            } else {
                $execer = 'user.p.' . $chain_model->chain->platform . '.coins';
                $switch = true;
            }
        }

        if ('BTY' == strtoupper($symbol)) {
            $switch = true;
            $node = \Yii::$app->params['chain_parallel']['primary'];
        }

        $service = new Chain33Service($node);

        if (false == $switch) {
            $result = $service->getTokenBalance($token, $execer, $symbol);
        } else {
            $result = $service->getBalance($token, $execer = '');
        }

        if (0 !== $result['code']) {
            $this->msg = '当前发币人数过多，请客官稍后再试';
            $this->code = -1;
            goto doEnd;
        }
        $this->data = $result['result'];
        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    public function actionTransfer()
    {
        $code = -1;
        $request = Yii::$app->request;
        $post = $request->post();
        if (!$request->isPost) {
            $msg = '请求错误!';
            goto doEnd;
        }

        $txhex = isset($post['txhex']) ? $post['txhex'] : '';
        $issue_coin_id = isset($post['issue_coin_id']) ? $post['issue_coin_id'] : 0;
        if (false == $txhex || false == $issue_coin_id) {
            $msg = '参数错误!';
            goto doEnd;
        }

        $model = new CoinIssueTransfer();
        $data['txhex'] = $txhex;
        $data['issue_coin_id'] = (int)$issue_coin_id;
        $model->load($data, '');
        $model->save();

        $code = 0;
        $msg = 'success';
        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }
}