<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Jinwang extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_jinwang';
    protected $quotation_prefix = 'quotation_jinwang_';
    protected $base_url = 'http://www.jinwangin.com/exapi/api/klinevtwo/message';

    public function symbolExists($tag = 'TG', $aim = "USDT")
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
    public function formatSymbol($tag = 'TG', $aim = 'USDT')
    {
        return strtolower($tag . '_' . $aim);
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
        $resp = $curl->get($this->base_url, false);
        if (is_array($resp)) {
            if (isset($resp['marketDetail'])) {
                foreach ($resp['marketDetail'] as $symbol => $item) {
                    $key = $this->quotation_prefix . strtolower($symbol);
                    $this->redis->hmset($key, 'low', $item[0]["payload"]['priceLow'], 'high', $item[0]["payload"]['priceHigh'], 'last', $item[0]["payload"]['priceLast'], 'open', $item[0]["payload"]['priceOpen'], 'vol', $item[0]["payload"]['totalAmount']);
                    if (!$this->redis->sismember($this->supported_symbol, strtoupper($symbol))) {
                        $this->redis->sadd($this->supported_symbol, strtoupper($symbol));
                    }
                }
            }
        }
    }

}