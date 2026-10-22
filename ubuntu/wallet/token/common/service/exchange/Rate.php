<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Rate extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_rate';
    protected $quotation_prefix = 'quotation_rate_';
    protected $base_url         = 'https://ticker-api.cointelegraph.com/history/USDT/CNY/?coin=USDT&currency=CNY&limit=1';

    public function symbolExists($tag = 'CNY', $aim = "USD")
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
    public function formatSymbol($tag = 'CNY', $aim = 'USD')
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
        $this->redis->sadd($this->supported_symbol, 'CNY_USD');
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
            $data = $content['data'];
            foreach ($data as $item) {
                $key  = $this->quotation_prefix . 'CNY_USD';
                $this->redis->hmset($key, 'low', $item['p'], 'high', $item['p'], 'last', $item['p']);
                $this->redis->sadd($this->supported_symbol, 'CNY_USD');
                break;
            }
        }
    }

}