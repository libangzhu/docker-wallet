<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinCrossChain extends BaseActiveRecord
{
    const WITHDRAW = 0;
    const RECHARGE = 1;

    const CROSS_CHAIN_TRANSFERING = 'cross_chain_transfering';
    const CROSS_CHAIN_CACHE_FAIL =  'cross_chain_fail';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_cross_chain_transfer}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';


    public function rules()
    {
        return [
            [['is_with_draw', 'address', 'txhex', 'transfer_url'], 'required'],
            [['transfer_number', 'amount', 'step', 'send_result', 'query_result', 'msg'], 'safe']
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['is_with_draw', 'address', 'txhex', 'transfer_url', 'transfer_number', 'amount', 'step', 'send_result', 'query_result', 'msg'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }
}
