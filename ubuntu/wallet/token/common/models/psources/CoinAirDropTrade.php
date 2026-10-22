<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinAirDropTrade extends BaseActiveRecord
{
    const TYPE_GAME = 1;

    const AMOUNT_GAME = 0.0001;

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_airdrop_trade}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules() {
        return [
            [['coins_address','msg', 'amount', 'type', 'attach', 'balance'], 'required'],
        ];
    }

    public function scenarios() {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['coins_address','msg', 'amount', 'type', 'attach', 'balance'],
        ];
        return array_merge( parent:: scenarios(), $scenarios);
    }
}