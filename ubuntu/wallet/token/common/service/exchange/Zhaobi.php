<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Zhaobi extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_zhaobi';
    protected $quotation_prefix = 'quotation_zhaobi_';
    protected $base_url = 'https://127.0.0.1:80';

    public function symbolExists($tag = 'SFT', $aim = "CNY")
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
    public function formatSymbol($tag = 'SFT', $aim = 'CNY')
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
        $this->redis->sadd($this->supported_symbol, 'SFT_CNY');
    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $curl = new Curl();
        $api = $this->base_url . '/api/data/Ticker?sort=cname';
        $content = $curl->get($api, false);
        if (isset($content['message']) && 'OK' == $content['message']) {
            $ticker_temp = [];
            foreach ($content['data'] as $val) {
                $ticker_temp = array_merge($ticker_temp, $val);
            }
            foreach ($ticker_temp as $val) {
                $key = $this->quotation_prefix . strtolower($val['symbol']);
                $this->redis->hmset($key, 'low', $val['low'], 'high', $val['high'], 'last', $val['last'], 'open', $val['open'], 'vol', $val['vol'], 'rmb', $val['lastrmb']);
                $this->redis->sadd($this->supported_symbol, $val['symbol']);
            }
        }
    }
}
