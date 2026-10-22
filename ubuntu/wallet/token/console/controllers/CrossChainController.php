<?php

namespace console\controllers;

use common\models\psources\CoinCrossChain;
use Yii;
use yii\console\Controller;
use common\service\chain33\Chain33Service;

class CrossChainController extends Controller
{
    /**
     * 自动交易
     *
     * @return array
     */
    public function actionAutoTransfer($step, $is_with_draw)
    {
        $redis = Yii::$app->redis;
        $model = CoinCrossChain::find()->where(['send_result' => "0", 'step' => (int)$step, 'is_with_draw' => (int)$is_with_draw, 'query_result' => '0'])->asArray()->all();
        if (empty($model)) {
            echo date('Y-m-d H:i:s') . ' STEP: ' . $step . '暂无跨链交易计划' . PHP_EOL;
            return 0;
        }
        $current_time = time();
        foreach ($model as $val) {
            if ($step > 1) {
                $isExist = CoinCrossChain::find()
                    ->where(['transfer_number' => $val['transfer_number']])
                    ->andWhere(['<', 'id', (int)$val['id']])
                    ->orderBy('id desc')
                    ->asArray()
                    ->one();
                //上一步发送成功，未查询
                if ('success' != $isExist['query_result'] && !empty($isExist)) {
                    continue;
                }
            }
            $cache_transfering_time = $redis->hget(CoinCrossChain::CROSS_CHAIN_TRANSFERING, $val['address'] . ':' . $val['is_with_draw'] . ':' . $val['id']);
            if (true == $cache_transfering_time) {
                continue;
            }
            $redis->hmset(CoinCrossChain::CROSS_CHAIN_TRANSFERING, $val['address'] . ':' . $val['is_with_draw'] . ':' . $val['id'], $current_time);

            go(function () use ($val, $redis) {
                \Co::sleep(0.5);
                $transfer_url = $val['transfer_url'];
                $transfer_url = explode(':', $transfer_url);

                $node_params = [
                    'scheme' => $transfer_url[0],
                    'host' => str_replace('//', '', $transfer_url[1]),
                    'port' => isset($transfer_url[2]) ? $transfer_url[2] : '',
                ];

                $service = new Chain33Service($node_params);
                $sign_str = $val['txhex'];
                $result = $service->sendTransaction($sign_str);

                if (0 == $result['code']) {
                    $send_result = $result['result'];
                    $currentModel = CoinCrossChain::findOne($val['id']);
                    $currentModel->send_result = $send_result;
                    $currentModel->msg = 0;
                    $currentModel->save();
                    $redis->hdel(CoinCrossChain::CROSS_CHAIN_TRANSFERING, $val['address'] . ':' . $val['is_with_draw'] . ':' . $val['id']);
                }

                if (0 == $result['code']) {
                    $send_result = $result['result'];
                    $currentModel = CoinCrossChain::findOne($val['id']);
                    $currentModel->send_result = $send_result;
                    $currentModel->msg = 0;
                    $currentModel->save();
                    $redis->hdel(CoinCrossChain::CROSS_CHAIN_TRANSFERING, $val['address'] . ':' . $val['is_with_draw'] . ':' . $val['id']);
                } else {
                    $currentModel = CoinCrossChain::findOne($val['id']);
                    $currentModel->msg = $result['msg'];
                    $currentModel->save();
                }
            });
        }
        echo date('Y-m-d H:i:s') . ' STEP: ' . $step . '跨链交易成功' . PHP_EOL;
        return 0;
    }

