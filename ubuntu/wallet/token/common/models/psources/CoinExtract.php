<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 15:18:43
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

/**
 * 用户提币操作记录表
 * @property integer $id
 * @property string $address
 * @property integer $today
 * @property integer $total
 * @property string $create_time
 * @property string $update_time
 */
class CoinExtract extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_extract}}';
    }

    public function formName()
    {
        return '';
    }

    public static function getAllByAddress($addresses)
    {
        if (!is_array($addresses)) {
            $addresses = [$addresses];
        }
        $query = self::find()->where(['in', 'address', $addresses])->asArray()->all();
        return array_column($query, null, 'address');
    }
}
