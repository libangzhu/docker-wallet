<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class AirDrop extends CommonActiveRecord
{
    const REACH_INIT_NO = 0;    //未达标
    const REACH_INIT_YES = 1;   //已达标

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%wallet_airdrop_apply}}';
    }

    public function rules()
    {
        return [
            [['identifier', 'wallet_address', 'miner_address'], 'required'],
            [['reach_times', 'reach_init'], 'integer'],
            [['reach_times', 'reach_init', 'create_time', 'finish_time'], 'safe'],
            [['identifier'], 'string', 'length' => [5, 50]],
            [['wallet_address', 'miner_address'], 'string', 'length' => [10, 50]],
            ['identifier', 'unique', 'message' => '树莓派编号已存在'],
            ['miner_address', 'unique', 'message' => '矿工地址已存在'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['identifier', 'miner_address', 'reach_init', 'reach_times', 'create_time'],
            self:: SCENARIOS_UPDATE => ['wallet_address'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'identifier' => '树莓派编号',
            'wallet_address' => '矿主地址',
            'miner_address' => '矿工地址',
            'reach_init' => '达标初始化',
            'reach_times' => '达标次数',
            'create_time' => '开始时间',
            'finish_time' => '结束时间'
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['reach', 'draw_success', 'income', 'un_draw']);
    }

    public function getRecord()
    {
        return $this->hasMany(AirDropApplyRecord::className(), ['apply_id' => 'id'])->all();
    }
}