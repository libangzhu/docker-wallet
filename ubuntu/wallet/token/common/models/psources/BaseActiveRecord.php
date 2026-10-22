<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 14:32:44
 * @authors rlgy <rlgyzhcn@qq.com>
 */
namespace common\models\psources;

use common\core\BaseActiveRecord as CoreBase;
use Yii;

class BaseActiveRecord extends CoreBase
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }
}
