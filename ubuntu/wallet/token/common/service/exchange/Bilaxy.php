<?php

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Bilaxy extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_bilaxy';
    protected $quotation_prefix = 'quotation_bilaxy_';
    protected $base_url         = 'https://newapi.bilaxy.com';


    public function symbolExists($tag = 'BVA', $aim = "USDT")
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
    public function formatSymbol($tag = 'BVA', $aim = 'USDT')
    {
        return strtoupper($tag .'_'. $aim);
    }

    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setSupportedSymbol()
    {
        $curl = new Curl();
        $api = $this->base_url . '/v1/ticker/24hr';
        $content = $curl->get($api, false);//json
        if (is_array($content)) {
            foreach ($content as $key => $item) {
                $this->redis->sadd($this->supported_symbol, strtoupper($key));
            }
        }

    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/v1/ticker/24hr';
        $content = $curl->get($api, false);
        if (is_array($content)) {
            foreach ($content as $key => $item) {
                $this->redis->hmset($this->quotation_prefix . strtoupper($key), 'low', $item['low'], 'high', $item['height'], 'last', $item['close']);
                if (!$this->redis->sismember($this->supported_symbol, strtoupper($key))){
                    $this->redis->sadd($this->supported_symbol, strtoupper($key));
                }
            }
        }
    }

}