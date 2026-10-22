<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-26
 * Time: 下午7:21
 */

namespace common\service\exchange;

use Yii;
use linslin\yii2\curl\Curl;

class Binance extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_binance';
    protected $quotation_prefix = 'quotation_binance_';
    protected $base_url = 'https://api.binance.com';

    public function formatSymbol($tag = 'BTC', $aim = 'USDT')
    {
        return strtolower(trim($tag) . trim($aim));
    }

    public function setSupportedSymbol()
    {
        $curl = new Curl();
        $api = $this->base_url . '/api/v1/ticker/24hr';
        $res = $curl->get($api, false);//json
        if (is_array($res)) {
            foreach ($res as $item) {
                $this->redis->sadd($this->supported_symbol, strtolower($item['symbol']));
            }
        }
    }

    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/api/v1/ticker/24hr';
        $res = $curl->get($api, false);
        if (is_array($res)) {
            foreach ($res as $item) {
                $key = $this->quotation_prefix . strtolower($item['symbol']);
                $this->redis->hmset($key, 'low', $item['lowPrice'], 'high', $item['highPrice'], 'last', $item['lastPrice'], 'open', $item['openPrice'], 'vol', $item['volume']);
                $this->redis->sadd($this->supported_symbol, $item['symbol']);
            }
        }
    }

}