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

class DagBuilder extends FactoryService
{
    protected $supported_symbol = 'supported_symbol_dag';
    protected $quotation_prefix = 'quotation_dag_';

    public function getHotTicker($symbol = [])
    {
        if (empty($symbol)) {
            return ['code' => $this->code, 'ticker' => []];
        }

        $ticker = [];
        foreach ($symbol as $val) {
            list($low, $high, $close, $open, $vol) = $this->redis->hmget($this->quotation_prefix . strtoupper($val), 'low', 'high', 'last', 'open', 'vol');
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
