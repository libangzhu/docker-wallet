<?php

namespace api\controllers;

use api\base\BaseController;
use common\models\psources\CoinAirDropTrade;
use common\models\psources\CoinCrossChain;
use common\service\chain33\Chain33Service;
use Yii;
use yii\db\Exception;


class CrossChainController extends BaseController
{
    public function actionTransfer()
    {
        $code = -1;
        $request = Yii::$app->request;
        if (!$request->isPost) {
            $msg = '请求错误!';
            goto doEnd;
        }
        $post = $request->post();
        if (3 != count($post['txs'])) {
            $msg = '交易笔数错误!';
            goto doEnd;
        }

        $data['is_with_draw'] = (false == $post['isWithdraw']) ? 0 : 1;
        $data['address'] = $post['address'];
        $data['transfer_number'] = date('YmdHis') . self::getrandnums();

        $redis = Yii::$app->redis;
        if ($redis->exists($post['address'] . '_' . $data['is_with_draw'])) {
            $msg = '存在未完成的交易!';
            goto doEnd;
        }
        $redis->set($post['address'] . '_' . $data['is_with_draw'], $post['address'] . '_' . $data['is_with_draw']);

//        $isExist = CoinCrossChain::find()
//            ->where(['address' => $post['address'], 'is_with_draw' => $data['is_with_draw']])
//            ->andWhere(['<>', 'query_result', 'success'])
//            ->asArray()->all();
//
//        if ($isExist) {
//            $msg = '存在未完成的交易!';
//            goto doEnd;
//        }

        foreach ($post['txs'] as $key => $val) {
            $model = new CoinCrossChain();
            $data['txhex'] = $val['tx'];
            $data['transfer_url'] = $val['url'];
            $data['step'] = $val['step'];
            $data['amount'] = isset($val['amount']) ? $val['amount'] : 0;
            $model->load($data, '');
            $model->save();
        }

        $code = 0;
        $msg = 'success';
        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    public function actionFixTransfer()
    {
        $code = -1;
        $request = Yii::$app->request;
        if (!$request->isPost) {
            $msg = '请求错误!';
            goto doEnd;
        }
        $post = $request->post();

        $data['is_with_draw'] = (false == $post['isWithdraw']) ? 0 : 1;
        $data['address'] = $post['address'];

        foreach ($post['txs'] as $key => $val) {
            $model = CoinCrossChain::find()->where(['is_with_draw' => $data['is_with_draw']])
                ->andWhere(['address' => $data['address']])
                ->andWhere(['step' => $val['step']])
                ->andWhere(['<>', 'msg', 'success'])
                ->one();

            if(false == $model) continue;
//            $node_params = $model->transfer_url;
//            $node_params = explode(':', $node_params);
//            $node_params = [
//                'scheme' => $node_params[0],
//                'host' => str_replace('//', '', $node_params[1]),
//                'port' => isset($node_params[2]) ? $node_params[2] : ''
//            ];
//            $service = new Chain33Service($node_params);
//            $send_result = $model->send_result;
//            $result = $service->QueryTransaction($send_result);
//            if (isset($result['result']['actionName']) && 'unknown' == $result['result']['actionName']) {
//                $model->query_result = 'success';
//                $model->msg = 'success';
//                $model->save();
//                continue;
//            } else if (isset($result['result']['receipt']['ty']) && 2 == $result['result']['receipt']['ty']) {
//                $model->query_result = 'success';
//                $model->msg = 'success';
//                $model->save();
//                continue;
//            } else {
                $model->txhex = $val['tx'];
                $model->transfer_url = $val['url'];
                $model->send_result = 0;
                $model->query_result = 0;
                $model->msg = 0;
                $model->save();
                continue;
//            }
        }
        $code = 0;
        $msg = 'success';
        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    public function actionTransferStatus()
    {
        $code = -1;
        $msg = 'fail';
        $data = null;
        $step = $amount = 0;
        $request = Yii::$app->request;
        if (!$request->isGet) {
            $msg = '请求错误!';
            goto doEnd;
        }

        $address = $request->get('address', '');
        $is_with_draw = $request->get('isWithdraw', '');
        if (empty($address)) {
            $msg = '参数错误!';
            goto doEnd;
        }

        $model = CoinCrossChain::find()->where(['address' => $address, 'is_with_draw' => $is_with_draw])->orderBy("id desc")->limit(3)->asArray()->all();
        if (empty($model)) {
            $msg = '数据不存在!';
            $code = 0;
            goto doEnd;
        }
        sort($model);

        foreach ($model as $key => $val) {
            $amount = $val['amount'];
            //未交易
            if ('0' == $val['send_result'] && '0' == $val['query_result'] && '0' == $val['msg']) {
                $step = $key + 1;
                $code = 0;
                $msg = '第' . ($key + 1) . '笔交易尚未执行';
                goto doEnd;
            }
            //交易报错
            if ('0' == $val['send_result'] && '0' == $val['query_result'] && true == $val['msg']) {

                $step = $key + 1;
                $code = -1;
                $msg = $val['msg'];
                goto doEnd;
            }
            //交易成功
            if (true == $val['send_result'] && '0' == $val['query_result'] && '0' == $val['msg']) {
                $step = $key + 1;
                $code = 0;
                $msg = $val['send_result'];
                goto doEnd;
            }
            //交易成功，查询失败
            if (true == $val['send_result'] && 'success' != $val['query_result']) {
                $step = $key + 1;
                $code = -1;
                $msg = $val['msg'];
                goto doEnd;
            }
            //交易成功，查询成功
            if (true == $val['send_result'] && 'success' == $val['query_result'] && 'success' == $val['msg']) {
                if (2 == $key) {
                    $step = (2 == $key) ? 4 : ($key + 2);
                    $code = 0;
                    $msg = $val['send_result'];
                    goto doEnd;
                }
                continue;
            }
        }

        doEnd :
        $data = [
            'step' => $step,
            'amount' => (int)$amount
        ];
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public static function getrandnums()
    {
        $arr = array();
        while (count($arr) < 6) {
            $arr[] = rand(0, 9);
            $arr = array_unique($arr);
        }
        return implode("", $arr);
    }
}