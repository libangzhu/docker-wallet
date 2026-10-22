<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;

class Ljz extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_ljz';
    protected $quotation_prefix = 'quotation_ljz_';
    protected $base_url = 'http://127.0.0.1/market/overview';

    public function symbolExists($tag = 'GHT', $aim = "CNYD")
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
    public function formatSymbol($tag = 'GHT', $aim = 'CNYD')
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
        if (is_array($res) && count($res['changeRank']) > 0) {
            foreach ($res['changeRank'] as $val){
                $key = $this->quotation_prefix . str_replace('/','_', $val['symbol']);
                $this->redis->hmset($key, 'low', $val['low'], 'high', $val['high'], 'last', $val['close'], 'change', $val['change'],'open', $val['open'],'vol',$val['volume']);
                if (!$this->redis->sismember($this->supported_symbol, str_replace('/','', $val['symbol']))) {
                    $this->redis->sadd($this->supported_symbol, str_replace('/','', $val['symbol']));
                }
            }
        }
    }
}
