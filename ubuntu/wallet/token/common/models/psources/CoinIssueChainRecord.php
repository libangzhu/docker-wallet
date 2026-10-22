<?php

namespace common\models\psources;

use Yii;

class CoinIssueChainRecord extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_PRE_CREATE = 'create';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_issue_chain_record}}';
    }

    public function rules()
    {
        return [
            [['pre_create_tx', 'pre_send_transaction', 'pre_query_transaction', 'finish_tx', 'finish_send_transaction', 'finish_query_transaction', 'issue_coin_id'], 'required'],
            [['issue_coin_id'], 'integer'],
            [['finish_tx'], 'safe']
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_PRE_CREATE => ['pre_create_tx', 'pre_send_transaction', 'pre_query_transaction', 'finish_tx', 'finish_send_transaction', 'finish_query_transaction', 'issue_coin_id'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function getCoin()
    {
        return $this->hasOne(CoinIssueCoin::className(), ['id' => 'issue_coin_id']);
    }

    public function getAdvance()
    {
        return $this->hasOne(CoinIssueCoin::className(), ['id' => 'issue_coin_id'])->where(['status' => CoinIssueCoin::STATUS_ADVANCE]);
    }

    public function getConfirm()
    {
        return $this->hasOne(CoinIssueCoin::className(), ['id' => 'issue_coin_id'])->where(['>', 'status', CoinIssueCoin::STATUS_CONFIRM]);
    }
}