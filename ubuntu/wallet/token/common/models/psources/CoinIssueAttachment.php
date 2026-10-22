<?php

namespace common\models\psources;

use Yii;
use yii\db\Expression;

class CoinIssueAttachment extends CommonActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_issue_attachment}}';
    }

    public function getTransfer()
    {
        return $this->hasOne(CoinIssueCoin::className(), ['issue_coin_id' => 'id']);
    }
}