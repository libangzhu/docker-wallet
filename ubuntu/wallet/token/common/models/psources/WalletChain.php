<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class WalletChain extends BaseActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    const STATUS_NO = 0; //创建失败
    const STATUS_YES = 1; //创建成功

    const ORIGIN_MANAGE = 1; //管理员创建
    const ORIGIN_USER = 2;  //h5用户创建

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{wallet_chain}}';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform' => '平行链名称',
            'address' => '代扣地址',
            'private_key' => '代扣私钥',
            'execer' => '执行器',
            'fee' => '手续费',
            'brower_url' => '浏览器链接',
            'token' => '手续费（基础代币）',
            'host' => '钱包服务IP',
            'port' => '钱包服务端口',
            'wallet_address' => '钱包地址',
            'status' => '申请状态',
            'origin' => '创建人',
            'hash' => '申请费用hash'
        ];
    }

    public function rules()
    {
        return [
            [['platform', 'address', 'private_key', 'execer', 'brower_url', 'token', 'host', 'wallet_address', 'status', 'port', 'fee', 'origin', 'hash'], 'required'],
            [['platform', 'address', 'private_key', 'execer', 'brower_url', 'token', 'host', 'wallet_address', 'hash'], 'string'],
            ['address','unique','message'=>'地址已存在'],
            [['platform'], 'string', 'length' => [1, 30]],
            [['token'], 'string', 'length' => [1, 10]],
            [['port', 'status', 'origin'], 'integer'],
            ['host', 'verfiyIp']
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['platform', 'token', 'address',  'private_key', 'fee', 'host', 'port', 'wallet_address', 'status', 'origin', 'hash'],
            self:: SCENARIOS_UPDATE => ['status'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function verfiyIp($attribute, $params)
    {
        if (!preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $this->host)) {
            $this->addError($attribute, '错误的Ip地址');
            return false;
        }
    }
}