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

class Bitnasdaq extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_bitnasdaq';
    protected $quotation_prefix = 'quotation_bitnasdaq_';
    protected $base_url = 'https://openapi.bitnasdaq.pro';

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
        $api = $this->base_url . '/open/api/get_allticker';
        $res = $curl->get($api, false);

        if (is_array($res) && isset($res['data'])) {
            $data = $res['data']['ticker'];
            foreach ($data as $key => $item) {
                #if (in_array(strtoupper($item['symbol']), ['BNCUSAT', 'BTCUSAT', 'ETCUSAT', 'ETHUSAT', 'GHPUSAT', 'LTCUSAT', 'BNCUSDT'])) {
                    $low = isset($item['low']) ? $item['low'] : 0;
                    $high = isset($item['high']) ? $item['high'] : 0;
                    $last = isset($item['last']) ? $item['last'] : 0;
                    $open = isset($item['last']) ? $item['last'] : 0;
                    $vol = isset($item['vol']) ? $item['vol'] : 0;
                    $change = isset($item['rose']) ? $item['rose'] : 0;
                    $cache_key = strtolower($this->quotation_prefix . $item['symbol']);
                    $this->redis->hmset($cache_key, 'low', $low, 'high', $high, 'last', $last, 'open', $open, 'vol', $vol, 'change', $change);
                    $this->redis->sadd($this->supported_symbol, strtoupper($item['symbol']));
                #}
            }
        }
    }

}
