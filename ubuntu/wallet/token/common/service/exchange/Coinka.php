<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Coinka extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_coinka';
    protected $quotation_prefix = 'quotation_coinka_';
    protected $base_url = 'https://app.coinka.com.cn/api/v1/allticker';


    public function symbolExists($tag = 'STH', $aim = "USDT")
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
    public function formatSymbol($tag = 'STH', $aim = 'USDT')
    {
        return $tag . '_' . $aim;
    }

    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setSupportedSymbol()
    {
        $this->redis->sadd($this->supported_symbol, 'STHUSDT');
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
        if (is_array($content) && isset($content['ticker'])) {
            $data = $content['ticker'];
            foreach ($data as $item) {
                if (in_array(strtoupper($item['symbol']), ['STH_USDT', 'XJH_USDT'])) {
                    $data = $item;
                    $key = $this->quotation_prefix . $item['symbol'];
                    $this->redis->hmset($key, 'low', $data['low'], 'high', $data['high'], 'last', $data['last'], 'change', $data['change'], 'vol', $data['vol']);
                    $this->redis->sadd($this->supported_symbol, $item['symbol']);
                }
            }
        }
    }

}