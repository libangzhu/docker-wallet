<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinAddress extends BaseActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_address}}';
    }

    public function rules()
    {
        return [
            [['address', 'mobile', 'mail'], 'required'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['address', 'mobile', 'mail'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    /**
     * 导入数据
     *
     * @param  array $datas
     * @return boolean
     */
    public static function batchImport(array $datas)
    {
        if (!is_array($datas)) {
            return false;
        }
        try {
            $result = self::getDb()->createCommand()->batchInsert(self::tableName(), ['address', 'mobile', 'mail'], $datas)->execute();
            return $result;
        } catch (\Exception $e) {

        }
        return false;
    }
}
