<?php

namespace common\models\psources;

use Yii;

class CoinIssueRecord extends CommonActiveRecord
{

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_issue_record}}';
    }

    public function rules()
    {
        return [
            [['platform_id', 'total'], 'required'],
            [['total', 'platform_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['platform_id', 'total'],
            self:: SCENARIOS_UPDATE => ['platform_id', 'total'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

}