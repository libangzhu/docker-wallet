<?php

namespace console\controllers;

use common\models\psources\CoinCrossChain;
use common\models\psources\CoinCTocTransfer;
use Yii;
use yii\console\Controller;
use common\service\chain33\Chain33Service;

class OrderController extends Controller
{
    /**
     * 自动交易
     *
     * @return array
     */
    public function actionAutoOrder($is_sell, $type)
    {
        $redis = Yii::$app->redis_app;
        $model = CoinCTocTransfer::find()->where(['send_result' => "0", 'is_sell' => (int)$is_sell, 'type' => (int)$type, 'query_result' => '0'])->asArray()->all();
        if (empty($model)) {
            echo date('Y-m-d H:i:s') . '暂无交易计划' . PHP_EOL;
            return 0;
        }
        $current_time = time();
        foreach ($model as $val) {
            if ($val['step'] > 1) {
                $isExist = CoinCTocTransfer::find()
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
            $key = $val['address'] . '_' . $val['is_sell'] . '_' . $val['token_name'] . '_' . $val['market_name'];

            $cache_transfering_time = $redis->hget(CoinCTocTransfer::C2C_ORDERING, $key);
            if (true == $cache_transfering_time) {
                continue;
            }
            $redis->hmset(CoinCTocTransfer::C2C_ORDERING, $key, $current_time);

            go(function () use ($val, $redis, $key) {
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
                    $currentModel = CoinCTocTransfer::findOne($val['id']);
                    $currentModel->send_result = $send_result;
                    $currentModel->msg = 0;
                    $currentModel->save();
                    $redis->hdel(CoinCTocTransfer::C2C_ORDERING, $key);
                } else {
                    $currentModel = CoinCTocTransfer::findOne($val['id']);
                    $currentModel->msg = $result['msg'];
                    $currentModel->save();
                }
            });
        }
        echo date('Y-m-d H:i:s') . '交易成功' . PHP_EOL;
        return 0;
    }

    public function actionOrderStatus()
    {
        $redis = Yii::$app->redis_app;
        $model = CoinCTocTransfer::find()->where(['<>', 'send_result', '0'])->andWhere(['msg' => '0'])->asArray()->all();

        if (empty($model)) {
            echo date('Y-m-d H:i:s') . '暂无跨链交易计划' . PHP_EOL;
            return 0;
        }

        //正式环境查询共识高度
        //3步交易情况下需要做高度判断，2步交易无须判断
        $node = [
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

        $current_time = time();
        foreach ($model as $val) {
            $key = $val['address'] . '_' . $val['is_sell'] . '_' . str_replace('/', '', strtoupper($val['token_name'])) . '_' . str_replace('/', '', strtoupper($val['market_name']));
            $node_params = $val['transfer_url'];
            $node_params = explode(':', $node_params);
            $node_params = [
                'scheme' => $node_params[0],
                'host' => str_replace('//', '', $node_params[1]),
                'port' => isset($node_params[2]) ? $node_params[2] : ''
            ];
            $send_result = $val['send_result'];
            $switch = true;
            $result = $this->queryTransaction($node_params, $send_result);
            if (isset($result['result']['actionName']) && 'unknown' == $result['result']['actionName']) {
                $redis->hdel(CoinCTocTransfer::C2C_ORDER_FAILED, $key);
                $query_result = 'success';
                $msg = 'success';
                $height = isset($result['result']['height']) ? $result['result']['height'] : 0;
                goto doEnd;
            } else if (isset($result['result']['receipt']['ty']) && 2 == $result['result']['receipt']['ty']) {
                $redis->hdel(CoinCTocTransfer::C2C_ORDER_FAILED, $key);
                $query_result = 'success';
                $msg = 'success';
                $height = isset($result['result']['height']) ? $result['result']['height'] : 0;
                goto doEnd;
            } else {
                $cache_error_time = $redis->hget(CoinCTocTransfer::C2C_ORDER_FAILED, $key);
                if (false == $cache_error_time) {
                    $redis->hmset(CoinCTocTransfer::C2C_ORDER_FAILED, $key, $current_time);
                    continue;
                }

                if (($current_time - $cache_error_time) < 60) {
                    continue;
                }

                $redis->hdel(CoinCTocTransfer::C2C_ORDER_FAILED, $key);
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
            if (CoinCTocTransfer::CONSENSUE_YES == $val['consensus'] && true == $switch && isset($height)) {
                $last_hash = $result['result']['tx']['hash'];
                $service = new Chain33Service($node);
                $execer = 'paracross';
                $func_name = 'GetTitle';
                $playload = [
                    'data' => 'user.p.' . $val['consensus_platform'] . '.'
                ];
                $result = $service->chainQuery($execer, $func_name, $playload);
                if (!isset($result['result'])) {
                    continue;
                }
                $consensHeight = $result['result']['height'];
                if ($consensHeight < $height) {
                    continue;
                }

                $func_name = 'GetAssetTxResult';
                $playload = [
                    'data' => $last_hash
                ];
                $result = $service->chainQuery($execer, $func_name, $playload);
                if (!isset($result['result'])) {
                    continue;
                }
                $tx_result = $result['result']['success'];
                if (false == $tx_result) {
                    continue;
                }
            }
            $currentModel = CoinCTocTransfer::findOne($val['id']);
            $currentModel->query_result = $query_result;
            $currentModel->msg = $msg;
            $currentModel->save();

            $model_c2c = CoinCTocTransfer::find()->where(['transfer_number' => $val['transfer_number']])->asArray()->all();
            $clear = true;
            foreach ($model_c2c as $record) {
                if ('success' != $record['query_result']) {
                    $clear = false;
                }
            }

            if ($clear) {
                $key = 'C2C_' . $val['is_sell'] . '_' . str_replace('/', '', strtoupper($val['token_name'])) . '_' . str_replace('/', '', strtoupper($val['market_name']));
                $redis->hdel($key, $val['address']);
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
