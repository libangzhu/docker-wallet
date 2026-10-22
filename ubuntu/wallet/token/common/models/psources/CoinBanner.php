<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/13
 * Time: 14:29
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinBanner extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{coin_banner}}';
    }

    public static function getBannerInfoByIds($banner_ids)
    {
        if($banner_ids) {
            if(is_array($banner_ids)){
                $idItems = $banner_ids;
            }else {
                $idItems = explode(',',$banner_ids);
            }
            $data = self::find()->where(['in','id',$idItems])->asArray()->all();
            $image_id_items = array_column($data,'image_id');
            $image_info = CoinImage::getItemsByIds($image_id_items);
            foreach ($data as &$value){
                $value['image_url'] = Yii::$app->params['service']['OssService']['url'].$image_info[$value['image_id']]['file_url'];
            }
            return ['code' => 0,'data' => $data];
        }else{
            return ['code' => 0,'data' => []];
        }
    }

    public static function getBannerById($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

    public static function getBannerDataByIds($banner_ids)
    {
        if ($banner_ids) {
            if (is_array($banner_ids)) {
                $idItems = $banner_ids;
            } else {
                $idItems = explode(',', $banner_ids);
            }
            $data = self::find()->where(['in', 'id', $idItems])->asArray()->all();
            $image_id_items = array_column($data, 'image_id');
            $image_info = CoinImage::getItemsByIds($image_id_items);
            foreach ($data as &$value) {
                $value['banner_image_url'] = $image_info[$value['image_id']]['base_url'] . $image_info[$value['image_id']]['file_url'];
                unset($value['image_id']);
            }
            return array_column($data,null,'id');
        }else{
            return [];
        }

    }

}