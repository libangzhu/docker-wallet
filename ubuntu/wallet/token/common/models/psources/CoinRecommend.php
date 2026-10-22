<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 10:21:08
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use common\models\psources\Coin;
use Yii;

class CoinRecommend extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_recommend}}';
    }

    /**
     * 获取推荐列表
     *
     * @param  integer $page           [description]
     * @param  integer $limit          [description]
     * @param  array   $condition      [description]
     * @param  array   $order_by       [description]
     * @return [type]  [description]
     */
    public static function getList($page = 1, $limit = 10, $condition = [], $order_by = [], $select = [])
    {
        $query = self::find();
        if (!empty($condition)) {
            $query = $query->where($condition);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit);
        if (!empty($order_by)) {
            # code...
            $data = $data->orderby($order_by);
        }
        $data = $data->asArray()->all();
        //获取币种详情
        $coin_ids = array_column($data, 'cid');
        if (empty($select)) {
            $coin_info = Coin::getCoinInfoByIds($coin_ids, ['id', 'name'], 'id');
        } else {
            $coin_info = Coin::getCoinInfoByIds($coin_ids, $select, 'id');
        }
        foreach ($data as $key => &$value) {
            $value['coin'] = $coin_info[$value['cid']]['name'];
        }
        unset($key, $value);

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

    /**
     * 添加推介币种
     *
     * @param [type] $params [description]
     */
    public static function addRecommendCoin($params)
    {
        $id             = $params['id'] ?? null;
        $platform_id    = $params['platform_id'] ?? 1;
        $coin_name      = $params['coin'] ?? '';
        $recommend_type = $params['recommend'] ?? 1;
        $sort           = $params['sort'] ?? 0;
        $type           = $params['type'] ?? 1;
        $chain          = $params['chain'] ?? '';

        $coin = Coin::findOne(['name' => strtoupper($coin_name), 'platform' => strtoupper($chain)]);
        if (empty($coin)) {
            return ['code' => -1, 'msg' => '币种不存在'];
        }

        if (!empty($id)) {
            $coin_recommend = CoinRecommend::findOne($id);
        } else {
            $coin_recommend = new CoinRecommend();
            $count          = self::find()->where(['cid' => $coin->id, 'platform_id' => $platform_id, 'type' => $type, 'chain' => $chain])->count();
            if ($count > 0) {
                return ['code' => -1, 'msg' => '推荐币种已经存在'];
            }
        }

        $recommend_type = $recommend_type == 1 ? 1 : 2;
        $sort           = (int) $sort;

        $coin_recommend->cid         = $coin->id;
        $coin_recommend->recommend   = $recommend_type;
        $coin_recommend->platform_id = $platform_id;
        $coin_recommend->sort        = $sort;
        $coin_recommend->type        = $type;
        $coin_recommend->chain       = $chain;
        if ($coin_recommend->save()) {
            return ['code' => 0, 'msg' => '保存成功'];
        }
        return ['code' => -1, 'msg' => '保存失败'];
    }
}
