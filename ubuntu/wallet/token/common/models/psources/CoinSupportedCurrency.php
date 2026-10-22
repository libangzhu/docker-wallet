<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinSupportedCurrency extends BaseActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{coin_supported_currency}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules()
    {
        return [
            [['platform_id', 'currency_id'], 'required'],
            [['platform_id', 'currency_id', 'sort'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'platform_id' => '钱包Id',
            'currency_id' => '货币Id',
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['platform_id', 'currency_id', 'sort'],
            self:: SCENARIOS_UPDATE => ['id', 'platform_id', 'currency_id', 'sort'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['pj_id', 'pj_name', 'pj_symbol', 'rate']);
    }

    public function getCurrency()
    {
        return $this->hasOne(CoinCurrency::className(), ['id' => 'currency_id']);
    }

    public static function loadArray(array $data)
    {
        return self::getDb()->createCommand()->batchInsert(self::tableName(),
            ['platform_id', 'currency_id'],
            $data)->execute();
    }
}