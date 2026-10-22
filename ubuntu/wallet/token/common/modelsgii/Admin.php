<?php

namespace common\modelsgii;

use yii\helpers\HtmlPurifier;

/**
 * This is the model class for table "{{%admin}}".
 *
 * @property string $uid
 * @property string $username
 * @property string $password
 * @property string $access_token
 * @property string $salt
 * @property string $reg_time
 * @property string $reg_ip
 * @property string $last_login_time
 * @property string $last_login_ip
 * @property string $update_time
 * @property integer $status
 * @property integer $platform_id
 * @property integer $group
 */
class Admin extends \common\core\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'salt'], 'required'],
            [
                ['username', 'password'],
                'filter',
                'filter' => function ($str) {
                    return HtmlPurifier::process($str);
                }
            ],
            [['reg_time', 'reg_ip', 'last_login_time', 'last_login_ip', 'update_time', 'status', 'platform_id'], 'integer'],
            [['username'], 'string', 'max' => 32],
            [['password'], 'string', 'min' => 6, 'max' => 60],
            [['salt'], 'string', 'max' => 32],
            [['access_token','group'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid'             => 'Uid',
            'username'        => 'Username',
            'password'        => 'Password',
            'salt'            => 'Salt',
            'reg_time'        => 'Reg Time',
            'reg_ip'          => 'Reg Ip',
            'last_login_time' => 'Last Login Time',
            'last_login_ip'   => 'Last Login Ip',
            'update_time'     => 'Update Time',
            'status'          => 'Status',
            'platform_id'     => 'PlatformId',
            'group'           => 'Group'
        ];
    }
}
