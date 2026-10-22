<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class AirDropRefund extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%wallet_airdrop_refund}}';
    }
}