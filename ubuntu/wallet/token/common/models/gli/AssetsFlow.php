<?php

namespace common\models\gli;

use common\core\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * 计算资产流水
 * Class AssetsFlow
 * @package common\models\gli
 */
class AssetsFlow extends BaseActiveRecord
{
	const TYPE_BUY = 0;

	const TYPE_SELL = 1;

	const TYPE_TRANSFER = 2;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%assets_flow}}';
	}

//	/**
//	 * 获取资产记录
//	 * @author: libingke
//	 * @return \yii\db\ActiveQuery
//	 */
//	public function getWallet()
//	{
//		return $this->hasMany(AssetsWallet::className(), ['user_id' => 'user_id'])
//			->groupBy(AssetsWallet::tableName().'.symbol_id')
//			->indexBy('symbol_id');
//	}

	/**
	 * [获取类型]
	 * @author: libingke
	 * @param $from_uid
	 * @param $to_uid
	 * @param null $create_time
	 * @return int
	 */
	public static function getType($from_uid, $to_uid, $create_time = null)
	{
		return static::TYPE_TRANSFER;
	}

	/**
	 * [获取类型列表]
	 * @author libingke
	 * @param bool $hasAll
	 * @param bool $hasTransfer
	 * @param bool $hasOrder
	 * @return array
	 */
	public static function getTypeList($hasAll = false, $hasTransfer = true, $hasOrder = true)
	{
		$dataBase = ['' => '全部'];
		$dataTransfer = [
			static::TYPE_TRANSFER => '转账'
		];
		$dataOrder = [
			static::TYPE_BUY  => '买',
			static::TYPE_SELL => '卖'
		];

		$data = $hasAll == true ? $dataBase : [];
		$data = $hasTransfer == true ? ArrayHelper::merge($data, $dataTransfer) : $data;
		$data = $hasOrder == true ? ArrayHelper::merge($data, $dataOrder) : $data;
		return $data;
	}

	/**
	 * [获取类型名称]
	 * @author libingke
	 * @param $id
	 * @return mixed|null
	 */
	public static function getTypeName($id)
	{
		$list = static::getTypeList();
		return isset($list[$id]) ? $list[$id] : null;
	}
}
