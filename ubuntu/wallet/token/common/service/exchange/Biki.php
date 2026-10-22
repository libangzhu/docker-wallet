<?php

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Biki extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_biki';
    protected $quotation_prefix = 'quotation_biki_';
    protected $base_url         = 'https://openapi.biki.com/open/api/get_ticker?symbol=kpc8usdt';


    public function symbolExists($tag = 'KPC8', $aim = "USDT")
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
    public function formatSymbol($tag = 'KPC8', $aim = 'USDT')
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
        $this->redis->sadd($this->supported_symbol, 'KPC8_USDT');
    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $curl = new Curl();
        $content = $curl->get($this->base_url, false);
        if (is_array($content) && isset($content['data'])) {
            $data = $content['data'];
            $key  = $this->quotation_prefix . 'KPC8_USDT';
            $this->redis->hmset($key, 'low', $data['low'], 'high', $data['high'], 'last', $data['last']);
            $this->redis->sadd($this->supported_symbol, 'KPC8_USDT');
        }
    }

}