<?php

namespace common\models;

use Firebase\JWT\JWT;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * 实现User组件中的身份识别类 参见 yii\web\user
 * This is the model class for table "{{%admin}}".
 *
 * @property string $uid
 * @property string $username
 * @property string $password
 * @property string $access_token
 * @property string $reg_time
 * @property string $reg_ip
 * @property string $last_login_time
 * @property string $last_login_ip
 * @property string $update_time
 * @property integer $status
 * @property integer $platform_id
 * @property integer $group
 */
class Admin extends \common\modelsgii\Admin implements IdentityInterface
{
    const STATUS_DELETED = 2;
    const STATUS_ACTIVE = 1;

    /**
     * 根据UID获取账号信息
     */
    public static function findIdentity($uid)
    {
        return static::find()->where(['uid' => $uid])->one();
    }

    /**
     * 根据用户名获取账号信息
     *
     * @param string $username
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->salt;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 验证密码
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * 设置加密后的密码
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 设置密码干扰码
     */
    public function generateAuthKey()
    {
        $this->salt = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password = null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::find()->where(['access_token' => $token, 'status' => self::STATUS_ACTIVE])->one();
        if (!$user) {
            return false;
        }
//        if ($user->expire_at < time()) {
//            throw new UnauthorizedHttpException('the access - token expired ', -1);
//        } else {
//            return $user;
//        }

        return $user;
    }

    public function loginByAccessToken($accessToken, $type)
    {
        print_r(static::findIdentityByAccessToken($accessToken, $type));
        exit;
    }

    public static function verfication($accessToken)
    {
        $key = Yii::$app->params['Jwt']['Key'];//key要和签发的时候一样
        try {
            JWT::$leeway = 60;//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($accessToken, $key, ['HS256']); //HS256方式，这里要和签发的时候对应
            $arr = (array)$decoded;
            $resp = [
                'code' => 0,
                'data' => $arr['user']
            ];
        } catch (\Exception $e) {  //其他错误
            $resp = [
                'code' => -1,
                'data' => $e->getMessage()
            ];
        }

        return $resp;
    }

    /**
     * Generate accessToken string
     * @return string
     * @throws \yii\base\Exception
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    public static function loadArray(array $data)
    {
        return self::getDb()->createCommand()->batchInsert(self::tableName(),
            ['bind_uid', 'username', 'salt', 'password', 'reg_time', 'reg_ip', 'last_login_time', 'last_login_ip', 'update_time', 'status', 'platform_id'],
            $data)->execute();
    }
}
