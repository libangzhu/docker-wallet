<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 下午3:01
 */

namespace common\models\pwallet;

use Yii;
use common\core\BaseActiveRecord;

class Notice extends BaseActiveRecord
{
    const STATUS_ON = 1;    //激活
    const STATUS_OFF = 0;    //未激活

    const TYPE_ROLL = 1;    //滚动
    const TYPE_POP = 0;    //弹窗

    const TOP_TRUE = 1; //置顶公告

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    public function rules()
    {
        return [
            [['title', 'content', 'type', 'status', 'platform_id', 'author'], 'required'],
            [['status', 'type', 'platform_id', 'is_top'], 'integer'],
            //['is_top', 'verfiyIsTop'],
            //['type', 'verfiyType'],
            //[['is_top'], 'safe']
        ];
    }

    public function verfiyIsTop($attribute, $params)
    {
        $count = self::find()->where(['platform_id' => $this->platform_id, 'is_top' => Notice::TOP_TRUE])->count();
        if ($count >= 1) {
            $this->addError($attribute, '置顶公告只能1条');
            return false;
        }
    }

    public function verfiyType($attribute, $params)
    {
        $count = self::find()->where(['platform_id' => $this->platform_id, 'type' => $this->type])->count();
        if (Notice::TYPE_ROLL == $this->type) {
            if ('create' == self::getScenario()) {
                if ($count == 3) {
                    $this->addError($attribute, '滚动公告最多只能3条');
                    return false;
                }
            }

            if ('update' == self::getScenario()) {
                if ($count > 3) {
                    $this->addError($attribute, '滚动公告最多只能3条');
                    return false;
                }
            }

        }

        if (Notice::TYPE_POP == $this->type) {
            if ('create' == self::getScenario()) {
                if ($count == 1) {
                    $this->addError($attribute, '弹窗公告最多只能1条');
                    return false;
                }
            }
            if ('update' == self::getScenario()) {
                if ($count > 1) {
                    $this->addError($attribute, '弹窗公告最多只能1条');
                    return false;
                }
            }
        }

    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['title', 'content', 'author', 'status', 'type', 'platform_id', 'is_top'],
            self:: SCENARIOS_UPDATE => ['id', 'title', 'content', 'author', 'status', 'type', 'platform_id', 'is_top'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'title' => '公告标题',
            'content' => '公告内容',
            'author' => '作者',
            'status' => '公告状态',
            'type' => '公告类型',
            'platform_id' => '钱包',
        ];
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