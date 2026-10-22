<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 上午9:53
 */

namespace common\models\pwallet;

use common\base\Exception;
use common\core\BaseActiveRecord;
use Yii;

class News extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    public static function tableName()
    {
        return '{{%news}}';
    }

    /**
     * 获取文章列表
     * @param int $page
     * @param int $limit
     * @param array $condition
     * @return array
     */
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

    /**
     * 添加文章
     * @param $params
     * @return bool|array
     */
    public function addOne($params)
    {
        return $this->realWrite($params);
    }

    /**
     * 更新文章
     * @param $params
     * @return bool|array
     */
    public function updateOne($params)
    {
        return $this->realWrite($params);
    }

    /**
     * 实际操作数据方法
     */
    private function realWrite($params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        $params = array_filter($params, function ($value, $key) {
            if (ctype_digit($key) || empty($value)) {
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
        if (isset($params['id'])) {
            $article = self::findOne($params['id']);
            if ($article === null) {
                return ['code' => 1, 'msg' => '文章不存在'];
            }
            unset($params['id']);
        } else {
            $article = $this;
            $params  = array_merge($params, ['create_at' => date('Y-m-d H:i:s')]);
        }
        $params = array_merge($params, ['update_at' => date('Y-m-d H:i:s', time())]);
        if (!isset($params['status'])) {
            $params['status'] = 0;
        }
        $article->setAttributes($params, false);
        try {
            return (bool)$article->save();
        } catch (Exception $exception) {
            return ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
        }
    }
}