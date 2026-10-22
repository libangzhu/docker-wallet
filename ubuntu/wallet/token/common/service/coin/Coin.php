<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-19
 * Time: 下午2:25
 */

namespace common\service\coin;

abstract class Coin
{
    //行情信息
    protected $price;               //价格
    protected $dollar;              //价格美元
    protected $btc;                 //价格btc
    protected $high;                //最高价
    protected $low;                 //最低价
    protected $change;              //涨幅（跌幅）
    protected $rank;                //流通市值排名
    protected $circulate_value_rmb; //流通市值人民币
    protected $circulate_value_usd; //流通市值美元
    protected $circulate_value_btc; //流通市值btc
    protected $publish_count;       //发行总量
    protected $circulate_count;     //流通总量


    protected $content;//行情html数据

    public $id;//币种id
    public $sid;//币种sid

    public $cache_key_prifx = 'quotation_coin_';

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getDollar()
    {
        return $this->dollar;
    }

    /**
     * @param mixed $dollar
     */
    public function setDollar($dollar)
    {
        $this->dollar = $dollar;
    }

    /**
     * @return mixed
     */
    public function getBtc()
    {
        return $this->btc;
    }

    /**
     * @param mixed $btc
     */
    public function setBtc($btc)
    {
        $this->btc = $btc;
    }

    /**
     * @return mixed
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * @param mixed $high
     */
    public function setHigh($high)
    {
        $this->high = $high;
    }

    /**
     * @return mixed
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * @param mixed $low
     */
    public function setLow($low)
    {
        $this->low = $low;
    }

    /**
     * @return mixed
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * @param mixed $change
     */
    public function setChange($change)
    {
        $this->change = $change;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getCirculateValueRmb()
    {
        return $this->circulate_value_rmb;
    }

    /**
     * @param mixed $circulate_value_rmb
     */
    public function setCirculateValueRmb($circulate_value_rmb)
    {
        $this->circulate_value_rmb = $circulate_value_rmb;
    }

    /**
     * @return mixed
     */
    public function getCirculateValueUsd()
    {
        return $this->circulate_value_usd;
    }

    /**
     * @param mixed $circulate_value_usd
     */
    public function setCirculateValueUsd($circulate_value_usd)
    {
        $this->circulate_value_usd = $circulate_value_usd;
    }

    /**
     * @return mixed
     */
    public function getCirculateValueBtc()
    {
        return $this->circulate_value_btc;
    }

    /**
     * @param mixed $circulate_value_btc
     */
    public function setCirculateValueBtc($circulate_value_btc)
    {
        $this->circulate_value_btc = $circulate_value_btc;
    }

    /**
     * @return mixed
     */
    public function getPublishCount()
    {
        return $this->publish_count;
    }

    /**
     * @param mixed $publish_count
     */
    public function setPublishCount($publish_count)
    {
        $this->publish_count = $publish_count;
    }

    /**
     * @return mixed
     */
    public function getCirculateCount()
    {
        return $this->circulate_count;
    }

    /**
     * @param mixed $circulate_count
     */
    public function setCirculateCount($circulate_count)
    {
        $this->circulate_count = $circulate_count;
    }
}