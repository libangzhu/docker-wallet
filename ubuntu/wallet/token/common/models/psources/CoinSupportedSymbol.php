<?php

namespace common\models\psources;

class CoinSupportedSymbol extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%coin_supported_symbol}}';
    }

    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data  = $query->offset(($page - 1) * 10)->limit($limit)->all();

        return ['count' => $count, 'data' => $data, 'code' => 0];
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

//    public function attributes()
//    {
//        return array_merge(parent::attributes(), ['code']);
//    }


    public function getCoinInfo()
    {
        return $this->hasOne(Coin::class, ['id' => 'currency']);
    }

    public function getBaseCurrencyInfo()
    {
        return $this->hasOne(Coin::class, ['id' => 'base_currency']);
    }
}
