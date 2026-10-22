<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/16
 * Time: 11:25
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinAppCate extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_app_cate}}';
    }

    public function getApplication()
    {
        return $this->hasOne(CoinApplication::class, ['id' => 'app_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(CoinApplicationCategory::class, ['id' => 'cate_id']);
    }


    public static function getAppIdByCateId($cate_id)
    {
        return self::find()->where(['cate_id' => $cate_id])->asArray()->all();
    }

    public static function getAppCountByCateId($cate_id)
    {
        return self::find()->where(['cate_id' => $cate_id])->count();
    }

    public static function getAppCountByCateIds($cate_ids)
    {
        $data = self::find()->select('cate_id,count(*) as num')->where(['in','cate_id',$cate_ids])->groupBy('cate_id')->asArray()->all();
        return array_column($data,'num','cate_id');
    }

    public static function getAppCate($category_id,$app_id)
    {
        return self::find()->where(['cate_id' => $category_id,'app_id' => $app_id])->one();
    }

    public static function getCateCountByAppId($app_id)
    {
        return self::find()->where(['app_id' => $app_id])->count();
    }

    public static function updateSort($category_id,$app_id,$sort)
    {
        $cate_app = self::getAppCate($category_id,$app_id);
        if($cate_app){
            $cate_app->sort = $sort;
            $cate_app->save();
        }
    }

    public static function getCateItemsByAppId($app_id)
    {
        return array_column(self::find()->where(['app_id' => $app_id])->asArray()->all(),'cate_id');
    }


}