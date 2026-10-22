<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 下午2:59
 */

namespace backend\models\coin;

use common\models\psources\MinerFee;
use yii\base\Model;

class MinerFeeForm extends Model
{
    public $id;
    public $platform;
    public $max;
    public $min;
    public $level;
    public $type;
    public $fee;

    //定义场景
    const SCENARIOS_WALLET_CREATE = 'wallet_create';
    const SCENARIOS_WALLET_UPDATE = 'wallet_update';
    const SCENARIOS_TRUSTEESHIP_CREATE = 'trusteeship_create';
    const SCENARIOS_TRUSTEESHIP_UPDATE = 'trusteeship_update';

    const TYPE_WALLET = 1;  //币钱包
    const TYPE_TRUSTEESHIP = 2; //托管钱包


    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['platform', 'min', 'max', 'level', 'fee'], 'required'],
            [['id', 'max', 'min', 'level'], 'number'],
            ['platform', 'isExist','on' => [self:: SCENARIOS_TRUSTEESHIP_CREATE]]
        ];
    }

    public function isExist($attribute, $params)
    {
        $count = MinerFee::find()->where(['platform' => $this->platform, 'type' => self::TYPE_TRUSTEESHIP])->count();
        if($count > 0) {
            $this->addError('platform', '该平台已存在');
        }
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_WALLET_CREATE => ['platform', 'min', 'max', 'level'],
            self:: SCENARIOS_WALLET_UPDATE => ['platform', 'min', 'max', 'level'],
            self:: SCENARIOS_TRUSTEESHIP_CREATE => ['platform', 'type', 'fee'],
            self:: SCENARIOS_TRUSTEESHIP_UPDATE => ['platform', 'type', 'fee'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'platform' => '平台',
            'min' => '最小值',
            'max' => '最大值',
            'level' => '分档',
            'fee' => '手续费'
        ];
    }
}