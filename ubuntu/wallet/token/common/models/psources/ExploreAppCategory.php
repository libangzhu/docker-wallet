<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class ExploreAppCategory extends BaseActiveRecord
{
    const STATUS_ON = 1;    //激活
    const STATUS_OFF = 0;    //未激活

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{explore_app_category}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules()
    {
        return [
            [['name', 'sort', 'limit', 'style', 'platform_id', 'status'], 'required'],
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['apps']);
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['name', 'sort', 'limit', 'style', 'platform_id', 'status'],
            self:: SCENARIOS_UPDATE => ['name', 'sort', 'limit', 'style', 'status'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'name' => '应用分类名称',
            'sort' => '应用分类排序',
            'limit' => '该分类下首页可显示的应用数量',
            'style' => '应用分类展示样式',
            'status' => '应用分类状态'
        ];
    }

    public function getApplications()
    {
        return $this->hasMany(ExploreApp::className(), ['app_category_id' => 'id'])->select(['id', 'name', 'icon', 'app_url', 'slogan', 'type'])->where(['status' => ExploreApp::STATUS_ON])->orderBy('sort');
    }

}