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

class Isummit extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_isummit';
    protected $quotation_prefix = 'quotation_isummit_';
    protected $base_url = 'http://www.isummit.top';

    public function formatSymbol($tag = 'BTC', $aim = 'USDT')
    {
        return strtolower(trim($tag) . trim($aim));
    }

    public function setSupportedSymbol()
    {

    }

    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/api/coins/market';
        $res = $curl->get($api, false);
        if (isset($res['code']) && 200 == $res['code'] && isset($res['data'])) {
            foreach ($res['data'] as $item) {
                if (in_array(strtoupper($item['coin']), ['SUM', 'USDW', 'FUT'])) {
                    $low = isset($item['price']) ? $item['price'] : 0;
                    $high = isset($item['price']) ? $item['price'] : 0;
                    $last = isset($item['price']) ? $item['price'] : 0;
                    $open = isset($item['price']) ? $item['price'] : 0;
                    $cache_key = $this->quotation_prefix . strtolower($item['coin'] . 'USDT');
                    $this->redis->hmset($cache_key, 'low', $low, 'high', $high, 'last', $last, 'open', $open);
                }
            }
        }
    }

}
