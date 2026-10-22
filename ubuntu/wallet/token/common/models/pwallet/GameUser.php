<?php
/**
 * Created By Sublime text 3
 * User: rlgyzhcn
 * Date: 18-08-12
 * Time: 下午14:17
 */

namespace common\models\pwallet;

use common\core\BaseActiveRecord;
use Yii;

class GameUser extends BaseActiveRecord{

	public static function getDb(){
		return Yii::$app->get('db_pwallet');
	}

    public function formName()
    {
        return '';
    }
}