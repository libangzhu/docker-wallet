<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class WalletCoinRelation extends BaseActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{wallet_coin_relation}}';
    }

    public function getCoin()
    {
        return $this->hasOne(WalletCoin::className(), ['id' => 'coin_id']);
    }

}