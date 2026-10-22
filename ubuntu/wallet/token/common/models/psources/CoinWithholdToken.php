<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 18:10:27
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

class CoinWithholdToken extends BaseActiveRecord
{
    const SCENARIOS_ADD    = 'add';
    const SCENARIOS_UPDATE = 'update';

    public static function tableName()
    {
        return '{{%coin_withhold_token}}';
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id'      => 'ID',
            'address' => '代扣地址',
            'status'  => '代扣状态',
            'token'   => 'Token名称',
            'wid'     => '代扣币种ID',
        ];
    }

    public function rules()
    {
        return [
            [['id', 'status', 'wid'], 'integer'],
            [['address', 'token'], 'string', 'max' => 255],
            [['id', 'wid', 'status', 'token', 'address'], 'required', 'on' => self::SCENARIOS_UPDATE],
            [['wid', 'status', 'token', 'address'], 'required', 'on' => self::SCENARIOS_ADD],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIOS_ADD    => ['token', 'address', 'status', 'wid'],
            self::SCENARIOS_UPDATE => ['token', 'address', 'status', 'wid', 'id'],
        ];
    }
}
