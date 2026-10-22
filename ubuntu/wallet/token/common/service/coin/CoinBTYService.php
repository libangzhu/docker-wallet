<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-19
 * Time: 下午2:20
 */

namespace common\service\coin;

use linslin\yii2\curl\Curl;
use Yii;

class CoinBTYService extends Coin implements CoinInterface
{
    public function exchange()
    {
        // TODO: Implement exchange() method.
        return ['count' => 0, 'data' => []];
    }

    public function exchange_count()
    {
        // TODO: Implement exchange_count() method.
        return 0;
    }

    /**
     * 行情获取
     */
    public function quotation()
    {
        $result = [
            'price'               => $this->getPrice(),//价格
            'dollar'              => $this->getDollar(),//价格美元
            'publish_count'       => '',//发行总量
            'circulate_count'     => '',//流通总量
        ];
        return $result;
    }

    public function __construct($id, $sid)
    {
        $this->id  = $id;
        $this->sid = $sid;
        $this->init();
    }

    public function init()
    {
        $key    = $this->cache_key_prifx . $this->sid . 'all';
        $result = Yii::$app->cache->get($key);
        if ($result === false || YII_ENV_DEV) {
            $url     = 'https://zbapi.licai.cn/api/data/Ticker?sort=cname';
            $ch      = new Curl();
            $content = $ch->get($url, false);
            if (is_array($content) && isset($content['code']) && $content['code'] == 200) {
                $data = $content['data']['USDT'];
                foreach ($data as $item) {
                    if ($item['symbol'] == 'BTYUSDT') {
                        $data = $item;
                        break;
                    }
                }
                $this->content = $data;
                Yii::$app->cache->set($key, $data, Yii::$app->params['curl_cache_time']['quotation']);
            }
        } else {
            $this->content = $result;
        }
    }

    public function __destruct()
    {
        unset($this->content);
    }

    public function getPrice()
    {
        if ($this->content) {
            $this->setPrice($this->content['lastrmb']);
        } else {
            return 0;
        }
        return $this->price;
    }

    public function setPrice($price)
    {
        return $this->price = (float)sprintf("%0.4f", $price);
    }

    public function getDollar()
    {
        if ($this->content) {
            $this->setDollar($this->content['last']);
        } else {
            return 0;
        }
        return $this->dollar;
    }

    public function setDollar($dollar)
    {
        $this->dollar = (float)sprintf("%0.4f", $dollar);
    }

    public function getHigh()
    {
        if ($this->content) {
            $this->setHigh($this->content['high']);
        } else {
            return 0;
        }
        return $this->high;
    }

    public function setHigh($high)
    {
        return $this->high = (float)sprintf("%0.4f", $high);
    }

    public function getLow()
    {
        if ($this->content) {
            $this->setLow($this->content['low']);
        } else {
            return 0;
        }
        return $this->low;
    }

    public function setLow($low)
    {
        $this->low = (float)sprintf("%0.4f", $low);
    }

    public function getChange()
    {
        if ($this->content) {
            $this->setChange($this->content['range']);
        } else {
            return 0;
        }
        return $this->change;
    }

    public function setChange($change)
    {
        $this->change = "$change%";
    }


}