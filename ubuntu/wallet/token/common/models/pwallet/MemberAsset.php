<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-5
 * Time: 上午10:27
 */

namespace common\models\pwallet;

use common\core\BaseActiveRecord;

/**
 * Class MemberAsset
 * 用户资产表
 *
 * @property integer $id
 * @property integer $uid
 * @property string $asset
 * @property string $create_at
 * @property string $update_at
 * @package common\models\pwallet
 */
class MemberAsset extends BaseActiveRecord
{
    public static function getDb()
    {
        return \Yii::$app->get('db_pwallet');
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'uid'       => '用户ID',
            'asset'     => '资产信息',
            'create_at' => '创建时间',
            'update_at' => '更新时间'
        ];
    }
}