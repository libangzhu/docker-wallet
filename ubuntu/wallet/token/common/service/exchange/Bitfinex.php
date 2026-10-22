<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-27
 * Time: 下午5:29
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;
use Yii;

class Bitfinex extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_bitfinex';
    protected $quotation_prefix = 'quotation_bitfinex_';

    public function symbolExists($tag = 'BTC', $aim = "USD")
    {
        $supported = $this->redis->smembers($this->supported_symbol);
        if (is_array($supported) && in_array($this->formatSymbol($tag, $aim), $supported)) {
            return true;
        }
        return false;
    }

    public function formatSymbol($tag = 'BTC', $aim = 'USD')
    {
        return 't' . strtoupper(trim($tag) . trim($aim));
    }

    public function setSupportedSymbol()
    {
        $curl = new Curl();
        $result = $curl->get('https://api.bitfinex.com/v1/symbols', false);
        if ($result) {
            foreach ($result as $item) {
                $this->redis->sadd($this->supported_symbol, 't' . strtoupper($item));
            }
        }
    }

    public function setQuotation()
    {
        $symbols = $this->redis->smembers($this->supported_symbol);
        $query   = '?symbols=' . implode(',', $symbols);
        $curl = new Curl();
        $res = $curl->get('https://api.bitfinex.com/v2/tickers' . $query, false);

        if(false == $res){
            return;
        }
        foreach ($res as $item) {
            $this->redis->hmset($this->quotation_prefix . $item[0],
                'low', $item[10],
                'high', $item['9'],
                'last', $item[7]);
        }
    }

}