<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class WalletCoin extends BaseActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{coin}}';
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['quotation']);
    }
}