<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinPlatformBanner extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{coin_platform_banner}}';
    }

    public static function getBanner($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public static function getItems($condition = [])
    {
        return self::find()->joinWith('platform')->where($condition)->asArray()->all();
    }

    public function getPlatform()
    {
        return $this->hasOne(CoinPlatform::className(), ['id'=>'platform_id']);
    }
}