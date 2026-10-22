<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-20
 * Time: 下午3:28
 */

namespace common\models\pwallet;

use Yii;
use common\core\BaseActiveRecord;

/**
 * Class InvestmentType
 * @property integer $id
 * @property string $name
 * @property string $create_at
 * @package common\models\pwallet
 */
class InvestmentType extends BaseActiveRecord
{
    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    public static function isExists($name)
    {
        return (bool)self::findOne(['name' => $name]);
    }
}