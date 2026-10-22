<?php
namespace common\models\gli;

use common\core\BaseActiveRecord;

class Cache extends BaseActiveRecord
{
	/**
	 * 保存数据
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public static function setData($key, $value)
	{
		if ($key) {
			$one = self::findOne(['key' => $key]);
			if ($one == null) {
				$one = new self();
			}
			$one->key = $key;
			$one->value = $value;
			$one->info = "更新于:" . date("Y-m-d H:i:s");
			return $one->save();
		}
		return false;
	}


	/**
	 * 获取数据
	 * @param $key
	 */
	public static function getData($key)
	{
		$one = self::findOne(['key' => $key]);
		if (isset($one->value)) {
			return $one->value;
		} else {
			return null;
		}
	}


	/**
	 * 删除数据
	 * @param $key
	 */
	public static function deleteData($key)
	{
		if (!is_string($key)) {
			return false;
		}

		$one = self::findOne(['key' => $key]);
		if ($one) {
			return $one->delete();
		}
		return true;
	}
}