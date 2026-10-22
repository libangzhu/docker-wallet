<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class WalletRecommendCategory extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{wallet_recommend_category}}';
    }

    public function getRecommendCoin()
    {
        return $this->hasMany(WalletRecommendCoin::className(), ['category_id' => 'id'])->asArray()->all();
    }
}