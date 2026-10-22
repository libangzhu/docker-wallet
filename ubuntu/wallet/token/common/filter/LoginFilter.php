<?php
namespace common\filter;

use Yii;
use yii\base\ActionFilter;
use common\models\AdminSession;

/**
 * Class LoginFilter
 * @package common\filter
 */
class LoginFilter extends ActionFilter
{
	public $filter;

	public function beforeAction($action)
	{
		if(!parent::beforeAction($action)) {
			return false;
		}

		if ($this->filter !== true) {
			return true;
		}

		//登录  所有操作都虚经过过滤器控制输出
		if(!Yii::$app->user->isGuest && $action->id != 'logout') {

			try {
				$id = Yii::$app->user->id;
				$session = Yii::$app->session;
				$username = Yii::$app->user->identity->username;
				//取出session中的用户登录token
				$tokenSES = $session->get(md5(sprintf("%s&%s", $id, $username)));
				$sessionTBL = AdminSession::findOne(['id' => $id]);
				$tokenTBL = $sessionTBL->session_token;

				//如果用户登录在 session中token不同于数据表中token
				if ($tokenSES != $tokenTBL) {
					Yii::$app->user->logout(); //执行登出操作
				}
			} catch (\Exception $e) {
					Yii::$app->user->logout();
			}

		} else {
			Yii::$app->user->logout(); //执行登出操作
		}

		return true;
	}
}