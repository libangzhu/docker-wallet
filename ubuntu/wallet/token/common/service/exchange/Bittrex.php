<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-28
 * Time: 上午9:25
 */

namespace common\service\exchange;

use Yii;
use linslin\yii2\curl\Curl;

class Bittrex extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_bittrex';
    protected $quotation_prefix = 'quotation_bittrex_';

    public function formatSymbol($tag = 'BTC', $aim = 'USD')
    {
        return strtoupper(trim($tag) . '-' . trim($aim));
    }

    public function setSupportedSymbol()
    {
        $api = 'https://bittrex.com/api/v1.1/public/getmarkets';
        $curl = new Curl();
        $rev = $curl->get($api, false);
        if ($rev && $rev['success']) {
            $lists = $rev['result'];
            foreach ($lists as $item) {
                $this->redis->sadd($this->supported_symbol, $item['MarketName']);
            }
        }
    }

    public function setQuotation()
    {
        $api = 'https://bittrex.com/api/v1.1/public/getmarketsummaries';
        $curl = new Curl();
        $rev = $curl->get($api, false);
        if ($rev && $rev['success']) {
            $lists = $rev['result'];
            foreach ($lists as $item) {
                $this->redis->hmset($this->quotation_prefix . $item['MarketName'], 'low', $item['Low'], 'high',
                    $item['High'], 'last', $item['Last']);
            }
        }
    }

}