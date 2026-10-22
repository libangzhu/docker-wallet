<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class AirDropRule extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%wallet_airdrop_rule}}';
    }

    public function rules()
    {
        return [
            [['token', 'amount', 'duration'], 'required'],
            [['amount', 'duration'], 'integer'],
            [['token'], 'string', 'length' => [2, 10]],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['token', 'amount', 'duration'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'token' => '空投币种单位',
            'amount' => '每次空投数量',
            'duration' => '空投周期'
        ];
    }

    public function getRecord()
    {
        return $this->hasMany(AirDropRulePool::className(), ['rule_id' => 'id'])->all();
    }
}