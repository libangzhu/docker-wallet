<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class WalletRecommendCoin extends BaseActiveRecord
{
    const TYPE_COMMON = 1; //去中心化
    const TYPE_TRUSTEESHIP = 2; //托管钱包

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{wallet_recommend_coin}}';
    }

    public function getCoin()
    {
        return $this->hasOne(Coin::className(), ['id' => 'cid'])->select(['id', 'icon', 'name', 'introduce', 'nickname', 'platform', 'chain', 'treaty'])->asArray();
    }
}