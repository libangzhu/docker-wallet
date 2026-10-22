<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%fxee_auth}}".
 *
 * @property string $user_id
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires
 * @property string $scope
 * @property int $addtime
 */
class FxeeAuth extends \common\core\BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fxee_auth}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['access_token', 'refresh_token', 'expires', 'addtime'], 'required'],
            [['expires'], 'safe'],
            [['scope'], 'string'],
            [['expires', 'addtime'], 'integer'],
            [['user_id', 'access_token', 'refresh_token'], 'string', 'max' => 255],
            [['access_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('backend', 'User ID'),
            'access_token' => Yii::t('backend', 'Access Token'),
            'refresh_token' => Yii::t('backend', 'Refresh Token'),
            'expires' => Yii::t('backend', 'Expires'),
            'scope' => Yii::t('backend', 'Scope'),
            'addtime' => Yii::t('backend', 'Addtime'),
        ];
    }
}
