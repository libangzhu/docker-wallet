<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/15
 * Time: 20:41
 */
namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinApplication extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_application}}';
    }

    public function beforeSave($instert)
    {
        if (parent::beforeSave($instert)) {
            if ($this->isNewRecord) {
                $this->create_time = Yii::$app->formatter->asTimestamp('now');
            } else {
                $this->update_time = Yii::$app->formatter->asTimestamp('now');
            }
            return true;
        } else {
            return false;
        }
    }

    public function getAppCate()
    {
        return $this->hasOne(CoinAppCate::class, ['app_id' => 'id']);
    }

    public static function getList($page = 1, $limit = 10, $condition = [], $order_by = ['sort' => SORT_ASC], $select = [])
    {
        $query = self::find();
        foreach($condition as $item){
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit)->orderby($order_by);
        $data = $data->asArray()->all();
        $icon_Items = array_column($data,'icon');
        $icon_Infos = CoinImage::getItemsByIds($icon_Items);
        $recommendItems = CoinApplicateRecommend::getRecommendItemsByType(2);
        foreach($data as $key => &$value){
            if($value['icon']){
                $value['icon_url'] = Yii::$app->params['service']['OssService']['url'].$icon_Infos[$value['icon']]['file_url'];
            }else{
                $value['icon_url'] = '';
            }
            if($value['h5_url']){
                $value['has_h5'] = 1;
            }else{
                $value['has_h5'] = 0;
            }
            if($value['android_url']){
                $value['has_android'] = 1;
            }else{
                $value['has_android'] = 0;
            }
            $has_ios_url = $value['ios_url']?1:0;
            $has_app_store = $value['app_store_url']?1:0;
            if($has_ios_url && $has_app_store){
                $value['has_ios'] = 3;
            }else if($has_ios_url){
                $value['has_ios'] = 1;
            }else if($has_app_store){
                $value['has_ios'] = 2;
            }else{
                $value['has_ios'] = 0;
            }
            if(isset($recommendItems[$value['id']])){
                $value['isrecommend'] = 1;
            }
        }
        return ['count' => $count, 'data' => $data, 'code' => 0];
    }

    public static function getListByCategory($page = 1, $limit = 10, $category_id, $order_by = ['sort' => SORT_ASC])
    {
        $coin_applicate_Model = CoinApplication::find();
        $query = $coin_applicate_Model->JoinWith(['appCate'], false)
            ->select(CoinApplication::tableName().'.id,name,icon,type,native_url,h5_url,android_url,ios_url,app_store_url,advertise,description,enable,redirect_type,'.CoinAppCate::tableName().'.sort as sort')
            ->orderBy(CoinAppCate::tableName().'.sort asc')->where(['cate_id' => $category_id]);
        $count = $query->count();
        $data = $query->offset(($page - 1) * 10)->limit($limit)->asArray()->all();
        $icon_Items = array_column($data,'icon');
        $icon_Infos = CoinImage::getItemsByIds($icon_Items);
        $recommendItems = CoinApplicateRecommend::getRecommendItemsByType(2);
        foreach($data as $key => &$value){
            if($value['icon']){
                $value['icon_url'] = isset($icon_Infos[$value['icon']]) ? Yii::$app->params['service']['OssService']['url'].$icon_Infos[$value['icon']]['file_url'] : '';
            }else{
                $value['icon_url'] = '';
            }
            if($value['h5_url']){
                $value['has_h5'] = 1;
            }else{
                $value['has_h5'] = 0;
            }
            if($value['android_url']){
                $value['has_android'] = 1;
            }else{
                $value['has_android'] = 0;
            }
            $has_ios_url = $value['ios_url']?1:0;
            $has_app_store = $value['app_store_url']?1:0;
            if($has_ios_url && $has_app_store){
                $value['has_ios'] = 3;
            }else if($has_ios_url){
                $value['has_ios'] = 1;
            }else if($has_app_store){
                $value['has_ios'] = 2;
            }else{
                $value['has_ios'] = 0;
            }
            if(isset($recommendItems[$value['id']])){
                $value['isrecommend'] = 1;
            }
        }
        return ['count' => $count, 'data' => $data, 'code' => 0];

    }


    public static function getApplicateById($id)
    {
        return self::find()->where(['id' => $id])->asArray()->one();
    }

    public static function getApplicate($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public static function getAllApplicate($condition = [])
    {
        return self::find()->select('id,name')->where($condition)->asArray()->all();
    }

    public static function getAppItemsByName($name)
    {
        return array_column(self::find()->select('id as app_id,name,icon,advertise,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url,redirect_type,type')
                                        ->where(['and',['like','name',$name,],['enable' => 1]])->asArray()->all(),null,'app_id');
    }

    public static function getH5ApplicateById($id)
    {
        return self::find()->where(['id' => $id])
                           ->select('id,h5_icon,name,show_width,show_height,open_type,advertise,official_url,h5_image_ids,h5_image_showtype,description,type,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url')
                           ->asArray()->one();
    }

    public static function getDisableIds($condition = [])
    {
        $condition = array_merge($condition, ['enable' => 0]);
        return array_column(self::find()->where($condition)->select('id')->asArray()->all(),'id');
    }


}