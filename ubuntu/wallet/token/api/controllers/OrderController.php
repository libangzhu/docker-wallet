<?php

namespace api\controllers;

use Yii;
use yii\data\Pagination;
use api\base\BaseController;
use common\models\psources\CoinCTocTransfer;

class OrderController extends BaseController
{
    public function actionIndex()
    {
        $code = -1;
        $request = Yii::$app->request;
        if (!$request->isPost) {
            $msg = '请求错误!';
            goto doEnd;
        }
        $post = $request->post();

        $data['is_sell'] = (false == $post['isSell']) ? 0 : 1;
        $data['type'] = $post['type'];
        $data['address'] = $post['address'];
        $data['token_name'] = strtoupper($post['token_name']);
        $data['market_name'] = strtoupper($post['market_name']);
        $data['price'] = $post['price'];
        $data['amount'] = $post['amount'];

        $data['transfer_number'] = date('YmdHis') . self::getrandnums();

        $redis = Yii::$app->redis_app;
        $key = 'C2C_' . $data['is_sell'] . '_' . str_replace('/', '', strtoupper($post['token_name'])) . '_' . str_replace('/', '', strtoupper($post['market_name']));
        if ($redis->hexists($key, $data['address'])) {
            $msg = '尚存在一笔未完成的交易!';
            goto doEnd;
        }
        if (!$redis->exists($key)) {
            $redis->hmset($key, $data['address'], time());
        }
        foreach ($post['txs'] as $key => $val) {
            $model = new CoinCTocTransfer();
            $data['txhex'] = $val['tx'];
            $data['transfer_url'] = $val['url'];
            $data['step'] = $val['step'];
            $data['consensus'] = (false == $val['consensus']) ? 0 : 1;
            $data['consensus_platform'] = isset($val['consensus_platform']) ? $val['consensus_platform'] : "";
            $model->load($data, '');
            $model->save();
        }

        $code = 0;
        $msg = 'success';
        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    public function actionOrderList()
    {
        $address = Yii::$app->request->get('address', '');
        $is_sell = Yii::$app->request->get('isSell', -1);
        $type = Yii::$app->request->get('type', '');
        $page = Yii::$app->request->get('page', 1);

        if (empty($address)) {
            $msg = '请求参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $query = CoinCTocTransfer::find()
            ->where('address= :address', [':address' => $address])
            ->orderBy('create_time desc')
            ->groupBy('address, is_sell');

        if ($is_sell > -1) {
            $query->andWhere('is_sell= :is_sell', [':is_sell' => $is_sell]);
        }
        if (false != $type) {
            $query->andWhere('type= :type', [':type' => $type]);
        }
        $data = $query->offset(($page - 1) * 20)->limit(20)->asArray()->all();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 20]);
        $data = [
            'list' => $data,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize' => 20,
                'currentPage' => $page,
            ]
        ];
        $code = 1;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionOrderStatus()
    {
        $address = Yii::$app->request->get('address', '');
        $token_name = Yii::$app->request->get('token_name', '');
        $market_name = Yii::$app->request->get('market_name', '');
        $is_sell = Yii::$app->request->get('isSell', -1);
        $type = Yii::$app->request->get('type', '');

        if (false == $address || false == $token_name || false == $market_name || false == $type || -1 == $is_sell) {
            $msg = '请求参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $latest_model = CoinCTocTransfer::find()->select('transfer_number')
            ->where(['is_sell' => (int)$is_sell])
            ->andWhere(['type' => (int)$type])
            ->andWhere(['address' => $address])
            ->andWhere(['token_name' => $token_name])
            ->andWhere(['market_name' => $market_name])
            ->orderBy('transfer_number desc')
            ->asArray()
            ->one();

        if (false == $latest_model) {
            $msg = '订单不存在';
            $code = -1;
            $step = 0;
            goto doEnd;
        }

        $orders_info = CoinCTocTransfer::find()->where(['transfer_number' => $latest_model['transfer_number']])->orderBy('id desc')->asArray()->all();
        sort($orders_info);
        foreach ($orders_info as $key => $val) {
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
                if ((count($orders_info) - 1) == $key) {
                    $step = ((count($orders_info) - 1) == $key) ? 99 : ($key + 2);
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