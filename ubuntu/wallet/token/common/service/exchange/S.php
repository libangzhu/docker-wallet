<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class S extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_s';
    protected $quotation_prefix = 'quotation_s_';
    protected $base_url         = 'https://exapi.s.top/api/v1/market/detail?symbol=becc_st';

    public function symbolExists($tag = 'BECC', $aim = "ST")
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
    public function formatSymbol($tag = 'BECC', $aim = 'ST')
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
        $this->redis->sadd($this->supported_symbol, 'BTYUSDT');
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
        if (is_array($content) && isset($content['data']) && 200 === $content['code']) {
            $data = $content['data'];
            $key  = $this->quotation_prefix . 'BECC_ST';
            $this->redis->hmset($key, 'low', $data['low'], 'high', $data['high'], 'last', $data['close']);
            $this->redis->sadd($this->supported_symbol, 'BECC_ST');
        }
    }

}