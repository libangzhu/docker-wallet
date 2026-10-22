<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/8/20
 * Time: 10:10
 */

namespace common\service\exchange\factory;

use linslin\yii2\curl\Curl;

abstract class FactoryService
{
    protected $code = -1;
    protected $basic_coin = ['ETH', 'BTC', 'USDT', 'BTY', 'CNYT'];

    protected $basic_price = [];
    protected $redis;
    protected $redis_ticker;

    protected $start;
    protected $end;

    public function __construct()
    {
        $curl = new Curl();
        $data = [
            "names" => [
                "eth,ethereum",
                "btc,btc",
                "usdt,ethereum",
                "bty,bty",
                "cnyt,bitnasdaqchain"
            ]
        ];
        $params = json_encode($data);
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setRawPostData($params);
        $res = $curl->post(\Yii::$app->params['biqianbao'].'/interface/coin/coin-index', true);
        $res = json_decode($res, true);

        foreach ($res['data'] as $val) {
            $this->basic_price[$val['name']]['rmb'] =  $val['rmb'] ?? 0;
            $this->basic_price[$val['name']]['usd'] =  $val['usd'] ?? 0;
        }

        $this->redis = \Yii::$app->redis;
        $this->redis_ticker = \Yii::$app->redis_ticker;
    }
}