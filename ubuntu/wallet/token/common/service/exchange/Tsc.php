<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Tsc extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_tsc';
    protected $quotation_prefix = 'quotation_tsc_';
    protected $base_url         = 'https://api.tsc100.vip/openapi/quote/v1/ticker/24hr';

    public function symbolExists($tag = 'TSC', $aim = "USDT")
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
    public function formatSymbol($tag = 'TSC', $aim = 'USDT')
    {
        return strtoupper($tag . $aim);
    }

    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setSupportedSymbol()
    {

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
        if (is_array($content)) {
            foreach ($content as $item) {
                $key = $this->quotation_prefix . strtoupper($item['symbol']);
                $this->redis->hmset($key, 'low', $item['lowPrice'], 'high', $item['highPrice'], 'last', $item['lastPrice']);
                if (!$this->redis->sismember($this->supported_symbol, strtoupper($item['symbol']))){
                    $this->redis->sadd($this->supported_symbol, strtoupper($item['symbol']));
                }
            }
        }
    }

}