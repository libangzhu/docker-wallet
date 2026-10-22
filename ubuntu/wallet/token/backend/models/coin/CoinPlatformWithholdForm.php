<?php

namespace backend\models\coin;

use yii\base\Model;

class CoinPlatformWithholdForm extends Model
{
    public $id;
    public $platform;
    public $address;
    public $private_key;
    public $exer;
    public $fee;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['platform', 'address', 'private_key', 'exer', 'fee'], 'required', 'on' => 'add'],
            [['id', 'platform', 'address', 'private_key', 'exer', 'fee'], 'required', 'on' => 'update'],
        ];
    }

    public function scenarios()
    {
        return [
            'add' => ['platform', 'address', 'private_key', 'exer', 'fee'],
            'update' => ['id', 'platform', 'address', 'private_key', 'exer', 'fee'],
        ];
    }


}