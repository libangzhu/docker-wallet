<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class AirDropRulePool extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%wallet_airdrop_rule_pool}}';
    }

    public function rules()
    {
        return [
            [['rule_id', 'identifier'], 'required'],
            [['identifier'], 'string', 'length' => [5, 50]],
            ['identifier', 'unique', 'message' => '树莓派编号已存在'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['rule_id', 'identifier'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'identifier' => '树莓派编号',
            'rule_id' => '所属规则'
        ];
    }

    public function getRule()
    {
        return $this->hasOne(AirDropRule::className(), ['id' => 'rule_id'])->one();
    }
}