<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-28
 * Time: 上午10:08
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

/**
 * Class Zb
 * api document:https://www.zb.com/i/developer/restApi#market
 * @package common\service\exchange
 */
class Zb extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_zb';
    protected $quotation_prefix = 'quotation_zb_';

    public function formatSymbol($tag = 'BTC', $aim = 'USDT')
    {
        return strtolower(trim($tag) . '_' . trim($aim));
    }

    public function setSupportedSymbol()
    {
        $api = "http://api.zb.com/data/v1/markets";
        $curl = new Curl();
        $rev = $curl->get($api, false);
        if ($rev) {
            $keys = array_keys($rev);
            foreach ($keys as $key) {
                $this->redis->sadd($this->supported_symbol, str_replace('_', '', $key));
            }
        }
    }

    public function setQuotation()
    {
        $api = 'http://api.zb.com/data/v1/allTicker';
        $curl = new Curl();
        $rev = $curl->get($api, false);
        if ($rev) {
            foreach ($rev as $key => $item) {
                $this->redis->hmset($this->quotation_prefix . $key, 'low', $item['low'], 'high', $item['high'], 'last',
                    $item['last']);
            }
        }
    }

}