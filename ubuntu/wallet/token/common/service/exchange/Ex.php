<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Ex extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_zg';
    protected $quotation_prefix = 'quotation_ex_';
    protected $base_url         = 'https://www.ex.pizza/api/v1/tickers';

    public function symbolExists($tag = 'FOLI', $aim = "USDT")
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
    public function formatSymbol($tag = 'FOLI', $aim = 'USDT')
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
        $this->redis->sadd($this->supported_symbol, 'FOLI_USDT');
    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $curl = new Curl();
        $curl->setHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);
        $content = $curl->get($this->base_url, false);
        if (is_array($content) && isset($content['ticker'])) {
            $data = $content['ticker'];
            foreach ($data as $item) {
                if (in_array($item['symbol'], ['FOLI_USDT'])) {
                    $data = $item;
                    $key  = $this->quotation_prefix . $item['symbol'];
                    $this->redis->hmset($key, 'low', $data['low'], 'high', $data['high'], 'last', $data['last']);
                    $this->redis->sadd($this->supported_symbol, $item['symbol']);
                }
            }
        }
    }

}