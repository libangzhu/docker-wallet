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

class JinwangBuilder extends FactoryService
{
    protected $base_url = 'http://www.jinwangin.com';
    protected $supported_symbol = 'supported_symbol_jinwang';
    protected $supported_symbol_list = 'supported_symbol_jinwang_list';
    protected $supported_symbol_close_asc = 'supported_symbol_close_asc_jinwang';
    protected $supported_symbol_close_desc = 'supported_symbol_close_desc_jinwang';
    protected $supported_symbol_change_asc = 'supported_symbol_change_asc_jinwang';
    protected $supported_symbol_change_desc = 'supported_symbol_change_desc_jinwang';
    protected $quotation_prefix = 'quotation_jinwang_';

    public function getTickerFromCache($page = 1, $condition = [])
    {
        $size = 0;
        for ($i = 0; $i < $page; $i++) {
            $size += 50;
            $this->end = $size;
        }
        $this->start = $this->end - 50;
        $this->end = $this->end - 1;
        $keys = $this->redis->smembers($this->supported_symbol);
        if (false == $this->redis->exists($this->supported_symbol_list)) {
            foreach ($keys as $val) {
                if (in_array(strtoupper($val), ['LTC_USDT', 'BTC_USDT', 'EOS_USDT', 'TG_USDT', 'TPC_USDT', 'ETH_USDT', 'GLCW_USDT'])) {
                    $this->redis->lpush($this->supported_symbol_list, strtoupper($val));
                }
            }
        }
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
                $explode_arr = explode(strtoupper($coin), strtoupper($val));
                if (2 == count($explode_arr) && empty($explode_arr[1])) {
                    if (false != $condition) {
                        list($low, $high, $close, $open, $vol) = $this->redis_ticker->hmget($this->quotation_prefix . strtoupper($val), 'low', 'high', 'last', 'open', 'vol');
                    } else {
                        list($low, $high, $close, $open, $vol) = $this->redis->hmget($this->quotation_prefix . strtolower($val), 'low', 'high', 'last', 'open', 'vol');
                    }
                    $temp = [];
                    $temp['currency'] = strtoupper(str_replace('_', '', $explode_arr[0]));
                    $temp['symbol'] = $temp['currency'] . '/' . $coin;
                    $temp['base_currency'] = strtoupper($coin);
                    $temp['close'] = rtrim(sprintf('%.8f', floatval($close)), '0');
                    $temp['close_usd'] = rtrim(sprintf('%.6f', floatval($close * $this->basic_price[$coin]['usd'])), '0');
                    $temp['close_rmb'] = (float)sprintf("%0.4f", $close * $this->basic_price[$coin]['rmb']);
                    $temp['change'] = (false == $open) ? 0 : (float)sprintf("%0.2f", ($close - $open) / $open * 100);
                    $temp['high_usd'] = (float)sprintf("%0.4f", $high * $this->basic_price[$coin]['usd']);
                    $temp['low_usd'] = (float)sprintf("%0.4f", $low * $this->basic_price[$coin]['usd']);
                    $temp['high_rmb'] = (float)sprintf("%0.4f", $high * $this->basic_price[$coin]['rmb']);
                    $temp['low_rmb'] = (float)sprintf("%0.4f", $low * $this->basic_price[$coin]['rmb']);
                    $temp['vol'] = (float)sprintf("%0.4f", $vol);
                    $temp['optional'] = false;
                    $temp['platform_zh'] = '金网国际';
                    $temp['platform_us'] = 'Diamond coin';
                    array_push($ticker, $temp);
                }
            }
        }
        $this->code = 0;
        $data = [
            'ticker' => $ticker,
            'page' => [
                'pageSize' => 50,
                'currentPage' => (int)$page,
            ]
        ];
        return ['code' => $this->code, 'data' => $data];
    }

    public function TickerSort()
    {
        $len = $this->redis->llen($this->supported_symbol_list);
        $ticker = [];
        for ($i = 0; $i < $len; $i++) {
            $symbol = $this->redis->lindex($this->supported_symbol_list, $i);
            list($close, $open, $low, $high, $vol) = $this->redis->hmget($this->quotation_prefix . strtolower($symbol), 'last', 'open', 'low', 'high', 'vol');
            if ('0.00000000' == $close) continue;
            $temp = [];
            $temp['symbol'] = strtoupper($symbol);
            $temp['close'] = $close;
            foreach ($this->basic_coin as $k => $coin) {
                $explode_arr = explode(strtoupper($coin), strtoupper($symbol));
                if (2 == count($explode_arr) && empty($explode_arr[1])) {
                    $temp['close_rmb'] = (float)sprintf("%0.4f", $close * $this->basic_price[$coin]['rmb']);
                }
            }
            $temp['low'] = $low;
            $temp['high'] = $high;
            $temp['open'] = $open;
            $temp['vol'] = $vol;
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
