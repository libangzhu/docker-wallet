<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Dag extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_dag';
    protected $quotation_prefix = 'quotation_dag_';
    protected $base_url = 'https://www.7xex.com/m/ticker/DAGUSDT';


    public function symbolExists($tag = 'DAG', $aim = "USDT")
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
    public function formatSymbol($tag = 'DAG', $aim = 'USDT')
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
        $this->redis->sadd($this->supported_symbol, 'DAGUSDT');
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
            $key = $this->quotation_prefix . 'DAGUSDT';
            $this->redis->hmset($key, 'low', $content['low'], 'high', $content['high'], 'last', $content['close'], 'open', $content['open'], 'vol', $content['volume']);
            $this->redis->sadd($this->supported_symbol, 'DAGUSDT');
        }
    }

}