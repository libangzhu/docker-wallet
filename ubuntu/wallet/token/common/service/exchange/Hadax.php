<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-27
 * Time: 下午4:22
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;
use Yii;

class Hadax extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_hadax';
    protected $quotation_prefix = 'quotation_hadax_';
    protected $base_url         = 'https://api.hadax.com';

    public function formatSymbol($tag = 'BTC', $aim = 'USDT')
    {
        return strtolower(trim($tag) . trim($aim));
    }

    public function setSupportedSymbol()
    {
        $curl = new Curl();
        $api = $this->base_url . '/v1/hadax/common/symbols';
        $res = $curl->get($api, false);//json
        if ($res && $res['status'] == 'ok') {
            $data = $res['data'];
            foreach ($data as $item) {
                $this->redis->sadd($this->supported_symbol,
                    $this->formatSymbol($item['base-currency'], $item['quote-currency']));
            }
        }
    }

    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/market/tickers';
        $res = $curl->get($api, false);
        if ($res && $res['status'] == 'ok') {
            $datas = $res['data'];
            foreach ($datas as $item) {
                $key = $this->quotation_prefix . $item['symbol'];
                $this->redis->hmset($key, 'low', $item['low'], 'high', $item['high'], 'last', $item['close']);
                $this->redis->sadd($this->supported_symbol, $item['symbol']);
            }
        }
    }
}