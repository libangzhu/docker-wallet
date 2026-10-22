<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Wbf extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_wbf';
    protected $quotation_prefix = 'quotation_wbf_';
    protected $base_url         = 'https://openapi.wbf.live/open/api/get_allticker';

    public function symbolExists($tag = 'LELE', $aim = "USDT")
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
    public function formatSymbol($tag = 'LELE', $aim = 'USDT')
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
        if (is_array($res) && isset($res['data'])) {
            $data = $res['data']['ticker'];
            foreach ($data as $item) {
                $key = $this->quotation_prefix . strtoupper($item['symbol']);
                $low = isset($item['low']) ? $item['low'] : 0;
                $high = isset($item['high']) ? $item['high'] : 0;
                $last = isset($item['last']) ? $item['last'] : 0;
                $vol = isset($item['vol']) ? $item['vol'] : 0;
                $change = isset($item['change']) ? $item['change'] : 0;
                $this->redis->hmset($key, 'low', $low, 'high', $high, 'last', $last, 'vol', $vol, 'change', $change);
                if (!$this->redis->sismember($this->supported_symbol, strtoupper($item['symbol']))){
                    $this->redis->sadd($this->supported_symbol, strtoupper($item['symbol']));
                }
            }
        }
    }

}