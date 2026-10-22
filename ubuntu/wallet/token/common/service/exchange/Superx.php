<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Superx extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_superx';
    protected $quotation_prefix = 'quotation_superx_';
    protected $base_url = 'http://127.0.0.1:8091';

    public function symbolExists($tag = 'bty', $aim = "usdt")
    {
        $supported = $this->redis->smembers($this->supported_symbol);
        if (is_array($supported) && in_array($this->formatSymbol($tag, $aim), $supported)) {
            return true;
        }
        return false;
    }

    /**
     * 转化交易对为请求变量
     *
     * @param string $tag
     * @param string $aim
     * @return mixed
     */
    public function formatSymbol($tag = 'bty', $aim = 'usdt')
    {
        return strtolower(trim($tag) . trim($aim));
    }

    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setSupportedSymbol()
    {
        $this->redis->sadd($this->supported_symbol, 'btyusdt');
    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/api/ticker';
        $content = $curl->get($api, false);
        if (isset($content['msg']) && 'succeed' == $content['msg']) {
            $ticker = isset($content['data']['data']['USDT']) ? $content['data']['data']['USDT'] : null;
            if (false != $ticker) {
                foreach ($ticker as $k => $val) {
                    $key = $this->quotation_prefix . strtolower($k) . "usdt";
                    $this->redis->hmset($key, 'low', $val['low'], 'high', $val['high'], 'last', $val['last'], 'open', $val['open'], 'vol', $val['vol'], 'range', str_replace('%', '', $val['range']));
                    $this->redis->sadd($this->supported_symbol, strtoupper($k) . 'USDT');
                }
            }
        }
    }
}
