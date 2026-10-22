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

class ZtbBuilder extends FactoryService
{
    protected $base_url = 'https://api.ztb.pro';
    protected $supported_symbol = 'supported_symbol_ztb';
    protected $supported_symbol_list = 'supported_symbol_ztb_list';
    protected $supported_symbol_close_asc = 'supported_symbol_close_asc_ztb';
    protected $supported_symbol_close_desc = 'supported_symbol_close_desc_ztb';
    protected $supported_symbol_change_asc = 'supported_symbol_change_asc_ztb';
    protected $supported_symbol_change_desc = 'supported_symbol_change_desc_ztb';
    protected $quotation_prefix = 'quotation_ztb_';

    public function getHotTicker($symbol = [])
    {
        if (empty($symbol)) {
            return ['code' => $this->code, 'ticker' => []];
        }

        $ticker = [];
        foreach ($symbol as $val) {
            list($low, $high, $change, $last, $vol) = $this->redis->hmget($this->quotation_prefix . strtoupper(str_replace('USDT', '_USDT', $val)), 'low', 'high', 'change', 'last', 'vol');
            $explode_arr = explode('usdt', $val);
            $temp = [];
            $temp['symbol'] = strtoupper($explode_arr[0]) . '/USDT';
            $temp['currency'] = strtoupper($explode_arr[0]);
            $temp['base_currency'] = 'USDT';
            $temp['close'] = (float)sprintf("%0.6f", $last);
            $temp['close_usd'] = (float)sprintf("%0.6f", $last * $this->basic_price['USDT']['usd']);
            $temp['close_rmb'] = (float)sprintf("%0.4f", $last * $this->basic_price['USDT']['rmb']);
            $temp['change'] = (float)sprintf("%0.2f", $change);;
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
