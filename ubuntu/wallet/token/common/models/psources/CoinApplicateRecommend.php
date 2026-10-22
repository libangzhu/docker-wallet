<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/17
 * Time: 9:36
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinApplicateRecommend extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_applicate_recommend}}';
    }

    public static function getRecommend($relate_id,$type)
    {
        return self::find()->where(['relate_id' => $relate_id,'type'=>$type])->one();
    }

    public static function getRecommendCount()
    {
        return self::find()->count();
    }

    /**
     * @param $type
     * 1 分类 2 应用
     */
    public static function getRecommendItemsByType($type)
    {
        $data = self::find()->where(['type'=>$type])->asArray()->all();
        return array_column($data,null,'relate_id');
    }

    public static function updateName($id,$type,$name)
    {
        $recommend = self::find()->where(['relate_id' => $id,'type' => $type])->one();
        if($recommend){
            $recommend->name=$name;
            $recommend->save();
        }
    }

    public static function del($id,$type)
    {
        $recommend = self::find()->where(['relate_id' => $id,'type' => $type])->one();
        if($recommend){
            $recommend->delete();
        }
    }

    public static function getRecommendList($condition = [])
    {
        return self::find()->where($condition)->select('relate_id as app_cate_id,name,type,icon')->asArray()->orderBy('sort asc')->all();
    }

    public static function getRecommendById($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public function getPlatform()
    {
        return $this->hasOne(CoinPlatform::className(), ['id'=>'platform_id']);
    }


}