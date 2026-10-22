<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinHashMemo extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_hash_memo}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules() {
        return [
            [['hash'], 'required'],
            [['coins_fee','memo'],'safe']
        ];
    }

    public function scenarios() {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['hash','memo', 'coins_fee'],
        ];
        return array_merge( parent:: scenarios(), $scenarios);
    }
}