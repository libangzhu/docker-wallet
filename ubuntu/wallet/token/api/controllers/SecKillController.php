<?php

namespace api\controllers;

use common\models\psources\CoinExtract;
use Yii;
use api\base\BaseController;

class SecKillController extends BaseController
{
    public function actionIndex()
    {
        echo hash(141414, 'The quick brown fox jumped over the lazy dog.');
        exit;

        $num = $this->randomFloat(1, 3);
        $expire = 5;
        $nowTime = time();
        //新的过期时间
        $expTime = $nowTime + $expire;
        $redis = \Yii::$app->redis_es;
        list($good_id, $limit_min_amount, $limit_max_amount) = $redis->hmget('productInfo:16', 'good_id', 'limit_min_amount', 'limit_max_amount');
        if ($num < $limit_min_amount || $num > $limit_max_amount) {
            $this->msg = 'err amount';
            $this->code = -1;
            goto doEnd;
        }

        $lock_key = 'lock:' . $good_id;
        //var_dump($redis->del($lock_key));exit;

        $stock = $redis->get('stock:' . $good_id);
        if (0 == $stock) {
            $this->msg = 'weclome again';
            goto doEnd;
        }

        //$is_locked = $redis->set($lock_key, $expTime, 'ex', 5, 'nx');

        $is_locked = $redis->setnx($lock_key, $expTime);

        if (!$is_locked) {
            $this->msg = 'ahaaa, locked';
            $this->code = -1;
            goto doEnd;
        }

        $redis->setex($lock_key, 5, $expTime);
        $redis->incrbyfloat('stock:' . $good_id, -$num);
        $order_info = [
            'good_id' => $good_id,
            'amount' => $num,
            'time' => date('Y-m-d H:i:s')
        ];
        $redis->lpush('order_info', json_encode($order_info));
        //开始其他业务逻辑
        //下单。。。。。。
        //用户信息写入redis，禁止重复下单
        if ($redis->get('stock:' . $good_id) <= $limit_min_amount) {
            //下架操作。。。。。。
            $redis->set('stock:' . $good_id, 0);
        }
        $redis->del($lock_key);

        $expireTime = $redis->get('lock:' . $good_id);
        if ($expireTime < $nowTime) {

        }

        doEnd :

        return ['code' => $this->code, 'msg' => $this->msg];
    }

    function actionTest()
    {
        $model = CoinExtract::find()->where(['id' => 8])->one();
        if (!$model->total > 0) {
            $this->msg = 'weclome again';
            goto doEnd;
        }
        $model->total = $model->total - 1;
        $model->save();

        doEnd :

        return ['code' => $this->code, 'msg' => $this->msg];
    }

    function randomFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}