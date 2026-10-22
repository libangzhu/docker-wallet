<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 上午11:46
 */

namespace common\models\psources;

/**
 * Class CoinMidGuess
 *
 * @property int    $id
 * @property int    $aid
 * @property string $device_id
 * @property string $address
 * @property string $txhash
 * @package common\models\psources
 */
class CoinMidGuess extends BaseActiveRecord
{
    public static function tableName()
    {
        return 'coin_mid_guess';
    }

    public static function isExists($device_id, $address)
    {
        return self::findOne(['device_id' => $device_id, 'address' => $address]);
    }

    /**
     * 获取记录条数
     *
     * @param string $device_id
     * @param string $address
     * @return int|string
     */
    public static function existsCount($device_id, $address)
    {
        $address_count = self::find()->where(['address' => $address])->count();
        $driveid_count = self::find()->where(['device_id' => $device_id])->count();
        return $address_count > $driveid_count ? $address_count : $driveid_count;
    }
}