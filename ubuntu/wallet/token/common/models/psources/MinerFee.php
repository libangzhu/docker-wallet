<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 下午2:50
 */

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class MinerFee extends BaseActiveRecord
{
    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    /**
     * 获取矿工费列表
     *
     * @param int $page
     * @param int $limit
     * @param array $condition
     * @return array
     */
    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andFilterWhere($item);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit)->asArray()->all();
        return ['count' => $count, 'data' => $data];
    }

}