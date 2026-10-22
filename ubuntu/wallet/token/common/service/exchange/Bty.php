<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Bty extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_bty';
    protected $quotation_prefix = 'quotation_bty_';
    protected $base_url         = 'https://127.0.0.1:80/api/data/Ticker?sort=cname&platform=';

    /**
     * 转化交易对为请求变量
     *
     * @param string $tag
     * @param string $aim
     * @return mixed
     */
    public function formatSymbol($tag = 'BTC', $aim = 'USDT')
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
        $this->redis->sadd($this->supported_symbol, 'BTYUSDT');
    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $platforms = ['HKJB', 'BB360'];
        $curl = new Curl();
        foreach($platforms as $platform) {
            $content = $curl->get($this->base_url . $platform, false);
            if (is_array($content) && isset($content['code']) && $content['code'] == 200) {
                $data = $content['data']['USDT'];
                foreach ($data as $item) {
                    if (in_array($item['symbol'], ['BTYUSDT', 'ITVBUSDT'])) {
                        $data = $item;
                        $key  = $this->quotation_prefix . $item['symbol'];
                        $this->redis->hmset($key, 'low', $data['low'], 'high', $data['high'], 'last', $data['last'], 'rmb', $data['lastrmb']);
                        $this->redis->sadd($this->supported_symbol, $item['symbol']);
                    }
                }
            }
        }
    }

}
