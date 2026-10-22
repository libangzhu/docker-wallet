<?php

namespace common\models\search;

use common\core\BaseModel;
use yii\helpers\ArrayHelper;

class UserSearch extends BaseModel
{
	public $from_date;//修改日期 始

	public $to_date;//修改日期 末

	public $edit_field;//修改字段

	public $uid;//uid

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [];
	}


	/**
	 * [修改内容：范围]
	 * @return array
	 */
	public static function getFieldRange($hasAll = false)
	{
		$base = $hasAll !== false ? ['' => '全部'] : [];

		return ArrayHelper::merge($base, [
			1 => '手机号',
			2 => '邮箱',
			3 => '用户类型',
			4 => '证件类型',
			5 => '证件号',
			6 => '代理商',
			7 => '姓名'
		]);
	}

	/**
	 * [用户范围：范围]
	 * @return array
	 */
	public static function getUserTypeRange($hasAll = false)
	{
		$base = $hasAll !== false ? ['' => '全部'] : [];

		return ArrayHelper::merge($base, [
			1 => '国内用户',
			2 => '国际用户',
			3 => '未认证用户'
		]);
	}

	/**
	 * [证件类型：范围]
	 * @return array
	 */
	public static function getCredentialsTypeRange($hasAll = false)
	{
		$base = $hasAll !== false ? ['' => '全部'] : [];

		return ArrayHelper::merge($base, [
			1 => '身份证',
			2 => '护照',
			3 => '驾照',
			4 => '其它',
		]);
	}

	/**
	 * [经纪商：范围]
	 * @return array
	 */
	public static function getEconomyRange($hasAll = false)
	{
		$base = $hasAll !== false ? ['' => '全部'] : [];

		return ArrayHelper::merge($base, [
			1 => '无经纪商用户',
			2 => '马来西亚',
			3 => '其它',
		]);
	}

	/**
	 * [代理商：范围]
	 * @return array
	 */
	public static function getAgentRange($hasAll = false)
	{
		$base = $hasAll !== false ? ['' => '全部'] : [];

		return ArrayHelper::merge($base, [
			1 => '代理A',
			2 => '代理B',
			3 => '其它',
		]);
	}
}
