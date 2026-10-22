<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-26
 * Time: 下午6:07
 */

namespace common\models\psources;

/**
 * Class CoinReleaseList
 * 国顿账单记录表
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $mid
 * @property string  $mobile
 * @property integer $amount
 * @property string  $coin
 * @property integer $type
 * @property integer $from
 * @property integer $to
 * @property integer $status
 *
 * @package common\models\psources
 */
class CoinReleaseList extends BaseActiveRecord
{
    public static function tableName()
    {
        return 'coin_release_list';
    }
}