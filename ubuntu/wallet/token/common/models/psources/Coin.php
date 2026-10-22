<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午1:10
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

/**
 * Class Coin
 *
 * @property array $minerFee 矿工费用
 * @package common\models\pwallet
 */
class Coin extends BaseActiveRecord
{
    /**
     * @throws \yii\base\InvalidConfigException
     * @return null|object|\yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin}}';
    }

    /**
     * 获取币种信息列表
     *
     * @param  int $page
     * @param  int $limit
     * @param  array $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data = $query->offset(($page - 1) * 10)->limit($limit)->asArray()->all();
        foreach ($data as $key => $item) {
            $data[$key]['price'] = sprintf("%0.6f", $item['price']);
        }
        return ['count' => $count, 'data' => $data];
    }

    /**
     * 获取币种信息列表
     *
     * @param  int $page
     * @param  int $limit
     * @param  array $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getSelectList($page = 1, $limit = 10, $columns = [], $condition = [], $orderBy = [])
    {
        $ids_str = implode(',', $orderBy);
        $order = "FIELD(`id`,$ids_str)";
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        if (false != $orderBy) {
            $ids_str = implode(',', $orderBy);
            $order = "FIELD(`id`,$ids_str)";
            $data = $query->select($columns)->offset(($page - 1) * $limit)->limit($limit)->asArray()->orderBy([$order => true])->all();
        } else {
            $data = $query->select($columns)->offset(($page - 1) * $limit)->limit($limit)->asArray()->all();
        }

        return ['count' => $count, 'data' => $data];
    }

    /**
     * 添加一个币种信息
     */
    public function addOne($params)
    {
        $params = array_filter($params, function ($value) {
            if (null == $value) {
                return false;
            }
            return true;
        });
        $this->setAttributes($params, false);
        try {
            return (bool)$this->save();
        } catch (\Exception $exception) {
            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
    }

    /**
     * 修改币种信息
     */
    public function updateOne($params)
    {
        $params = array_filter($params, function ($value) {
            if (null === $value) {
                return false;
            }
            return true;
        });
        if (isset($params['id']) && !empty($params['id'])) {
            $coin = self::findOne(['id' => $params['id']]);
            if ($coin === null) {
                return ['code' => 1, 'msg' => '币种不存在'];
            }
            unset($params['id']);
        }
        $coin->setAttributes($params, false);
        try {
            return (bool)$coin->save();
        } catch (\Exception $exception) {
            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
    }

    public function getMinerFee()
    {
        return $this->hasOne(MinerFee::className(), ['platform' => 'chain']);
    }

    /**
     * 获取平台列表
     *
     * @return array
     */
    public static function getPlatformList()
    {
        $data = self::find()->select('platform')->asArray()->all();
        $data = array_unique(array_map('strtolower', array_column($data, 'platform')));
        return $data;
    }

    /**
     * 返回类型，类型就是币种的主链
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getChainList()
    {
        $data = self::find()->select('chain')->asArray()->all();
        $data = array_unique(array_map('strtoupper', array_column($data, 'chain')));
        return $data;
    }

    /**
     * 获取某个平台下的所有币种
     *
     * @param  integer $id
     * @param  integer $page
     * @param  integer $limit
     * @return array
     */
    public static function getAllByPlatformId($id, $page = 1, $limit = 10)
    {
        if (!ctype_alnum($id)) {
            return false;
        }
        $query = self::find()->andWhere("JSON_CONTAINS(platform_id,'$id')");
        $count = $query->count();
        if ($count) {
            $data = $query->limit($limit)->offset(($page - 1) * $limit)->asArray()->all();
            return ['total' => $count, 'list' => $data];
        }
        return false;
    }

    /**
     * 根据id列表获取币种信息
     *
     * @param  array $ids [description]
     * @param  array $select [description]
     * @param  string $sort_by [description]
     * @return array  [description]
     */
    public static function getCoinInfoByIds($ids, $select, $sort_by)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $query = self::find();
        if (!empty($select)) {
            $query = $query->select($select);
        }
        $data = $query->where(['in', 'id', $ids])->asArray()->all();
        if (empty($data)) {
            return false;
        }
        if (!in_array($sort_by, $select)) {
            return $data;
        }
        return array_column($data, null, 'id');
    }

    /**
     * 获取推介币种信息列表
     *
     * @param  int $page
     * @param  int $limit
     * @param  array $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRecommendList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $query = $query->rightJoin('coin_recommend', 'coin.id=coin_recommend.cid');
        $count = $query->count();
        $data = $query->offset(($page - 1) * 10)->limit($limit)->asArray()->all();
        foreach ($data as $key => $item) {
            $data[$key]['price'] = sprintf("%0.6f", $item['price']);
        }
        return ['count' => $count, 'data' => $data];
    }

    /**
     * 批量获取币种别称
     *
     * @param array $names
     * @return array
     */
    public static function getNicknamesByNames(array $names)
    {
        $data = self::find()->select(['name', 'nickname'])->where(['in', 'name', $names])->asArray()->all();
        if ($data && is_array($data)) {
            $data = array_column($data, 'nickname', 'name');
        }
        return $data;
    }

    /**
     * 根据name批量返回币种信息
     *
     * @param array $names
     * @return array
     */
    public static function getInfoByNames(array $names)
    {
        if (!is_array($names)) {
            $names = [$names];
        }
        $data = self::find()->where(['in', 'name', $names])->asArray()->all();
        if ($data && is_array($data)) {
            return array_column($data, null, 'name');
        }
        return $data ? $data : [];
    }

    /**
     * 获取币种
     */
    public static function getOneById($id)
    {
        return self::find()->where(['id' => $id])->one();
    }
}
