<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-19
 * Time: 下午2:20
 */

namespace common\service\coin;

use common\helpers\Curl;
use Yii;

class CoinYCCService extends Coin implements CoinInterface
{
    public function exchange()
    {
        return ['count' => 0, 'data' => []];
    }

    public function exchange_count()
    {
        return 0;
    }

    /**
     * 行情获取
     */
    public function quotation()
    {
        $result = [
            'price'               => '',//价格
            'dollar'              => '',//价格美元
            'btc'                 => '',//价格btc
            'high'                => '',//最高价
            'low'                 => '',//最低价
            'change'              => '',//涨幅（跌幅）
            'rank'                => '',//流通市值排名
            'circulate_value_rmb' => '',//流通市值人民币
            'circulate_value_usd' => '',//流通市值美元
            'circulate_value_btc' => '',//流通市值btc
            'publish_count'       => '',//发行总量
            'circulate_count'     => '',//流通总量
        ];
        return $result;
    }

    public function __construct($id, $sid)
    {
        $this->id  = $id;
        $this->sid = $sid;
    }

    public function init()
    {
        $url           = 'https://kdata.zhaobi.com:4062/kdata?datafile=db&c=BTYUSDT&p=H1&action=init&count=24&ind=volumes&out=json';
        $ch            = new Curl();
        $content       = $ch->get($url, false);
        $this->content = $content;
    }

    public function __destruct()
    {
        unset($this->content);
    }

    public function getPrice()
    {
        return "0.2";
    }

    public function setPrice($price)
    {
        return 0;
    }

    public function getDollar()
    {
        return 0;
    }

    public function setDollar($dollar)
    {
        return 0;
    }

    public function getHigh()
    {
        return 0.2;
    }

    public function getLow()
    {
        return 0.2;
    }

}