    public function actionQueryTransaction()
    {
        $redis = Yii::$app->redis;
        $model = CoinCrossChain::find()->where(['<>', 'send_result', '0'])->andWhere(['msg' => '0'])->asArray()->all();

        if (empty($model)) {
            echo date('Y-m-d H:i:s') . '暂无跨链交易计划' . PHP_EOL;
            return 0;
        }

        //正式环境查询共识高度
        //3步交易情况下需要做高度判断，2步交易无须判断
        $node_params = [
            'scheme' => 'https',
            'host' => 'jiedian1.bityuan.com',
            'port' => 8801
        ];

        //测试环境查询共识高度
        //2步交易情况下需要做高度判断
//        $node_params = [
//            'scheme' => 'http',
//            'host' => '172.16.100.77',
//            'port' => 8801
//        ];
        $service = new Chain33Service($node_params);
        $payload = [
            "data" => 'user.p.game',
        ];
        $result = $service->chainQuery('paracross', 'GetHeight', $payload);
        $consensHeight = $result['result']['consensHeight'];

        $current_time = time();
        foreach ($model as $val) {
            $node_params = $val['transfer_url'];
            $node_params = explode(':', $node_params);
            $node_params = [
                'scheme' => $node_params[0],
                'host' => str_replace('//', '', $node_params[1]),
                'port' => isset($node_params[2]) ? $node_params[2] : ''
            ];
            $send_result = $val['send_result'];
            #$result = $service->QueryTransaction($send_result);
            $switch = true;
            $result = $this->queryTransaction($node_params, $send_result);
            if (isset($result['result']['actionName']) && 'unknown' == $result['result']['actionName']) {
                $redis->hdel(CoinCrossChain::CROSS_CHAIN_CACHE_FAIL, $val['address'] . ':' . $val['is_with_draw']);
                $query_result = 'success';
                $msg = 'success';
                $height = isset($result['result']['height']) ? $result['result']['height'] : 0;
                goto doEnd;
            } else if (isset($result['result']['receipt']['ty']) && 2 == $result['result']['receipt']['ty']) {
                $redis->hdel(CoinCrossChain::CROSS_CHAIN_CACHE_FAIL, $val['address'] . ':' . $val['is_with_draw']);
                $query_result = 'success';
                $msg = 'success';
                $height = isset($result['result']['height']) ? $result['result']['height'] : 0;
                goto doEnd;
            } else {
                $cache_error_time = $redis->hget(CoinCrossChain::CROSS_CHAIN_CACHE_FAIL, $val['address'] . ':' . $val['is_with_draw']);
                if (false == $cache_error_time) {
                    $redis->hmset(CoinCrossChain::CROSS_CHAIN_CACHE_FAIL, $val['address'] . ':' . $val['is_with_draw'], $current_time);
                    continue;
                }

                if (($current_time - $cache_error_time) < 60) {
                    continue;
                }

                $redis->hdel(CoinCrossChain::CROSS_CHAIN_CACHE_FAIL, $val['address'] . ':' . $val['is_with_draw']);
                if (-1 == $result['code']) {
                    $msg = $result['msg'];
                    $query_result = $result['code'];
                    $switch = false;
                    goto doEnd;
                } else {
                    $query_result = 'fail';
                    foreach ($result['result']['receipt']['logs'] as $log) {
                        if (is_array($log['log'])) continue;
                        $msg = isset($log['log']) ? $log['log'] : '查询错误';
                    }
                    $switch = false;
                    goto doEnd;
                }
            }

            doEnd :

            //3步交易情况下需要做高度判断，2步交易注释IF判断
            if (CoinCrossChain::RECHARGE == (int)$val['is_with_draw'] && 2 == (int)$val['step'] && true == $switch && isset($height) && ($consensHeight < $height)) {
                continue;
            }
            $currentModel = CoinCrossChain::findOne($val['id']);
            $currentModel->query_result = $query_result;
            $currentModel->msg = $msg;
            $currentModel->save();

            $count = CoinCrossChain::find()->where(['transfer_number' => $val['transfer_number']])->andWhere(['query_result' => 'success'])->count();
            if (3 == $count) {
                $key = $val['address'] . '_' . $val['is_with_draw'];
                if ($redis->exists($key)) {
                    $redis->del($key);
                }
            }
        }

        echo date('Y-m-d H:i:s') . '查询完毕' . PHP_EOL;
        return 0;
    }

    protected function queryTransaction($node_params, $send_result)
    {
        static $result = [];
        $service = new Chain33Service($node_params);
        $result = $service->QueryTransaction($send_result);
        if (isset($result['result']['receipt']) && is_array($result['result']['receipt']['logs'])) {
            foreach ($result['result']['receipt']['logs'] as $log) {
                if (isset($log['tyName']) && 'logerr' == strtolower($log['tyName'])) {
                    return $result;
                }
            }
        }

        if (isset($result['result']['tx']['next'])) {
            $this->queryTransaction($node_params, $result['result']['tx']['next']);
        }
        return $result;
    }
}
