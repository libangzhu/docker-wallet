<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Zg extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_zg';
    protected $quotation_prefix = 'quotation_zg_';
    protected $base_url = 'https://api.zg.com/openapi/quote/v1/ticker/24hr';

    public function symbolExists($tag = 'HL', $aim = "USDT")
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
    public function formatSymbol($tag = 'HL', $aim = 'USDT')
    {
        return strtoupper($tag . '_' . $aim);
    }

    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setSupportedSymbol()
    {
        $this->redis->sadd($this->supported_symbol, 'HL_USDT');
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
        if (is_array($content)) {
            foreach ($content as $item) {
                if (in_array($item['symbol'], ['HLUSDT', 'GHPUSDT'])) {
                    if ($item['symbol'] == 'HLUSDT') {
                        $symbol = 'HL_USDT';
                    }
                    if ($item['symbol'] == 'GHPUSDT') {
                        $symbol = 'GHP_CNZ';
                    }
                    $data = $item;
                    $key = $this->quotation_prefix . $symbol;
                    $this->redis->hmset($key, 'low', $data['lowPrice'], 'high', $data['highPrice'], 'last', $data['lastPrice'], 'open', $data['openPrice']);
                    $this->redis->sadd($this->supported_symbol, $symbol);
                }
            }
        }
    }

}