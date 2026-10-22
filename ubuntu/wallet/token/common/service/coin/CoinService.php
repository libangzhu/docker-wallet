<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 上午11:17
 */

namespace common\service\coin;

use Yii;
use common\helpers\Curl;

/**
 * 币种服务类
 * Class CoinService
 * @package common\service
 */
class CoinService extends Coin implements CoinInterface
{

    public function __construct($id, $sid)
    {
        $this->id  = $id;
        $this->sid = $sid;
    }

    public function __destruct()
    {
        unset($this->content);
    }

    public function init()
    {
        $url           = Yii::$app->params['feixiaohao_domain'] . Yii::$app->params['feixiaohao_page']['currencies'] . $this->sid . '/';
        $ch            = new Curl();
        $content       = $ch->get($url, true);
        $this->content = str_replace(['&nbsp;', '&nbsp;'], '', $content);
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_price');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=coinprice>(.*?)<span class=(tags-red|tags-green)>(.*?)<\/span><\/div>/is';
            preg_match_all($pattern, $this->content, $matchs, PREG_SET_ORDER);
            $price = $matchs[0][1];
            $price = str_replace(['￥', ','], '', $price);
            $this->setPrice($price);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_price', $this->price,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setPrice($result);
        }
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = (float)sprintf("%0.2f", $price);
    }

    /**
     * @return mixed
     */
    public function getDollar()
    {
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_usd');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            preg_match_all('/<div class=sub><span>(.*?)<\/span>(.*?)<span>(.*?)<\/span><\/div>/is', $this->content,
                $matchs,
                PREG_SET_ORDER);
            $this->setDollar(str_replace(['$', '≈', ','], '', $matchs[0][1]));
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_usd', $this->dollar,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setDollar($result);
        }
        return $this->dollar;
    }

    /**
     * @param mixed $dollar
     */
    public function setDollar($dollar)
    {
        $this->dollar = (float)sprintf("%0.4f", $dollar);
    }

    /**
     * @return mixed
     */
    public function getBtc()
    {
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_btc');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            preg_match_all('/<div class=sub><span>(.*?)<\/span>(.*?)<span>(.*?)<\/span><\/div>/is', $this->content,
                $matchs,
                PREG_SET_ORDER);
            $this->setBtc($matchs[0][3]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_btc', $this->btc,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setBtc($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_high');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            preg_match_all('/<div class=lowHeight>(.*?)<\/div><div class=sub>/is', $this->content, $matchs);
            preg_match_all('/<span class=value>(.*?)<\/span>/is', $matchs[1][0], $matchs);
            $this->setHigh(str_replace(['￥', ','], '', $matchs[1][0]));
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_high', $this->high,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            return $result;
        }
        return $this->high;
    }

    /**
     * @param mixed $high
     */
    public function setHigh($high)
    {
        $this->high = (float)sprintf("%0.2f", $high / 6.6);
    }

    /**
     * @return mixed
     */
    public function getLow()
    {
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_low');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            preg_match_all('/<div class=lowHeight>(.*?)<\/div><div class=sub>/is', $this->content, $matchs);
            preg_match_all('/<span class=value>(.*?)<\/span>/is', $matchs[1][0], $matchs);
            $this->setLow(str_replace(['￥', ','], '', $matchs[1][1]));
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_low', $this->low,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            return $result;
        }
        return $this->low;
    }

    /**
     * @param mixed $low
     */
    public function setLow($low)
    {
        $this->low = (float)sprintf("%0.2f", $low / 6.6);
    }

    /**
     * @return mixed
     */
    public function getChange()
    {
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_change');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=coinprice>(.*?)<span class=(tags-red|tags-green)>(.*?)<\/span><\/div>/is';
            preg_match_all($pattern, $this->content, $matchs, PREG_SET_ORDER);
            $this->setChange($matchs[0][3]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_change', $this->change,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setChange($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_rank');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=value>(.*?)<span class=tag-marketcap>(.*?)<\/span><\/div><div class=sub>(.*?)<\/div><div class=sub>(.*?)<\/div>/is';
            preg_match_all($pattern, $$this->content, $matchs, PREG_SET_ORDER);
            $this->setRank($matchs[0][2]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_rank', $this->rank,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setRank($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_circulate_value_rmb');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=value>(.*?)<span class=tag-marketcap>(.*?)<\/span><\/div><div class=sub>(.*?)<\/div><div class=sub>(.*?)<\/div>/is';
            preg_match_all($pattern, $$this->content, $matchs, PREG_SET_ORDER);
            $this->setCirculateValueRmb($matchs[0][1]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_circulate_value_rmb',
                $this->circulate_value_rmb,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setCirculateValueRmb($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_circulate_value_usd');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=value>(.*?)<span class=tag-marketcap>(.*?)<\/span><\/div><div class=sub>(.*?)<\/div><div class=sub>(.*?)<\/div>/is';
            preg_match_all($pattern, $$this->content, $matchs, PREG_SET_ORDER);
            $this->setCirculateValueUsd($matchs[0][3]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_circulate_value_usd',
                $this->circulate_value_usd,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setCirculateValueUsd($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_circulate_value_btc');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=value>(.*?)<span class=tag-marketcap>(.*?)<\/span><\/div><div class=sub>(.*?)<\/div><div class=sub>(.*?)<\/div>/is';
            preg_match_all($pattern, $$this->content, $matchs, PREG_SET_ORDER);
            $this->setCirculateValueBtc($matchs[0][4]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_circulate_value_btc',
                $this->circulate_value_btc,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setCirculateValueBtc($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_publish_count');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=tit>流通量<\/div><div class=value>(.*?)<\/div><div class=tit>总发行量<\/div><div class=value>(.*?)<\/div>/is';
            preg_match_all($pattern, $$this->content, $matchs, PREG_SET_ORDER);
            $this->setPublishCount($matchs[0][2]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_publish_count',
                $this->publish_count,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setPublishCount($result);
        }
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
        $result = Yii::$app->cache->get($this->cache_key_prifx . $this->sid . '_circulate_count');
        if ($result === false) {
            if (empty($this->content)) {
                $this->init();
            }
            $pattern = '/<div class=tit>流通量<\/div><div class=value>(.*?)<\/div><div class=tit>总发行量<\/div><div class=value>(.*?)<\/div>/is';
            preg_match_all($pattern, $$this->content, $matchs, PREG_SET_ORDER);
            $this->setCirculateCount($matchs[0][1]);
            Yii::$app->cache->set($this->cache_key_prifx . $this->sid . '_publish_count',
                $this->circulate_count,
                Yii::$app->params['curl_cache_time']['quotation']);
        } else {
            $this->setCirculateCount($result);
        }
        return $this->circulate_count;
    }

    /**
     * @param mixed $circulate_count
     */
    public function setCirculateCount($circulate_count)
    {
        $this->circulate_count = $circulate_count;
    }

    /**
     * 爬取交易所列表，默认eos
     *
     * @param $name string 币种名称
     * @return array
     */
    public function exchange()
    {
        //缓存数据
        $key    = [
            __CLASS__,
            __METHOD__,
            'coin',//表名
            $this->sid,//sid
        ];
        $result = Yii::$app->cache->get($key);
        if ($result === false) {
            $url = Yii::$app->params['feixiaohao_domain'] . Yii::$app->params['feixiaohao_page']['coinmarket'] . $this->sid . '/';
            $ch  = new Curl();
            try {
                $content = $ch->get($url, true);
                preg_match_all("/<div class=boxContain><table class=table3 id=markets>(.*)?<\/table>/is", $content,
                    $matchs);
                $content = $matchs[1][0];
                preg_match_all('/<a href="(.*?)"(.*?)alt=(.*?)>/is', $content, $matchs);
                //匹配到了
                //保存匹配数据
                $count  = count($matchs[1]);
                $result = [];
                $r      = [];
                for ($i = 0; $i < $count; $i++) {
                    if (!in_array($matchs[3][$i], $r)) {
                        $r[] = $matchs[3][$i];
                        //爬取正确的官网
                        if (strpos($matchs[1][$i], '/exchange') === false) {
                            $matchs[1][$i] .= 'https:';
                        } else {
                            $url     = 'https://www.feixiaohao.com' . $matchs[1][$i];
                            $content = $ch->get($url, true);
                            preg_match_all('/官网地址：<a(.*?)>(.*?)<\/a><\/span>/', $content, $matchs2);
                            $matchs[1][$i] = $matchs2[2][0];
                        }
                        $result[] = ['url' => $matchs[1][$i], 'name' => $matchs[3][$i]];
                    }
                }
                unset($content, $i, $r, $url, $matchs2);
                Yii::$app->cache->set($key, $result, Yii::$app->params['curl_cache_time']['exchange']);//set caching
                return ['count' => count($result), 'data' => $result];
            } catch (\Exception $exception) {
                return ['count' => 0, 'data' => []];
            }
        }
        return ['count' => count($result), 'data' => $result];
    }

    /**
     * 爬取交易所数量
     * @param string $sid
     * @return integer|bool
     */
    public function exchange_count()
    {
        $key    = [
            __CLASS__,
            __METHOD__,
            'coin',//表名
            $this->sid,//sid
        ];
        $result = Yii::$app->cache->get($key);
        if (!$result) {
            $url = Yii::$app->params['feixiaohao_domain'] . Yii::$app->params['feixiaohao_page']['coinmarket'] . $this->sid . '/';
            $ch  = new Curl();
            try {
                $content = $ch->get($url, true);
                preg_match_all("/<div class=boxContain><table class=table3 id=markets>(.*)?<\/table>/is", $content,
                    $matchs);
                $content = $matchs[1][0];
                preg_match_all('/alt=(.*?)>/is', $content, $matchs);
                //匹配到了
                $result = array_unique($matchs[1]);
                $result = count($result);
                unset($content, $matchs2);
                Yii::$app->cache->set($key, $result,
                    Yii::$app->params['curl_cache_time']['exchange_count']);//set caching
            } catch (\Exception $exception) {

            }
        }
        return $result ? $result : 0;
    }

    /**
     * 爬取币种的实时行情，默认eos
     *
     * @param string $sid
     * @return object|string
     */
    public function quotation()
    {
        //使用缓存
        $key = [
            __CLASS__,
            __METHOD__,
            'coin',//表名
            $this->sid,//sid
        ];
        $result = Yii::$app->cache->get($key);
        if ($result === false) {
            $url = Yii::$app->params['feixiaohao_domain'] . Yii::$app->params['feixiaohao_page']['currencies'] . $this->sid . '/';
            $ch  = new Curl();
            try {
                $content = $ch->get($url, true);
                $result  = [
                    'price'           => '',//价格
                    'dollar'          => '',//价格美元
                    'publish_count'   => '',//发行总量
                    'circulate_count' => '',//流通总量
                ];

                //价格与涨幅
                $pattern = '/<div class=coinprice>(.*?)<span class=(tags-red|tags-green)>(.*?)<\/span><\/div>/is';
                preg_match_all($pattern, $content, $matchs, PREG_SET_ORDER);
                $result['price'] = (float)sprintf("%0.2f", (float)str_replace(['￥', ','], '', $matchs[0][1]));

                //美元 btc
                preg_match_all('/<div class=sub><span>(.*?)<\/span>(.*?)<span>(.*?)<\/span><\/div>/is', $content,
                    $matchs,
                    PREG_SET_ORDER);
                $result['dollar'] = (float)sprintf("%0.4f", (double)str_replace(['$', '≈', ','], '', $matchs[0][1]));

                //爬取流通总量，发行总量
                $pattern = '/<div class=tit>流通量<\/div><div class=value>(.*?)<\/div><div class=tit>总发行量<\/div><div class=value>(.*?)<\/div>/is';
                preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
                $result['circulate_count'] = $matches[0][1];
                $result['publish_count']   = $matches[0][2];

                Yii::$app->cache->set($key, $result, Yii::$app->params['curl_cache_time']['quotation']);//set cache
                return $result;
            } catch (\Exception $exception) {
                return new \stdClass();
            }
        }
        return $result;
    }

}