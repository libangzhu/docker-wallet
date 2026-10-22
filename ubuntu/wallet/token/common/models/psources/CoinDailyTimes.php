<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinDailyTimes extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_daily_times}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules() {
        return [
            [['dapp_name','dapp_address', 'coins_address', 'platform_id'], 'required'],
            [['platform_id'],'match','pattern'=>'/^[0-9]+$/','message' => '{attribute} 必须为数字'],
        ];
    }

    public function scenarios() {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['dapp_name','dapp_address', 'coins_address', 'platform_id'],
        ];
        return array_merge( parent:: scenarios(), $scenarios);
    }
}
