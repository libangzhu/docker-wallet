<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinCurrency extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{coin_currency}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules()
    {
        return [
            [['pj_id', 'pj_name'], 'required'],
            [['pj_symbol', 'lang'], 'safe'],
            [['platform_id', 'currency_id'], 'integer'],
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
            self:: SCENARIOS_CREATE => ['pj_id', 'pj_name', 'pj_symbol', 'lang'],
            self:: SCENARIOS_UPDATE => ['id', 'pj_id', 'pj_name', 'pj_symbol', 'lang'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }
}