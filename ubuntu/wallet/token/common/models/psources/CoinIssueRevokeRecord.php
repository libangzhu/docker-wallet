<?php

namespace common\models\psources;

use Yii;

class CoinIssueRevokeRecord extends CommonActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coins_issue_revoke_record}}';
    }

    public function rules()
    {
        return [
            [['revoke_tx', 'revoke_send_transaction', 'issue_coin_id'], 'required'],
            [['issue_coin_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['revoke_tx', 'revoke_send_transaction', 'issue_coin_id', 'revoke_query_transaction'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function getCoin()
    {
        return $this->hasOne(CoinIssueCoin::className(), ['id' => 'issue_coin_id']);
    }
}