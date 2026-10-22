<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/8/15
 * Time: 10:10
 */

namespace common\service\exchange\factory;

use common\components\Tools;
use linslin\yii2\curl\Curl;

class ZhaobiBuilder extends FactoryService
{
    protected $base_url = 'https://127.0.0.1:80';
    protected $supported_symbol = 'supported_symbol_zhaobi';
    protected $supported_symbol_list = 'supported_symbol_zhaobi_list';
    protected $supported_symbol_close_asc = 'supported_symbol_close_asc_zhaobi';
    protected $supported_symbol_close_desc = 'supported_symbol_close_desc_zhaobi';
    protected $supported_symbol_change_asc = 'supported_symbol_change_asc_zhaobi';
    protected $supported_symbol_change_desc = 'supported_symbol_change_desc_zhaobi';
    protected $quotation_prefix = 'quotation_zhaobi_';

    public function getTickerFromCache($page = 1, $condition = [])
    {
        $this->start = 0;
        $this->end = 50;
        if (false != $condition) {
            if ('price' == $condition['data_value']) {
                if ('price_asc' == $condition['sort_value']) {
                    $keys = $this->redis_ticker->lrange($this->supported_symbol_close_asc, $this->start, $this->end);
                }
                if ('price_desc' == $condition['sort_value']) {
                    $keys = $this->redis_ticker->lrange($this->supported_symbol_close_desc, $this->start, $this->end);
                }
            }
            if ('change' == $condition['data_value']) {
                if ('change_asc' == $condition['sort_value']) {
                    $keys = $this->redis_ticker->lrange($this->supported_symbol_change_asc, $this->start, $this->end);
                }
                if ('change_desc' == $condition['sort_value']) {
                    $keys = $this->redis_ticker->lrange($this->supported_symbol_change_desc, $this->start, $this->end);
                }
            }
        } else {
            $keys = $this->redis->lrange($this->supported_symbol_list, $this->start, $this->end);
        }
        $ticker = [];
        foreach ($keys as $val) {
            foreach ($this->basic_coin as $k => $coin) {
                $explode_arr = explode($coin, $val);
                if (2 == count($explode_arr) && empty($explode_arr[1])) {
                    if (false != $condition) {
                        list($low, $high, $close, $open, $vol) = $this->redis_ticker->hmget($this->quotation_prefix . strtoupper($val), 'low', 'high', 'last', 'open', 'vol');
                    } else {
                        list($low, $high, $close, $open, $vol) = $this->redis->hmget($this->quotation_prefix . strtolower($val), 'low', 'high', 'last', 'open', 'vol');
                    }
                    $temp = [];
                    $temp['symbol'] = ('BCC' == strtoupper($explode_arr[0])) ? 'BCH' . '/' . $coin : strtoupper($explode_arr[0]) . '/' . $coin;
                    $temp['currency'] = ('BCC' == strtoupper($explode_arr[0])) ? 'BCH' : strtoupper($explode_arr[0]);
                    $temp['base_currency'] = strtoupper($coin);
                    $temp['close'] = $close;
                    $temp['close_usd'] = (float)sprintf("%0.6f", $close * $this->basic_price[$coin]['usd']);
                    $temp['close_rmb'] = (float)sprintf("%0.4f", $close * $this->basic_price[$coin]['rmb']);
                    $temp['change'] = (0 == $open) ? 0 : (float)sprintf("%0.2f", ($close - $open) / $open * 100);
                    $temp['high_usd'] = (float)sprintf("%0.4f", $high * $this->basic_price[$coin]['usd']);
                    $temp['low_usd'] = (float)sprintf("%0.4f", $low * $this->basic_price[$coin]['usd']);
                    $temp['high_rmb'] = (float)sprintf("%0.4f", $high * $this->basic_price[$coin]['rmb']);
                    $temp['low_rmb'] = (float)sprintf("%0.4f", $low * $this->basic_price[$coin]['rmb']);
                    $temp['vol'] = (float)sprintf("%0.4f", $vol);
                    $temp['optional'] = false;
                    $temp['platform_zh'] = '找币';
                    $temp['platform_us'] = 'zhaobi';
                    array_push($ticker, $temp);
                }
            }
        }
        $this->code = 0;
        $data = [
            'ticker' => $ticker,
            'page' => [
                'pageSize' => 50,
                'currentPage' => 1,
            ]
        ];
        return ['code' => $this->code, 'data' => $data];
    }

