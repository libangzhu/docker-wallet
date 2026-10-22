<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class CoinSupportedCoin extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_supported_coin}}';
    }

    public function rules()
    {
        return [
            [['platform_id', 'coin_name'], 'required'],
            [['platform_id'], 'integer'],
            ['coin_name', 'string', 'length' => [1, 50]],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['platform_id', 'coin_name'],
            self:: SCENARIOS_UPDATE => ['platform_id', 'coin_name'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributeLabels()
    {
        return [
            'platform_id' => '所属钱包',
            'coin_name' => '币种名称',
        ];
    }

    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit)->asArray()->all();

        return ['count' => $count, 'data' => $data];
    }

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
}