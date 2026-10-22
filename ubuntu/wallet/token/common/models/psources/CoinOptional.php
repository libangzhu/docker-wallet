<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinOptional extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_optional}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';


    public function rules()
    {
        return [
            [['symbol', 'platform', 'device_code', 'platform_id'], 'required'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['symbol', 'platform', 'device_code', 'platform_id'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }
}
