<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class AirDropApplyRecord extends CommonActiveRecord
{
    const REACH_NO = 0;    //未达标
    const REACH_YES = 1;    //已达标

    const STATUS_UNDRAW = 0;    //未领取
    const STATUS_DRAWING = 1;    // 领取中
    const STATUS_DRAW_SUEEESS = 2;    //领取成功
    const STATUS_DRAW_FAIL = 3;    // 领取失败

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%wallet_airdrop_apply_record}}';
    }

    public function rules()
    {
        return [

        ];
    }

    public function scenarios()
    {
        $scenarios = [

        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'apply_id' => '申请ID',
            'reach' => '达标情况',
            'bonus_token' => '奖励单位',
            'draw_status' => '领取情况',
            'create_time' => '创建时间',
        ];
    }

    public function getApply()
    {
        return $this->hasOne(AirDrop::className(), ['id' => 'apply_id'])->one();
    }
}