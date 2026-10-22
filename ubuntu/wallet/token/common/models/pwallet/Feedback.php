<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-7
 * Time: 下午4:31
 */

namespace common\models\pwallet;

use common\base\Exception;
use Yii;
use common\core\BaseActiveRecord;

class Feedback extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        if ($count) {
            $data = $query->offset(($page - 1) * $limit)->limit($limit)->asArray()->all();
            return ['code' => 0, 'count' => $count, 'data' => $data];
        }
        return ['code' => 1, 'msg' => '数据为空'];
    }
}