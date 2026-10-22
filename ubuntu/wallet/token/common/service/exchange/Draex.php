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

class Draex extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_draex';
    protected $quotation_prefix = 'quotation_draex_';
    protected $base_url = 'https://www.draex.co';

    public function formatSymbol($tag = 'DRA', $aim = 'USDT')
    {
        return strtolower(trim($tag) . trim($aim));
    }

    public function setSupportedSymbol()
    {
        $curl = new Curl();
        $api = $this->base_url . 'm/symbol';
        $res = $curl->get($api, false);//json
        if (is_array($res) && isset($res['state']) && 1 == $res['state']) {
            foreach ($res as $item) {
                $this->redis->sadd($this->supported_symbol, strtolower($item['symbol']));
            }
        }
    }

    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/m/allticker/' . time();
        $res = $curl->get($api, false);

        if (is_array($res) && isset($res['state']) && 1 == $res['state']) {
            $data = $res['data'];
            foreach ($data as $key => $item) {
                $low = isset($item['low']) ? $item['low'] : 0;
                $high = isset($item['high']) ? $item['high'] : 0;
                $last = isset($item['close']) ? $item['close'] : 0;
                $open = isset($item['open']) ? $item['open'] : 0;
                $vol = isset($item['volume']) ? $item['volume'] : 0;
                $cache_key = strtolower($this->quotation_prefix . $item['symbol']);
                $this->redis->hmset($cache_key, 'low', $low, 'high', $high, 'last', $last, 'open', $open, 'vol', $vol);
                $this->redis->sadd($this->supported_symbol, $item['symbol']);
            }
        }
    }

}