    public function TickerSort()
    {
        $keys = $this->redis->smembers($this->supported_symbol);
        if (false == $this->redis->exists($this->supported_symbol_list)) {
            foreach ($keys as $val) {
                $this->redis->lpush($this->supported_symbol_list, $val);
            }
        }
        $len = $this->redis->llen($this->supported_symbol_list);
        $ticker = [];
        for ($i = 0; $i < $len; $i++) {
            $symbol = $this->redis->lindex($this->supported_symbol_list, $i);
            list($close, $open, $low, $high, $vol) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol), 'last', 'open', 'low', 'high', 'vol');
            $temp = [];
            $temp['symbol'] = strtoupper($symbol);
            $temp['close'] = number_format($close, 6, '.', '');
            foreach ($this->basic_coin as $k => $coin) {
                $explode_arr = explode(strtoupper($coin), strtoupper($symbol));
                if (2 == count($explode_arr) && empty($explode_arr[1])) {
                    $temp['close_rmb'] = (float)sprintf("%0.4f", $close * $this->basic_price[$coin]['rmb']);
                }
                if ('SFTSJPY' == strtoupper($symbol) || 'SFTCNY' == strtoupper($symbol) || 'BTYCCNY' == strtoupper($symbol) || 'USDTCCNY' == strtoupper($symbol)) {
                    $temp['close_rmb'] = (float)sprintf("%0.4f", $close * 1);
                }
            }
            $temp['change'] = (0 == $open) ? 0 : (float)sprintf("%0.2f", ($close - $open) / $open * 100);
            array_push($ticker, $temp);
            $key = $this->quotation_prefix . strtoupper($symbol);
            $this->redis_ticker->hmset($key, 'low', $low, 'high', $high, 'last', $close, 'open', $open, 'vol', $vol);
        }
        $ticker_sort_close = Tools::arraySort($ticker, 'close_rmb');
        $this->redis_ticker->del($this->supported_symbol_close_asc);
        $this->redis_ticker->del($this->supported_symbol_close_desc);
        foreach ($ticker_sort_close as $val) {
            $this->redis_ticker->lpush($this->supported_symbol_close_asc, strtoupper($val['symbol']));
            $this->redis_ticker->rpush($this->supported_symbol_close_desc, strtoupper($val['symbol']));
        }

        $ticker_change_close = Tools::arraySort($ticker, 'change');
        $this->redis_ticker->del($this->supported_symbol_change_asc);
        $this->redis_ticker->del($this->supported_symbol_change_desc);
        foreach ($ticker_change_close as $val) {
            $this->redis_ticker->lpush($this->supported_symbol_change_asc, strtoupper($val['symbol']));
            $this->redis_ticker->rpush($this->supported_symbol_change_desc, strtoupper($val['symbol']));
        }
    }

    public function getNotice($params = [])
    {
        $curl = new Curl();
        if (isset($params['id']) && !empty($params['id'])) {
            $api = $this->base_url . '/api/data/noticedetail?id=' . $params['id'];
            $res = $curl->get($api, false);
            if (isset($res['message']) && 'OK' == $res['message']) {
                $this->code = 0;
                $res['data']['abstract'] = str_replace(' ', '', str_replace('&nbsp;', '', $res['data']['abstract']));
                $res['data']['content'] = str_replace(' ', '', str_replace('&nbsp;', '', $res['data']['content']));
                $res['data']['source'] = '找币';
                $res['data']['share_url'] = \Yii::$app->params['service']['ZhaobiService']['h5_url'] . '/account/help/' . $params['id'];
                return ['code' => $this->code, 'notice' => $res['data']];
            } else {
                return ['code' => $this->code, 'notice' => $res['data'], 'msg' => $res['message']];
            }
        }
        $api = $this->base_url . '/api/data/noticelist?page=' . $params['page'] . '&size=' . $params['size'];
        $res = $curl->get($api, false);
        if (isset($res['message']) && 'OK' == $res['message']) {
            $this->code = 0;
            $notices = $res['data']['rows'];
            foreach ($notices as &$notice) {
                $notice['abstract'] = str_replace(' ', '', str_replace('&nbsp;', '', $notice['abstract']));
            }
            $data = [
                'list' => $notices,
                'count' => $res['data']['count']
            ];
        }
        return ['code' => $this->code, 'notice' => $data];
    }

    /*
     * @params $currency 交易货币
     * @params $base_currency 基础货币
     */
    public function getDetail($currency, $base_currency)
    {
        $code = 0;
        $ticker = [];
        $symbol = $currency . $base_currency;
        list($low, $high, $close, $open) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol), 'low', 'high', 'last', 'open');
        if (empty($low) && empty($high) && empty($close) && empty($open)) {
            if ($this->basic_coin[2] == $currency) {    //交易货币为USDT
                $symbol = $base_currency . $this->basic_coin[2];
                list($low, $high, $close, $open) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol), 'low', 'high', 'last', 'open');
                if (!empty($low) && !empty($high) && !empty($close) && !empty($open)) {
                    $low = number_format(1 / $low, 6, '.', '');
                    $high = number_format(1 / $high, 6, '.', '');
                    $open = number_format(1 / $open, 6, '.', '');
                    $close = number_format(1 / $close, 6, '.', '');
                    $ticker = [
                        'low' => $low,
                        'high' => $high,
                        'open' => $open,
                        'close' => $close
                    ];
                    goto doEnd;
                }
            }

            if ('CCNY' == $base_currency) {    //基础货币为CCNY
                $symbol_currency = $currency . $this->basic_coin[2];
                $symbol_base_currency = $this->basic_coin[2] . $base_currency;
                list($low_currency, $high_currency, $close_currency, $open_currency) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol_currency), 'low', 'high', 'last', 'open');
                if (empty($low_currency) && empty($high_currency) && empty($close_currency) && empty($open_currency)) {
                    $code = $this->code;
                    goto doEnd;
                }
                list($low_base_currency, $high_base_currency, $close_base_currency, $open_base_currency) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol_base_currency), 'low', 'high', 'last', 'open');
                if (empty($low_base_currency) && empty($high_base_currency) && empty($close_base_currency) && empty($open_base_currency)) {
                    $code = $this->code;
                    goto doEnd;
                }
                $low = number_format($low_currency * $low_base_currency, 6, '.', '');
                $high = number_format($high_currency * $high_base_currency, 6, '.', '');
                $close = number_format($close_currency * $close_base_currency, 6, '.', '');
                $open = number_format($open_currency * $open_base_currency, 6, '.', '');
                $ticker = [
                    'low' => $low,
                    'high' => $high,
                    'open' => $open,
                    'close' => $close
                ];
                goto doEnd;
            }

            if ('CCNY' == $currency) {    //交易货币为CCNY
                $symbol_currency = $base_currency . $this->basic_coin[2];
                $symbol_base_currency = $this->basic_coin[2] . $currency;
                if ($currency . $base_currency == 'CCNYUSDT') {
                    list($low, $high, $close, $open) = $this->redis->hmget($this->quotation_prefix . strtolower($base_currency . $currency), 'low', 'high', 'last', 'open');
                    if (empty($low) && empty($high) && empty($close) && empty($open)) {
                        $code = $this->code;
                        goto doEnd;
                    }
                    $low = number_format(1 / $low, 6, '.', '');
                    $high = number_format(1 / $high, 6, '.', '');
                    $close = number_format(1 / $close, 6, '.', '');
                    $open = number_format(1 / $open, 6, '.', '');
                    $ticker = [
                        'low' => $low,
                        'high' => $high,
                        'open' => $open,
                        'close' => $close
                    ];
                    goto doEnd;
                }
                list($low_currency, $high_currency, $close_currency, $open_currency) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol_currency), 'low', 'high', 'last', 'open');
                if (empty($low_currency) && empty($high_currency) && empty($close_currency) && empty($open_currency)) {
                    $code = $this->code;
                    goto doEnd;
                }
                list($low_base_currency, $high_base_currency, $close_base_currency, $open_base_currency) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol_base_currency), 'low', 'high', 'last', 'open');
                if (empty($low_base_currency) && empty($high_base_currency) && empty($close_base_currency) && empty($open_base_currency)) {
                    $code = $this->code;
                    goto doEnd;
                }
                $low = number_format(1 / ($low_currency * $low_base_currency), 6, '.', '');
                $high = number_format(1 / ($high_currency * $high_base_currency), 6, '.', '');
                $close = number_format(1 / ($close_currency * $close_base_currency), 6, '.', '');
                $open = number_format(1 / ($open_currency * $open_base_currency), 6, '.', '');
                $ticker = [
                    'low' => $low,
                    'high' => $high,
                    'open' => $open,
                    'close' => $close
                ];
                goto doEnd;
            }

            doEnd :
            return ['code' => $code, 'ticker' => $ticker];
        }
        $ticker = [
            'low' => $low,
            'open' => $open,
            'high' => $high,
            'close' => $close
        ];
        return ['code' => 0, 'ticker' => $ticker];
    }

    public function getHotTicker($symbol = [])
    {
        if (empty($symbol)) {
            return ['code' => $this->code, 'ticker' => []];
        }

        $ticker = [];
        foreach ($symbol as $val) {
            list($low, $high, $close, $open, $vol) = $this->redis->hmget($this->quotation_prefix . strtolower($val), 'low', 'high', 'last', 'open', 'vol');
            $explode_arr = explode('usdt', $val);
            $temp = [];
            $temp['symbol'] = strtoupper($explode_arr[0]) . '/USDT';
            $temp['currency'] = strtoupper($explode_arr[0]);
            $temp['base_currency'] = 'USDT';
            $temp['close'] = (float)sprintf("%0.6f", $close);
            $temp['close_usd'] = (float)sprintf("%0.6f", $close * $this->basic_price['USDT']['usd']);
            $temp['close_rmb'] = (float)sprintf("%0.4f", $close * $this->basic_price['USDT']['rmb']);
            $temp['change'] = (0 == $open) ? 0 : (float)sprintf("%0.2f", ($close - $open) / $open * 100);
            $temp['high_usd'] = (float)sprintf("%0.4f", $high * $this->basic_price['USDT']['usd']);
            $temp['low_usd'] = (float)sprintf("%0.4f", $low * $this->basic_price['USDT']['usd']);
            $temp['high_rmb'] = (float)sprintf("%0.4f", $high * $this->basic_price['USDT']['rmb']);
            $temp['low_rmb'] = (float)sprintf("%0.4f", $low * $this->basic_price['USDT']['rmb']);
            $temp['vol'] = (float)sprintf("%0.4f", $vol);
            array_push($ticker, $temp);
        }
        if (count($ticker) > 0) {
            $this->code = 0;
        }
        return ['code' => $this->code, 'ticker' => $ticker];
    }
}
