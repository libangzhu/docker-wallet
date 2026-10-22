<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 16:48:22
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

class CoinWithhold extends BaseActiveRecord
{
    public static function isExists($name, $platform_id)
    {
        $coin = Coin::findOne(['name' => $name]);
        if ($coin) {
            return (bool) self::findOne(['cid' => $coin->id, 'platform_id' => $platform_id]);
        }
        return false;
    }

    public static function saveByName($name, $status = 1, $token_name = '', $address = '', $platform_id = 1)
    {
        $coin = Coin::findOne(['name' => $name]);
        if ($coin) {
            $withhold              = new self();
            $withhold->cid         = $coin->id;
            $withhold->status      = $status;
            $withhold->token_name  = $token_name;
            $withhold->address     = $address;
            $withhold->platform_id = $platform_id;
            return $withhold->save();
        }
        throw new \Exception('币种不存在', -1);
    }
}
