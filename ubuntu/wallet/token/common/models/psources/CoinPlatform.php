<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 14:30:49
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

class CoinPlatform extends BaseActiveRecord
{

    public static function tableName()
    {
        return '{{%coin_platform}}';
    }

    /**
     * 获取钱包信息列表
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

    public function attributes()
    {
        return array_merge(parent::attributes(), ['chain_name', 'charge_unit', 'tokens', 'exer']);
    }

    public function getChain()
    {
        return $this->hasOne(CoinPlatformWithHold::className(), ['id' => 'chain_id']);
    }

    public function getGas()
    {
        return $this->hasOne(CoinSupportedCoin::className(), ['id' => 'charge_unit_id']);
    }

    public function getCurrency()
    {
        return $this->hasMany(CoinSupportedCurrency::className(), ['platform_id' => 'id']);
    }
}
