<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/8/15
 * Time: 10:10
 */

namespace common\service\exchange\factory;

use Yii;
use linslin\yii2\curl\Curl;

class OkexBuilder
{
    protected $base_url = 'https://www.okex.com';
    protected $base_coin = ['ETH', 'BTC', 'USDT', 'BTC'];
    protected $redis;
    protected $supported_symbol = 'supported_symbol_bitfinex_v2';

    public function __construct()
    {
        $this->redis = Yii::$app->redis;
    }

    public function getSymbol()
    {
        $curl = new Curl();
        $api = $this->base_url . '/v2/spot/markets/products';
        $res = $curl->get($api, false);
        if (isset($res['code']) && 0 == $res['code']) {
            foreach ($res['data'] as $val) {
                if ($this->redis->sismember($this->supported_symbol, $val['symbol'])) continue;
                $this->redis->sadd($this->supported_symbol, $val['symbol']);
            }
        }
    }

    public function getTicker()
    {
        $symbols = $this->redis->smembers($this->supported_symbol);
        if (empty($symbols)) $this->getSymbol();
        $symbols = $this->redis->smembers($this->supported_symbol);
        $curl = new Curl();
        $code = -1;
        $ticker = [];
        foreach ($symbols as $symbol) {
            $api = $this->base_url . '/api/v1/ticker.do?symbol=' . $symbol;
            $res = $curl->get($api, false);
            if (isset($res['ticker'])) {
                $code = 0;
                $symbol = strtoupper(str_replace('_', '/', $symbol));
                $ticker[$symbol]['symbol'] = $symbol;
                $ticker[$symbol]['close'] = (float)sprintf("%0.4f", $res['ticker']['last']);
                //$ticker[$symbol]['change'] = (float)sprintf(($val['close'] - $val['open']) / $val['open'] * 100);
                $ticker[$symbol]['high'] = (float)sprintf("%0.4f", $res['ticker']['high']);
                $ticker[$symbol]['low'] = (float)sprintf("%0.4f", $res['ticker']['low']);
                $ticker[$symbol]['vol'] = (float)sprintf("%0.4f", $res['ticker']['last']);
            }
        }

        return ['code' => $code, 'ticker' => $ticker];
    }
}