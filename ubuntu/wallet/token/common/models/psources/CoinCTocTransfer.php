<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinCTocTransfer extends BaseActiveRecord
{
    const TYPE_SELL = 1; //卖单
    const TYPE_BUY = 0;  //买单

    const CONSENSUE_YES = 1; //需要
    const CONSENSUE_NO = 0;  //不需要

    const TYPE_ORDER = 1;       //挂单
    const TYPE_TRANSFER = 2;  //交易
    const TYPE_REVOKE = 3;  //撤消

    const C2C_ORDERING = 'C2C_ORDERING';
    const C2C_ORDER_FAILED = 'C2C_ORDER_FAILED';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_ctoc_transfer}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';


    public function rules()
    {
        return [
            [['is_sell', 'type', 'address', 'token_name', 'market_name', 'price', 'amount', 'step', 'txhex', 'transfer_url', 'transfer_number', 'consensus'], 'required'],
            [['send_result', 'query_result', 'msg', 'consensus_platform'], 'safe'],
            ['consensus_platform', 'verfiyConsensusPlatform']
        ];
    }

    public function verfiyConsensusPlatform($attribute, $params)
    {
        if ($this->consensus == self::CONSENSUE_YES && empty($this->consensus_platform)){
            $this->addError($attribute, '共识平台错误');
            return false;
        }
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['is_sell', 'type', 'address', 'token_name', 'market_name', 'price', 'amount', 'step', 'txhex', 'transfer_url', 'transfer_number', 'consensus', 'consensus_platform'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }
}
