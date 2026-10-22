<?php

namespace common\core;

/**
 * 对框架的AR的扩展，所有后台数据模型都继承自该类
 */
class BaseActiveRecord extends \yii\db\ActiveRecord
{
    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit)->asArray()->all();
//        $sql   = $query->createCommand()->getSql();
        $data = ['count' => $count, 'data' => $data];
        if ($count > 0) {
            $data['code'] = 0;
        } else {
            $data['code'] = 1;
            $data['msg']  = '数据为空';
        }
        return $data;
    }

    public static function getList2($page = 1, $limit = 10, $condition = [], $order_by = [])
    {
        $query = self::find();
        if (!empty($condition)) {
            $query = $query->where($condition);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit);
        if (!empty($order_by)) {
            # code...
            $data = $data->orderby($order_by)->asArray()->all();
        } else {
            $data = $data->asArray()->all();
        }
        // $sql   = $query->createCommand()->getSql();
        $data = ['count' => $count, 'data' => $data];
        if ($count > 0) {
            $data['code'] = 0;
        } else {
            $data['code'] = 1;
            $data['msg']  = '数据为空';
        }
        return $data;
    }
}
