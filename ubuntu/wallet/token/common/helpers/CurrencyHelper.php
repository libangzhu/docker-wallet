<?php

namespace common\helpers;

use common\models\parse\Currency;
use common\models\gli\Currency as GliCurrency;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * Author: libingke
 * Date: 2017/10/30
 * Time: 11:18
 */
class CurrencyHelper
{
	/**
	 * [获取币种数组列表]
	 * @author: libingke
	 * @param bool $all
	 * @param string $index
	 * @return array
	 */
	public static function getCurrencyList($all = false, $index = 'id')
	{
		$index = in_array($index, ['id', 'name']) ? $index : 'id';
		try {
			$data = Currency::find()->select('symbol_name name,symbol_id id')->indexBy($index)->column();

		} catch (\yii\db\Exception $e) {
			//测试环境ip白名单限制时用
			if ($e->getCode() == 1045) {
				$data = GliCurrency::find()->select('name,id')->indexBy($index)->column();
			} else {
				$data = [];
			}
		}

		if ($all == true) {
			if ($index == 'name') {
				unset($data['USDT'],$data['CNY']);
				$result = ArrayHelper::merge(['' => '全部', 'USDT' => 'USDT'], $data, ['CNY' => 'CNY']);
			} else {
				unset($data[1],$data[15]);
				$result = ArrayHelper::merge(['' => '全部', 15 => 'USDT'], $data, [1 => 'CNY']);
			}

		} else {
			$result = $data;
		}

		return $result;
	}

	/**
	 * [获取币种名]
	 * @author: libingke
	 * @param $id
	 * @return string
	 */
	public static function getCurrencyName($id)
	{
		try {
			$name = Currency::find()->select('symbol_name')->where(['symbol_id' => $id])->scalar();
		} catch (\yii\db\Exception $e) {
			if ($e->getCode() == 1045) {
				$name = GliCurrency::find()->select('name')->where(['id' => $id])->scalar();
			} else {
				$name = $id;
			}
		}

		return isset($name) && $name ? $name : $id;
	}

	/**
	 * [获取币种id]
	 * @author: libingke
	 * @param $name
	 * @return false|null|string
	 */
	public static function getCurrencyId($name)
	{
		try {
			$id = Currency::find()->select('symbol_id')->where(['symbol_name' => $name])->scalar();
		} catch (\yii\db\Exception $e) {
			if ($e->getCode() == 1045) {
				$id = GliCurrency::find()->select('id')->where(['name' => $name])->scalar();
			} else {
				$id = $name;
			}
		}

		return isset($id) && $id ? $id : $name;
	}
}