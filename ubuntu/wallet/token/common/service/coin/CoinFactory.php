<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-19
 * Time: 下午1:56
 */

namespace common\service\coin;


class CoinFactory
{
    public static function createCoin($coinName = '', $id, $sid)
    {
        $coinName  = strtoupper($coinName);
        $className = __NAMESPACE__ . '\Coin' . $coinName . 'Service';
        if (class_exists($className)) {
            return new $className($id, $sid);
        } else {
            return new CoinService($id, $sid);
        }
    }
}