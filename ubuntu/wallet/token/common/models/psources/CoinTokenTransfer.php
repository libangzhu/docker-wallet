<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinTokenTransfer extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_token_transfer}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules() {
        return [
            [['coin_address','msg', 'code'], 'required'],
        ];
    }

    public function scenarios() {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['coin_address','msg', 'code'],
        ];
        return array_merge( parent:: scenarios(), $scenarios);
    }
}