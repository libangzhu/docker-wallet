<?php

namespace backend\models\coin;

use common\models\psources\Coin;
use yii\base\Model;

class CoinSupportedSymbolForm extends Model
{
    public $id;
    public $platform_id;
    public $currency;
    public $base_currency;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['platform_id', 'currency', 'base_currency'], 'required', 'on' => 'add'],
            [['id', 'platform_id', 'currency', 'base_currency'], 'required', 'on' => 'update'],
            [['currency', 'base_currency'], 'isExist']
        ];
    }

    public function scenarios()
    {
        return [
            'add' => ['platform_id', 'currency', 'base_currency'],
            'update' => ['id', 'platform_id', 'currency', 'base_currency'],
        ];
    }

    public function isExist($attribute, $params)
    {
        $coin_model = Coin::find()->where(['name' => $this->currency])->one();
        if (false == $coin_model) {
            $this->addError($attribute, '币种不存在');
            return false;
        }
    }
}