<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Gdpro extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_gdpro';
    protected $quotation_prefix = 'quotation_gdpro_';
    protected $base_url         = 'https://api.gdpro.pro/api/v1/ticker?symbol=25';

    public function symbolExists($tag = 'CTG', $aim = "CNY")
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
    public function formatSymbol($tag = 'CTG', $aim = 'CNY')
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
        $this->redis->sadd($this->supported_symbol, 'CTGCNY');
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
        if (is_array($content) && isset($content['data']) && 200 == $content['code']) {
            $data = $content['data'];
            $key  = $this->quotation_prefix . 'CTG_CNY';
            $this->redis->hmset($key, 'low', $data['low'], 'high', $data['high'], 'last', $data['last']);
            $this->redis->sadd($this->supported_symbol, 'CTG_CNY');
        }
    }

}