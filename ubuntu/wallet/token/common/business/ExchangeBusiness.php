<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-27
 * Time: 下午12:04
 */

namespace common\business;

use common\models\psources\Coin;
use common\models\psources\WalletCoinTicker;
use common\service\coin\CoinFactory;
use common\service\exchange\ExchangeFactory;
use Yii;

/**
 * Class ExchangeBusiness
 * 各大交易所交易所api逻辑层
 *
 * @package common\business
 */
class ExchangeBusiness
{
    /**支持的交易所
     *
     * @var array
     */
    private static $exchanges = [
        //0 => 'Bty',
        0 => 'HuoBi',
        1 => 'Hadax',
        2 => 'Bittrex',
        3 => 'Zb',
        4 => 'Superx',
        5 => 'Binance',
        6 => 'Bilaxy',
        7 => 'Bitnasdaq',
        8 => 'Isummit',
        9 => 'Ztb',
        10 => 'Wbf',
        11 => 'Gwa',
        12 => 'Ljz'

        //1 => 'Hadax', //不需要
        //2 => 'Bitfinex', //不需要
        //3 => 'Bittrex', //不需要
        //4 => 'Zb', //不需要
        //5 => 'Zg',//不需要
        //6 => 'Biki',//不需要
        //7 => 'S',//已挂
        //7 => 'Gdpro',//已挂
        //8 => 'Ceohk', //已挂
        //14 => 'Biki',//已挂
        //6 => 'Token7',//已挂
        //10 => 'Ex',//已挂
        //12 => 'Tsc',//已挂
        //16 => 'Dag',//已挂
        //17 => 'Coinka',//已挂
        //5 => 'Zg',//已挂
        //13 => 'Jinwang',//已挂
        //14 => 'Hd',//已挂
        //15 => 'Zhaobi',//已挂
    ];

