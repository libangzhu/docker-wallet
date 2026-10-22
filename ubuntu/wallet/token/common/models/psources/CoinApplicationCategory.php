<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/11
 * Time: 18:49
 */
namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinApplicationCategory extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_application_category}}';
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
    /**
     * 获取应用分类列表
     *
     * @param  integer $page           [description]
     * @param  integer $limit          [description]
     * @param  array   $condition      [description]
     * @param  array   $order_by       [description]
     * @return [type]  [description]
     */
    public static function getList($page = 1, $limit = 10, $condition = [], $order_by = ['sort' => SORT_ASC], $select = [])
    {
        $query = self::find()->joinWith('platform');
        foreach($condition as $item){
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit)->orderby($order_by);
        $data = $data->asArray()->all();
        $icon_Items = array_column($data,'icon');
        $banner_Items = array_column($data,'banner');
        $id_Items = array_column($data,'id');
        $image_Items = array_merge($icon_Items,$banner_Items);
        $icon_Infos = CoinImage::getItemsByIds($image_Items);
        //获取分类应用数量
        $cate_app_items = CoinAppCate::getAppCountByCateIds($id_Items);
        $recommendItems = CoinApplicateRecommend::getRecommendItemsByType(1);
        foreach($data as $key => &$value){
            if($value['icon']){
                $value['icon_url'] = Yii::$app->params['service']['OssService']['url'].$icon_Infos[$value['icon']]['file_url'];
            }else{
                $value['icon_url'] = '';
            }
            if($value['banner']){
                $value['banner_image_url'] = Yii::$app->params['service']['OssService']['url'].$icon_Infos[$value['banner']]['file_url'];
            }else{
                $value['banner_image_url'] = '';
            }
            if(isset($cate_app_items[$value['id']])){
                $value['app_count'] = $cate_app_items[$value['id']];
            }else{
                $value['app_count'] = 0;
            }
            if(isset($recommendItems[$value['id']])){
                $value['isrecommend'] = 1;
            }
        }
        return ['count' => $count, 'data' => $data, 'code' => 0];
    }

    /**
     * @param $id
     */
    public static function getCategoryById($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public static function getAppCategory($id)
    {
        return self::find()->where(['id' => $id])->asArray()->one();
    }

    public static function getCateItemsArray($condition = [])
    {
        return self::find()->where($condition)->asArray()->all();
    }

    public static function getAllList($condition=[])
    {
        if($condition){
            return self::find()->andWhere($condition)->orderBy('sort asc')->asArray()->all();
        }else{
            return self::find()->orderBy('sort asc')->asArray()->all();
        }

    }

    public static function getCateItemsByIds($ids)
    {
        return array_column(self::find()->where(['in','id',$ids])->asArray()->all(),null,'id');
    }

    public static function getDisableIds($condition = [])
    {
        $condition = array_merge($condition, ['enable' => 0]);
        return array_column(self::find()->where($condition)->select('id')->asArray()->all(),'id');
    }

    public function getPlatform()
    {
        return $this->hasOne(CoinPlatform::className(), ['id'=>'platform_id']);
    }

}
