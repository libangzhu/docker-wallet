<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class CoinIssueCoin extends CommonActiveRecord
{
    const UN_PAY = 0;
    const ALLOW_PAY = 1;
    const SUCCESS_PAY = 2;

    const STATUS_ADVANCE = 0;   //预发行，待确认
    const STATUS_PEDDING = 1;   //预发行成功，待用户确认
    const STATUS_CANCEL = 2;    //用户点击撤消后的状态
    const STATUS_CANCEL_SUCCESS = 3;    //撤消成功
    const STATUS_CANCEL_FAILED = 4;     //撤消失败
    const STATUS_CONFIRM = 5;   //用户点击确认后的状态
    const STATUS_ALLOW = 6;     //开启人工审核，管理员后台点击通过后的状态 (未开启人工审核，跳过该状态)
    const STATUS_REFUSE = 7;    //开启人工审核，管理员后台点击拒绝后的状态 (未开启人工审核，跳过该状态)
    const STATUS_SUCCESS = 8;   //发行成功
    const STATUS_FAILED = 9;    //（预）发行失败

    const TYPE_NO = 0; //不是增发
    const TYPE_YES = 1; //是增发

    const ISSUE_TOKEN = 'issue_token';
    const REVOKE_TOKEN = 'revoke_token';

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';
    const SCENARIOS_CANCEL = 'cancel';
    const SCENARIOS_NO_CHARGE = 'no_charge';
    const SCENARIOS_CREATE_MALL = 'create_mall';
    const SCENARIOS_VERIFY_MALL = 'verify_mall';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_issue_coin}}';
    }

    public function rules()
    {
        return [
            [['name', 'symbol', 'total', 'owner', 'introduction', 'category', 'type', 'platform_id', 'chain_id', 'charge_unit', 'charge', 'template'], 'required'],
            [['total', 'category', 'type', 'platform_id', 'chain_id'], 'integer'],
            [['introduction', 'charge_unit'], 'string', 'length' => [1, 100]],
            ['symbol', 'string', 'length' => [2, 16]],
            ['name', 'string', 'length' => [2, 128]],
            ['nickname', 'string', 'length' => [0, 10]],
            [['token_type', 'nickname'], 'safe'],
            ['template', 'verfiyTemplate'],
            #['status', 'in', 'range' => [1, 2, 0]],
            ['name', 'verfiyName'],
            ['symbol', 'verfiySymbol'],
            ['total', 'verfiyAmount']
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['name', 'symbol', 'total', 'owner', 'introduction', 'category', 'type', 'platform_id', 'chain_id', 'charge_unit', 'charge', 'token_type', 'nickname'],
            self:: SCENARIOS_CREATE_MALL => ['name', 'symbol', 'total', 'owner', 'introduction', 'category', 'type', 'platform_id', 'chain_id', 'token_type', 'nickname'],
            self:: SCENARIOS_VERIFY_MALL => ['name', 'symbol', 'total', 'owner', 'introduction', 'category', 'type', 'platform_id', 'chain_id', 'token_type', 'nickname', 'template'],
            self:: SCENARIOS_NO_CHARGE => ['name', 'symbol', 'total', 'owner', 'introduction', 'category', 'type', 'platform_id', 'chain_id', 'token_type', 'nickname'],
            self:: SCENARIOS_UPDATE => ['status'],
            self:: SCENARIOS_CANCEL => ['status'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function verfiyTemplate($attribute, $params)
    {
        if (3 == $this->token_type) {
            if (empty($this->template)) {
                $this->addError($attribute, '缺少必要的模板参数');
                return false;
            }
            foreach ($this->template as $key => $val) {
                if (empty($val)) {
                    $this->addError($attribute, $key . '不能为空');
                    return false;
                }
            }
        }
    }

    public function verfiyName($attribute, $params)
    {
        //增发
        if (CoinIssueCoin::TYPE_YES == $this->type) {
            $model = CoinIssueCoin::find()->where(['name' => $this->name, 'owner' => $this->owner, 'platform_id' => $this->platform_id, 'status' => CoinIssueCoin::STATUS_SUCCESS])->orderBy('id desc')->one();
            if (false == $model) {
                $this->addError($attribute, '该Token币种尚未发行');
                return false;
            }
            if (0 == $model->category) {
                $this->addError($attribute, '该Token币种不可增发');
                return false;
            }
        }

        //非增发
        if (CoinIssueCoin::TYPE_NO == $this->type) {
            $model = CoinIssueCoin::find()
                ->where(['name' => $this->name, 'platform_id' => $this->platform_id])
                ->andWhere(['<>', 'status', CoinIssueCoin::STATUS_FAILED])
                ->orderBy('id desc')->one();
            if ($model) {
                $this->addError($attribute, 'Token名称已存在');
                return false;
            }
        }
    }

    public function verfiySymbol($attribute, $params)
    {
//        if (35 == $this->platform_id) {
//            if (strlen($this->symbol) < 6 || strlen($this->symbol) > 16) {
//                $this->addError($attribute, 'Token简称需在6~16个字符');
//                return false;
//            }
//        } else {
//            if (strlen($this->symbol) < 1 || strlen($this->symbol) > 6) {
//                $this->addError($attribute, 'Token简称需在1~6个字符');
//                return false;
//            }
//        }

        if (!preg_match('/^([A-Z0-9]){2,16}$/', $this->symbol)) {
            $this->addError($attribute, 'Token简称必须为大写字母或数字');
            return false;
        }

        if (CoinIssueCoin::TYPE_YES == $this->type) {
            $model = CoinIssueCoin::find()->where(['symbol' => $this->symbol, 'owner' => $this->owner, 'platform_id' => $this->platform_id, 'status' => CoinIssueCoin::STATUS_SUCCESS])->orderBy('id desc')->one();
            if (false == $model) {
                $this->addError($attribute, '该Token币种尚未发行');
                return false;
            }
            if (0 == $model->category) {
                $this->addError($attribute, '该Token币种不可增发');
                return false;
            }
        }
        if (CoinIssueCoin::TYPE_NO == $this->type) {
            $model = CoinIssueCoin::find()
                ->where(['symbol' => $this->symbol, 'platform_id' => $this->platform_id])
                ->andWhere(['<>', 'status', CoinIssueCoin::STATUS_FAILED])
                ->orderBy('id desc')->one();
            if ($model) {
                $this->addError($attribute, 'Token名称已存在');
                return false;
            }
        }
    }

    public function verfiyAmount($attribute, $params)
    {
        if (CoinIssueCoin::TYPE_YES == $this->type) {
            if ($this->$attribute > (10 * 1e4)) {
                $this->addError($attribute, '增发发行量不能超过10亿');
                return false;
            }
        }
        $issue_record = CoinIssueCoin::find()->where(['platform_id' => $this->platform_id, 'symbol' => $this->symbol, 'status' => CoinIssueCoin::STATUS_SUCCESS])->sum('total');
        $issue_record = empty($issue_record) ? 0 : $issue_record;
        if (35 == $this->platform_id) {
            $unit = 900 * 1e8;
        } else {
            $unit = 900 * 1e4;
        }
        if ($issue_record + $this->$attribute > $unit) {
            $this->addError($attribute, '最大发行量900亿，目前已发行' . $issue_record . '亿');
            return false;
        }
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Token全称',
            'symbol' => 'Token简称',
            'total' => '发行数量',
            'owner' => '接收地址',
            'introduction' => 'Token简介',
            'category' => '是否增发',
            'chain_id' => '平行链名称',
            'msg' => '失败原因',
            'status' => '状态',
            'charge_unit' => '手续费',
            'template' => '模板',
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['issue_charge', 'url', 'chain_name', 'template', 'origin']);
    }

    public function getChain()
    {
        return $this->hasOne(CoinPlatformWithHold::className(), ['id' => 'chain_id']);
    }

    public function getPlatform()
    {
        return $this->hasOne(CoinPlatform::className(), ['id' => 'platform_id']);
    }

    public function getTransfer()
    {
        return $this->hasOne(CoinIssueChainRecord::className(), ['issue_coin_id' => 'id']);
    }

    public function getRevoke()
    {
        return $this->hasOne(CoinIssueRevokeRecord::className(), ['issue_coin_id' => 'id']);
    }

    public function getTemplated()
    {
        return $this->hasOne(CoinIssueAttachment::className(), ['issue_coin_id' => 'id'])->select('template, origin');
    }
}