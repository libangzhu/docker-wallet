<?php

namespace common\models;

class AdminSession extends \common\core\BaseActiveRecord
{
	/**
	 * [记录sessionToken]
	 * @author libingke
	 * @param $id
	 * @param $sessionToken
	 * @return bool|false|int
	 */
	public static function insertSession($id, $sessionToken)
	{
		//查询admin_session表中是否有用户的登录记录
		$loginAdmin = static::findOne(['id' => $id]);
		if (!$loginAdmin) {
			//如果没有记录则新建此记录
			$sessionModel = new AdminSession();
			$sessionModel->id = $id;
			$sessionModel->session_token = $sessionToken;
			$result = $sessionModel->save();
		} else {
			//如果存在记录（则说明用户之前登录过）则更新用户登录token
			$loginAdmin->session_token = $sessionToken;
			$result = $loginAdmin->update();
		}
		return $result;
	}
}
