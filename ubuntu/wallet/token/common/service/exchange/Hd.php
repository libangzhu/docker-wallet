<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Hd extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_hd';
    protected $quotation_prefix = 'quotation_hd_';
    protected $base_url = 'https://app.honordecent.com/exchange/getNowData';

    public function symbolExists($tag = 'HDC', $aim = "USDT")
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
    public function formatSymbol($tag = 'HDC', $aim = 'USDT')
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
        $res = $curl->get($this->base_url, false);
        if (is_array($res) && 1 == $res['code']) {
            $key = $this->quotation_prefix . 'HDCUSDT';
            $this->redis->hmset($key, 'low', $res['data']['currPrice'], 'high', $res['data']['currPrice'], 'last', $res['data']['currPrice'], 'change', $res['data']['range']);
            if (!$this->redis->sismember($this->supported_symbol, 'HDCUSDT')) {
                $this->redis->sadd($this->supported_symbol, 'HDCUSDT');
            }
        }
    }

}