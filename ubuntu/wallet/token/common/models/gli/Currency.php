<?php

namespace common\models\gli;

use common\core\BaseActiveRecord;

/**
 * Class Currency
 * @package common\models\gli
 */
class Currency extends BaseActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%currency}}';
	}
}
