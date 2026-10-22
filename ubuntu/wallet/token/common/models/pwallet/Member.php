<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-13
 * Time: 上午9:50
 */

namespace common\models\pwallet;

use common\base\Exception;
use Yii;
use common\core\BaseActiveRecord;

/**
 * Class Member
 * @property integer $uid
 * @property string $username
 * @property string $password
 * @property string $salt
 * @property string $realname
 * @property string $nickname
 * @property string $phone
 * @property string $email
 * @property double $capital
 * @property string $register_at
 * @property integer $recommend_id
 * @property integer $recommend_number
 * @property string $account_address
 * @package common\models\pwallet
 */
class Member extends BaseActiveRecord
{
    const SCENARIO_ADD    = 'add';
    const SCENARIO_UPDATE = 'update';

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['uid', 'recommend_id', 'recommend_number'], 'integer'],
            [['username', 'realname', 'nickname'], 'string', 'min' => 0, 'max' => 64],
            [['phone'], 'string', 'max' => 16],
            [['password', 'email'], 'string', 'max' => 255],
            [['register_at'], 'date', 'type' => 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['capital'], 'number'],
            [['account_address'], 'string'],

            [['username'], 'usernameExistsValidate', 'on' => self::SCENARIO_ADD],
            [['username', 'password', 'register_at'], 'required', 'on' => self::SCENARIO_ADD],
            [['uid', 'username'], 'required', 'on' => self::SCENARIO_UPDATE],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_ADD    => [
                'username',
                'password',
                'realname',
                'nickname',
                'phone',
                'email',
                'capital',
                'register_at',
                'recommend_id',
                'recommend_number',
                'account_address'
            ],
            self::SCENARIO_UPDATE => [
                'uid',
                'username',
                'password',
                'realname',
                'nickname',
                'phone',
                'email',
                'capital',
                'register_at',
                'recommend_id',
                'recommend_number',
                'account_address'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'uid'              => '用户ID',
            'username'         => '用户名',
            'password'         => '密码',
            'realname'         => '真实姓名',
            'nickname'         => '昵称',
            'phone'            => '电话号码',
            'email'            => '邮箱',
            'capital'          => '资产',
            'register_at'      => '注册时间',
            'recommend_id'     => '推介人ID',
            'recommend_number' => '推介人数',
            'account_address'  => '托管账户地址',
        ];
    }

    public static function usernameExists($username)
    {
        return (bool)self::findOne(['username' => $username]);
    }

    public function usernameExistsValidate($attribute, $params)
    {
        if (self::usernameExists($this->$attribute)) {
            $this->addError($attribute, 'Username Exists!');
        }
    }

    /**
     * 添加一个新用户
     * @throws \Exception
     */
    public function addOne()
    {
        $this->salt     = Yii::$app->security->generateRandomString();
        $this->password = Yii::$app->security->generatePasswordHash($this->password . $this->salt);
        if ($this->recommend_id) {
            $recommend = self::findOne(['uid' => $this->recommend_id]);
            if ($recommend) {
                $recommend->recommend_number += 1;
                $recommend->save(false);
            } else {
                $this->recommend_id = '';
            }
        }
        $this->save(false);
    }

    public function updateOne()
    {
        $old = self::findOne(['uid' => $this->uid]);
        if ($this->recommend_id) {
            //更新推介人数
            if ($old->recommend_id && $old->recommend_id != $this->recommend_id) {
                $recommend = self::findOne(['uid' => $old->recommend_id]);
                if ($recommend) {
                    $recommend->recommend_number -= 1;
                    $recommend->save(false);
                }
            }
            $recommend = self::findOne(['uid' => $this->recommend_id]);
            if ($recommend) {
                $recommend->recommend_number += 1;
                $recommend->save(false);
            }
        }
        $this->save(false);
    }

    public static function userExists($uid)
    {
        return (bool)self::findOne($uid);
    }

}