<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinClientParams extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_client_params}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules() {
        return [
            [['type','url'], 'required'],
        ];
    }

    public function scenarios() {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['type','url'],
        ];
        return array_merge( parent:: scenarios(), $scenarios);
    }
}
