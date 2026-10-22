<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 上午11:44
 */

namespace common\models\psources;

/**
 * Class CoinActivity
 *
 * @property int    $id
 * @property string $code
 * @property string $name
 * @property string $end_time
 * @package common\models\psources
 */
class CoinActivity extends BaseActiveRecord
{
    public static function tableName()
    {
        return 'coin_activity';
    }

    public static function isValiable($code)
    {
        $activity = self::findOne(['code' => strtoupper($code)]);
        if ($activity) {
            $time = time();
            $end  = strtotime($activity->end_time);
            if ($end > $time) {
                return $activity;
            }
        }
        return false;
    }

    public static function isExists($code)
    {
        return self::findOne(['code' => $code]);
    }
}