<?php

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinDailyStatistics extends BaseActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_daily_statistics}}';
    }

}