    /**
     * 获取行情
     *
     * @param  string $tag 目标币种
     * @param  string $aim 计数币种
     * @return array|bool
     */
    public static function getquatation($tag = 'btc')
    {
        $coinTicker = WalletCoinTicker::find()->where(['coin_name' => $tag])->asArray()->one();
        if (!empty($coinTicker)) {
            $quotation = [
                'low' => $coinTicker['ticker'],
                'high' => $coinTicker['ticker'],
                'last' => $coinTicker['ticker'],
                'rmb' => $coinTicker['ticker'],
            ];
            goto doEnd;
        }

        $coin_quotation_disable_items = Yii::$app->params['coin_quotation_disable_items'];

        if (strtoupper($tag) == 'USAT') {
            $tag = 'USDT';
        }

        if (strtoupper($tag) == 'DUSDT') {
            $quotation = [
                'low' => 6.99,
                'high' => 6.99,
                'last' => 6.99,
                'rmb' => 6.99,
            ];
            goto doEnd;
        }

        if (strtoupper($tag) == 'YPLUS') {
            $quotation = [
                'low' => 10,
                'high' => 10,
                'last' => 10,
                'rmb' => 10,
            ];
            goto doEnd;
        }

        if (strtoupper($tag) == 'BOSS') {
            $quotation = [
                'low' => 2000,
                'high' => 2000,
                'last' => 2000,
                'rmb' => 2000,
            ];
            goto doEnd;
        }

        if (strtoupper($tag) == 'CPF') {
            $quotation = [
                'low' => 3.4,
                'high' => 3.4,
                'last' => 3.4,
                'rmb' => 3.4,
            ];
            goto doEnd;
        }

        if (strtoupper($tag) == 'FZM') {
            $quotation = [
                'low' => 40,
                'high' => 40,
                'last' => 40,
                'rmb' => 40,
            ];
            goto doEnd;
        }

        if (strtoupper($tag) == 'POKE') {
            $quotation = [
                'low' => 0.1621,
                'high' => 0.1621,
                'last' => 0.1621,
                'rmb' => 0.1621,
            ];
            goto doEnd;
        }

        if (strtoupper($tag) == 'LHG') {
            $quotation = [
                'low' => 0.105,
                'high' => 0.105,
                'last' => 0.105,
                'rmb' => 0.105,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['LZ', 'DTM', 'CNNY'])) {
            $quotation = [
                'low' => 1,
                'high' => 1,
                'last' => 1,
                'rmb' => 1,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['SZHB', 'FK'])) {
            $quotation = [
                'low' => 0.01,
                'high' => 0.01,
                'last' => 0.01,
                'rmb' => 0.01,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['GST', 'JNTK', 'SPT', 'STO', 'GM', 'BSTC', 'RYH', 'CNDT', 'WL', 'ETS', 'LIMS', 'AT', 'BTJ', 'WXB', 'GFCC', 'TNT'])) {
            $quotation = [
                'low' => 0,
                'high' => 0,
                'last' => 0,
                'rmb' => 0,
            ];
            goto doEnd;
        }

        if (in_array($tag, $coin_quotation_disable_items)) {
            return false;
        }
        $f = false;
        $quotation = [];
        if (in_array(strtoupper($tag), ['LELE', 'ZUE'])) {
            if (strtoupper($tag) == 'LELE') {
                $exchange = ExchangeFactory::createExchange("Wbf");
                $quotation = $exchange->getTicker($tag, 'USDT');
            }
            if (strtoupper($tag) == 'ZUE') {
                $quotation = [
                    'low' => 0.1,
                    'high' => 0.1,
                    'last' => 0.1,
                ];
            }
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['GHT','JD'])) {
            $exchange = ExchangeFactory::createExchange("Ljz");
            $quotation = $exchange->getTicker($tag, 'CNYD');
            $quotation['rmb'] = $quotation['last'];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['GLCW'])) {
            $exchange = ExchangeFactory::createExchange("Jinwang");
            $quotation = $exchange->getTicker($tag, 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['SUM', 'USDW', 'FUT'])) {
            $exchange = ExchangeFactory::createExchange("Isummit");
            $quotation = $exchange->getTicker($tag, 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['PAX'])) {
            $exchange = ExchangeFactory::createExchange("HuoBi");
            $quotation = $exchange->getTicker($tag, 'husd');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['DRA'])) {
            $exchange = ExchangeFactory::createExchange("Draex");
            $quotation = $exchange->getTicker($tag, 'USDT');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['FOLI'])) {
            $exchange = ExchangeFactory::createExchange("Ex");
            $quotation = $exchange->getTicker($tag, 'USDT');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['DAG'])) {
            $exchange = ExchangeFactory::createExchange("Dag");
            $quotation = $exchange->getTicker($tag, 'USDT');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['BNC'])) {
            $exchange = ExchangeFactory::createExchange("Bitnasdaq");
            $quotation = $exchange->getTicker($tag, 'USDT');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['GM', 'BSTC'])) {
            $exchange = ExchangeFactory::createExchange("Token7");
            $quotation = $exchange->getTicker($tag, 'HA');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['BECC'])) {
            $exchange = ExchangeFactory::createExchange("S");
            $quotation = $exchange->getTicker($tag, 'ST');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['GHP'])) {
            $exchange = ExchangeFactory::createExchange("Bitnasdaq");
            $quotation = $exchange->getTicker($tag);
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['CTG'])) {
            $exchange = ExchangeFactory::createExchange("Gdpro");
            $quotation = $exchange->getTicker($tag, 'CNY');
            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['BTY', 'YCC'])) {
            $exchange = ExchangeFactory::createExchange("Superx");
            $quotation = $exchange->getTicker($tag, 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['USDT'])) {
//            $exchange = ExchangeFactory::createExchange("Rate");
//            $quotation = $exchange->getTicker("CNY", "USD");
//            $quotation['rmb'] = (float)sprintf("%0.4f", $quotation['last']);

            $cny_usd_rate_low = Yii::$app->redis_currency->hmget('quotation_currency_USD_CNY', 'low');
            $quotation['low'] = (float)sprintf("%0.4f", $cny_usd_rate_low[0]);
            $cny_usd_rate_high = Yii::$app->redis_currency->hmget('quotation_currency_USD_CNY', 'high');
            $quotation['high'] = (float)sprintf("%0.4f", $cny_usd_rate_high[0]);
            $cny_usd_rate_last = Yii::$app->redis_currency->hmget('quotation_currency_USD_CNY', 'last');
            $quotation['last'] = $quotation['rmb'] = (float)sprintf("%0.4f", $cny_usd_rate_last[0]);

            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['CIC', 'MC'])) {
            $exchange = ExchangeFactory::createExchange("Ztb");
            $quotation = $exchange->getTicker(strtoupper($tag), 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['STH', 'XJH'])) {
            $exchange = ExchangeFactory::createExchange("Coinka");
            $quotation = $exchange->getTicker(strtolower($tag), 'usdt');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['TSC'])) {
            $exchange = ExchangeFactory::createExchange("Tsc");
            $quotation = $exchange->getTicker('TSC', 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['TIX'])) {
            $exchange = ExchangeFactory::createExchange("Ceohk");
            $quotation = $exchange->getTicker('TIX', 'QC');
            $quotation['rmb'] = $quotation['last'];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['KPC8'])) {
            $quotation = [
                'low' => 1,
                'high' => 1,
                'last' => 1,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['BBD'])) {
            $quotation = [
                'low' => 0.2,
                'high' => 0.2,
                'last' => 0.2,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['LFT'])) {
            $quotation = [
                'low' => 100,
                'high' => 100,
                'last' => 100,
                'rmb' => 100,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['ONE'])) {
            $quotation = [
                'low' => 328,
                'high' => 328,
                'last' => 328,
                'rmb' => 328,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['ZYC'])) {
            $quotation = [
                'low' => 0.1,
                'high' => 0.1,
                'last' => 0.1,
                'rmb' => 0.1,
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['BVA'])) {
            $exchange = ExchangeFactory::createExchange("Bilaxy");
            $quotation = $exchange->getTicker('BVA', 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['MBTC', 'METH'])) {
            $exchange = ExchangeFactory::createExchange("HuoBi");
            if ('MBTC' == $tag) {
                $quotation = $exchange->getTicker('btc', 'usdt');
            }
            if ('METH' == $tag) {
                $quotation = $exchange->getTicker('eth', 'usdt');
            }
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['SJPY'])) {
            $exchange = ExchangeFactory::createExchange("Boc");
            $quotation = $exchange->getTicker('CNY', 'JPY');
            $quotation = [
                'low' => (float)sprintf("%0.4f", $quotation['low'] / 100),
                'high' => (float)sprintf("%0.4f", $quotation['high'] / 100),
                'last' => (float)sprintf("%0.4f", $quotation['last'] / 100),
                'rmb' => (float)sprintf("%0.4f", $quotation['last'] / 100),
            ];
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['HDC'])) {
            $exchange = ExchangeFactory::createExchange("Hd");
            $quotation = $exchange->getTicker('HDC', 'USDT');
            goto doEnd;
        }

        if (in_array(strtoupper($tag), ['GWA', 'KLG'])) {
            $exchange = ExchangeFactory::createExchange("Gwa");
            $quotation = $exchange->getTicker(strtoupper($tag), 'USDT');
            goto doEnd;
        }

        foreach (self::$exchanges as $exchange) {
            /**
             * @var $exchange \common\service\exchange\Exchange
             */
            $exchange = ExchangeFactory::createExchange($exchange);
            if ($exchange->symbolExists($tag)) {
                $quotation = $exchange->getTicker($tag);
                $f = true;
                break;
            }
        }
        if (!$f) {
            //所有交易所都不能直接获取交易对的行情就通过BTC去转换
            //获取BTC_USD
            $btc_usd = ExchangeFactory::createExchange(self::$exchanges[1])->getTicker('btc');
            foreach (self::$exchanges as $exchange) {
                /**
                 * @var $exchange \common\service\exchange\Exchange
                 */
                $exchange = ExchangeFactory::createExchange($exchange);
                if ($exchange->symbolExists($tag, 'btc')) {
                    $price_btc = $exchange->getTicker($tag, 'btc');
                    //获取btcusdt
                    $result = array_map(function ($a, $b) {
                        return $a * $b;
                    }, $price_btc, $btc_usd);
                    $quotation = ['low' => $result[0], 'high' => $result[1], 'last' => $result[2]];
                    $f = true;
                    break;
                }
            }
        }
        if (!$f || empty($quotation)) {
            return false;
        }
        //格式化行情数据
        foreach ($quotation as $key => $item) {
            $quotation[$key] = (float)sprintf("%0.4f", (double)$item);
        }

        doEnd :
        $cny_usd_rate = Yii::$app->redis_currency->hmget('quotation_currency_CNY_USD', 'last');
        $cny_usd_rate = floatval($cny_usd_rate[0]);

        $quotation['rmb'] = isset($quotation['rmb']) ? $quotation['rmb'] : (float)sprintf("%0.4f", $quotation['last'] / $cny_usd_rate);
        if (in_array(strtoupper($tag), ['FOLI', 'CIC', 'MC', 'KPC8', 'BBD', 'BVA', 'DAG', 'BNC', 'BNB', 'GHP', 'DRA', 'ETC', 'PAX', 'STH', 'XJH', 'SFT', 'TSC', 'SUM', 'USDW', 'FUT', 'MBTC', 'METH', 'GLCW', 'HDC', 'GWA', 'KLG', 'LELE', 'ZUE'])) {
            $quotation['usd'] = (float)sprintf("%0.4f", $quotation['last']);
            $quotation['low'] = (float)sprintf("%0.4f", $quotation['low']);
            $quotation['high'] = (float)sprintf("%0.4f", $quotation['high']);
            $quotation['last'] = (float)sprintf("%0.4f", $quotation['last']);
            $quotation['open'] = isset($quotation['open']) ? (float)sprintf("%0.4f", $quotation['open']) : $quotation['last'];
        } else if (in_array(strtoupper($tag), ['SUSD'])) {
            $quotation['usd'] = (float)sprintf("%0.4f", 1);
        } else {
            $quotation['usd'] = (float)sprintf("%0.4f", $quotation['rmb'] * $cny_usd_rate);
        }
        return $quotation;
    }

    /**
     * 保存各个交易所支持的交易对到redis，定时用crontab更新
     *
     * @return void
     */
    public static function setSupportedSymbol()
    {
        foreach (self::$exchanges as $exchange) {
            /**
             * @var $exchange \common\service\exchange\ExchangeInterface
             */
            $exchange = ExchangeFactory::createExchange($exchange);
            $exchange->setSupportedSymbol();
        }
    }

    /**
     * 更新交易所的行情数据保存到redis，定时更新
     *
     * @return void
     */
    public static function setQuotation()
    {
        foreach (self::$exchanges as $exchange) {
            /**
             * @var $exchange \common\service\exchange\ExchangeInterface
             */
            //go(function () use ($exchange) {
            //    \Co::sleep(0.5);
                $exchange = ExchangeFactory::createExchange($exchange);
                $exchange->setQuotation();
            //});
        }
    }

    /**
     * 根据name返回币种信息
     *
     * @param  array $condition 需要的币种sid列表
     * @return array
     */
    public static function getApiListForIndex($page = 1, $limit = 999, $condition = [], $fields = [])
    {
        if(is_array($condition[0]) && (count($condition[0]) > 1)) {
            foreach ($condition as $val) {
                if ('null' == $val[1] || 'coin' == $val[1]) {
                    $data[] = Coin::find()->select('id,sid,icon,name,optional_name,nickname,introduce,platform,chain,address as contract_address,treaty')
                        ->where(['name' => $val[0]])
                        ->asArray()
                        ->one();
                    continue;
                }
                $data[] = Coin::find()->select('id,sid,icon,name,optional_name,nickname,introduce,platform,chain,address as contract_address,treaty')
                    ->where(['name' => $val[0]])
                    ->andWhere(['platform' => $val[1]])
                    ->asArray()
                    ->one();
            }
        } else {
            foreach ($condition as $val) {
                $data[] = Coin::find()->select('id,sid,icon,name,optional_name,nickname,introduce,platform,chain,address as contract_address,treaty')
                    ->where(['name' => $val])
                    ->asArray()->one();
            }
        }
        $rows = [
            'count' => count($data),
            'data' => $data
        ];

        $count = 0;
        if (!empty($rows) && is_array($rows) && array_key_exists('count', $rows)) {
            $count = $rows['count'];
        }
        if ($rows['count'] > 0) {
            $rows = $rows['data'];
            foreach ($rows as $key => $row) {
                $rows[$key]['sid'] = ucfirst($rows[$key]['sid']);
                $quotation = self::getquatation($row['name']);
                if (!$quotation) {
                    $quotation = [];
                    if (in_array($row['name'], ['BTY', 'YCC'])) {
                        $coinServer = CoinFactory::createCoin($row['name'], $row['id'], $row['sid']);
                        $rows[$key]['sid'] = ucfirst($rows[$key]['sid']);
                        $rows[$key]['rmb'] = $coinServer->getPrice();
                        $rows[$key]['last'] = $coinServer->getDollar();
                        $rows[$key]['low'] = $coinServer->getLow();
                        $rows[$key]['high'] = $coinServer->getHigh();
                        $coinServer->__destruct();
                    } else {
                        $rows[$key]['rmb'] = 0;
                        $rows[$key]['last'] = 0;
                        $rows[$key]['low'] = 0;
                        $rows[$key]['high'] = 0;
                        $rows[$key]['usd'] = 0;
                    }
                }
                if (strtoupper($row['platform']) == 'GUODUN') {
                    $rows[$key]['rmb'] = 0;
                    $rows[$key]['last'] = 0;
                    $rows[$key]['low'] = 0;
                    $rows[$key]['high'] = 0;
                } else {
                    $rows[$key] = array_merge($rows[$key], $quotation);
                }
            }
            return ['code' => 0, 'count' => $count, 'data' => $rows];
        }
        return [];
    }

    /**
     * 根据名称搜索
     *
     * @param  int $page
     * @param  int $limit
     * @param  array $condition
     * @return array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function SearchByName($page = 1, $limit = 10, $condition = [])
    {
        $rows = Coin::getSelectList($page, $limit, ['id', 'sid', 'icon', 'name', 'optional_name', 'nickname', 'platform', 'chain', 'address as contract_address', 'treaty'],
            $condition);
        if ($rows['count'] > 0) {
            $total = $rows['count'];
            $rows = $rows['data'];
            foreach ($rows as $key => $row) {
                $rows[$key]['sid'] = ucfirst($rows[$key]['sid']);
                $platform = strtoupper($rows[$key]['platform']);
                $platform_icon = Yii::$app->redis->hget('platform_image_info', $platform);

                $rows[$key]['platform_icon'] = $platform_icon ?? '';
            }
            return ['total' => $total, 'data' => $rows];
        }
        return [];
    }

    public static function getQuatationByNames($names)
    {
        $result = [];
        foreach ($names as $key => $value) {
            $quotation = self::getquatation($value);
            if (!$quotation) {
                $quotation = [];
                //使用Coin服务
                try {
                    $coinServer = CoinFactory::createCoin($row['name'], $row['id'], $row['sid']);
                    $quotation['rmb'] = $coinServer->getPrice();
                    $quotation['last'] = $coinServer->getDollar();
                    $quotation['low'] = $coinServer->getLow();
                    $quotation['high'] = $coinServer->getHigh();
                    $coinServer->__destruct();
                } catch (\Exception $exception) {
                    $quotation['rmb'] = 0;
                    $quotation['last'] = 0;
                    $quotation['low'] = 0;
                    $quotation['high'] = 0;
                    \Yii::error($exception->getMessage());
                }
            }
            $result[$value] = $quotation;
        }
        return $result;
    }
}
