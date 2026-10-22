<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2019/3/1
 * Time: 17:37
 */

namespace common\models\psources;

class CoinPlatformWithHold extends BaseActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';
    const SCENARIOS_CONSOLE = 'console';

    const STATUS_NO = 0; //创建失败
    const STATUS_YES = 1; //创建成功
    const STATUS_CREATEING = 2; //生效中
    const STATUS_UNPAID = 3; //尚未支付

    const ORIGIN_MANAGE = 1; //管理员创建
    const ORIGIN_USER = 2;  //h5用户创建

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
            [['platform', 'address', 'private_key', 'exer', 'brower_url', 'token', 'host', 'wallet_address', 'status', 'port', 'fee', 'origin'], 'required'],
            [['platform', 'address', 'private_key', 'exer', 'brower_url', 'token', 'host', 'wallet_address', 'hash'], 'string'],
            ['host','unique','message'=>'该IP已存在'],
            ['platform','unique','message'=>'该平行链已存在'],
            [['platform'], 'string', 'length' => [1, 30]],
            [['token'], 'string', 'length' => [1, 10]],
            [['port', 'status', 'origin'], 'integer'],
            ['host', 'verfiyIp']
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['platform', 'token', 'address',  'private_key', 'fee', 'host', 'port', 'wallet_address', 'status', 'origin', 'hash', 'exer'],
            self:: SCENARIOS_UPDATE => ['id', 'hash', 'status'],
            self:: SCENARIOS_CONSOLE => ['status'],
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

    public static function tableName()
    {
        return 'coin_platform_withhold';
    }

    public static function getRecord($platform)
    {
        return self::find()->where(['platform' => $platform])->asArray()->one();
    }

    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $query = self::find();
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $count = $query->count();
        $data = $query->offset(($page - 1) * 10)->limit($limit);
        $data = $data->asArray()->all();
        return ['count' => $count, 'data' => $data, 'code' => 0];
    }

    public function addOne($params)
    {
        $params = array_filter($params, function ($value) {
            if (null == $value) {
                return false;
            }
            return true;
        });
        $this->setAttributes($params, false);
        try {
            return (bool)$this->save();
        } catch (\Exception $exception) {
            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
    }

    public function updateOne($params)
    {
        $params = array_filter($params, function ($value) {
            if (null === $value) {
                return false;
            }
            return true;
        });
        if (isset($params['id']) && !empty($params['id'])) {
            $coin = self::findOne(['id' => $params['id']]);
            if ($coin === null) {
                return ['code' => 1, 'msg' => '平台不存在'];
            }
            unset($params['id']);
        }
        $coin->setAttributes($params, false);
        try {
            return (bool)$coin->save();
        } catch (\Exception $exception) {
            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['tokens', 'platform_id', 'issue_charge', 'charge_unit']);
    }
}