<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class AirDropApplyTransferRecord extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%wallet_airdrop_apply_transfer_record}}';
    }

    public function rules()
    {
        return [
            [['apply_record_id', 'create_result', 'sign_result', 'send_result'], 'safe'],
            [['apply_record_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['apply_record_id', 'create_result', 'sign_result', 'send_result'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'apply_record_id' => '申请记录id',
            'create_result' => '构造失败原因',
            'sign_result' => '签名失败原因',
            'send_result' => '发送失败原因',
            'create_time' => '创建时间',
        ];
    }

    public function getRecord()
    {
        return $this->hasOne(AirDropApplyRecord::className(), ['id' => 'apply_record_id'])->all();
    }
}