<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/18
 * Time: 16:56
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinBannerItem extends BaseActiveRecord
{
    const STATUS_ON = 1;    //激活
    const STATUS_OFF = 0;    //未激活

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{coin_banner_item}}';
    }

    public function rules()
    {
        return [
            [['image_url', 'platform_id', 'sort', 'status', 'type'], 'required'],
            [['platform_id', 'sort', 'type', 'status', 'type'], 'integer'],
            ['banner_url', 'default', 'value' => '#'],
            [['banner_url', 'title'], 'safe'],
            [['image_url'], 'url'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'banner_url' => 'banner跳转链接',
            'image_url' => 'banner图片链接',
            'title' => 'banner标题',
            'platform_id' => '所属平台',
            'sort' => 'banner排序',
            'status' => 'banner状态',
            'type' => 'banner类型'
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['banner_url', 'image_url', 'title', 'platform_id', 'sort', 'status', 'type'],
            self:: SCENARIOS_UPDATE => ['id', 'banner_url', 'image_url', 'title', 'platform_id', 'sort', 'status', 'type'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }


    public static function getItems($condition = [])
    {
        return self::find()->joinWith('platform')->where($condition)->asArray()->all();
    }

    public static function getBanner($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public function getPlatform()
    {
        return $this->hasOne(CoinPlatform::className(), ['id' => 'platform_id']);
    }
}