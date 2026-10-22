<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/13
 * Time: 14:08
 */


namespace common\models\psources;

use Yii;

class CoinImage extends BaseActiveRecord
{
    public static function tableName()
    {
        return 'coin_image';
    }


    public static function addOneRecord($file_path)
    {
        $coin_image = new self();
        $coin_image->file_url = $file_path;
        $coin_image->save();
        return $coin_image->id;
    }

    public static function getItemsByIds($idItems)
    {
        $data = self::find()->where(['in', 'id', $idItems])->asArray()->all();
        return array_column($data, null, 'id');
    }

    public static function getItemById($id)
    {
        return self::find()->where(['id' => $id])->asArray()->one();
    }

    public static function getItemsByImageIds($image_ids)
    {
        if ($image_ids) {
            if (is_array($image_ids)) {
                $idItems = $image_ids;
            } else {
                $idItems = explode(',', $image_ids);
            }
            $data = self::find()->where(['in', 'id', $idItems])->asArray()->all();
            foreach ($data as &$value) {
                $value['image_url'] = Yii::$app->params['service']['OssService']['url'] . $value['file_url'];
            }
            return ['code' => 0, 'data' => $data];
        } else {
            return ['code' => 0, 'data' => []];
        }
    }


}