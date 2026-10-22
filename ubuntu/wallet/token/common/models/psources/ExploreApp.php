<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;
use common\core\BaseActiveRecord;
use yii\behaviors\TimestampBehavior;

class ExploreApp extends BaseActiveRecord
{
    const STATUS_ON = 1;    //激活
    const STATUS_OFF = 0;    //未激活

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{explore_app}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules()
    {
        return [
            [['name', 'icon', 'app_url', 'type', 'sort', 'status', 'platform_id', 'app_category_id', 'email', 'phone', 'real_name'], 'required'],
            [['type', 'sort', 'status', 'platform_id', 'app_category_id', 'email', 'phone', 'real_name'], 'integer'],
            [['email', 'phone', 'real_name'], 'in', 'range' => array(0, 1)],
            ['slogan', 'string', 'max' => 50],
            [['slogan'], 'safe'],
            [['icon'], 'url']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '应用名称',
            'icon' => '应用图标',
            'app_url' => 'H5链接',
            'slogan' => '应用宣传语',
            'type' => '应用类型',
            'sort' => '应用排序',
            'status' => '应用状态',
            'phone' => '准入条件 - 手机',
            'email' => '准入条件 - 邮箱',
            'real_name' => '准入条件 - 实名'
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['name', 'icon', 'app_url', 'slogan', 'type', 'sort', 'status', 'platform_id', 'app_category_id', 'email', 'phone', 'real_name'],
            self:: SCENARIOS_UPDATE => ['id', 'name', 'icon', 'app_url', 'slogan', 'type', 'sort', 'status', 'platform_id', 'app_category_id', 'email', 'phone', 'real_name'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function getAppCategory()
    {
        return $this->hasOne(ExploreAppCategory::className(), ['id' => 'app_category_id']);
    }

    public static function getCasesStatus()
    {
        return [
            self::STATUS_ON => '激活',
            self::STATUS_OFF => '未激活',
        ];
    }
}