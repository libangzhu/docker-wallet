<?php

namespace common\helpers;

use common\models\fxee\CashRecord;
use common\models\fxee\Member;
use common\models\fxee\MemberDetail;
use Yii;

/**
 * Class PublicHelper
 * @package common\helpers
 */
class PublicHelper
{
	/**
	 * [机器人的user_id]
	 * @author: libingke
	 * @return null | array
	 */
	public static function getRobotUserIds()
	{
		return null;
	}


	/**
	 * [查找用户]
	 * @author: libingke
	 * @param string $user
	 * @param string $returnType  'string' | 'array'
	 * @return array | string
	 */
	public static function getInFilterByUser($user, $returnType)
	{
		$search = $user;
		$result = null;
		if ($search != null && is_string($search)) {

			try {

				if (is_numeric($search)) {
					$result = Member::find()
						->select('id')
						->where(['mobile' => $search])
						->orWhere(['id' => $search])
						->column();

				} else if (is_numeric(strpos($search, '@'))) {
					$result = Member::find()->select('id')->where(['email' => $search])->column();

				} else {
					if (is_numeric(strpos($search,',')) || is_numeric(strpos($search,'，'))) {
						$search = str_replace("，",",",$search);
						$search = str_replace(" ","",$search);
						$search = explode(',', $search);
					}

					$result = MemberDetail::find()->select('uid')->where(['name' => $search])->column();
				}

				$cash = CashRecord::find()->select('uid')->where(['name' => $search])->distinct()->column();
				if (is_array($cash) && $cash)
					$result = is_array($result) ? array_unique(\yii\helpers\ArrayHelper::merge($result, $cash)) : $cash;

				if ($returnType !== 'array' && is_array($result))
					$result = implode(',', $result);

			} catch (yii\db\Exception $e) {
				//$e->getMessage();
			}

			if ($result == null)
				$result = false;
		}

		return $result;
	}
}