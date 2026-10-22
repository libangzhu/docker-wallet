<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class WalletSupportedTokenCategory extends BaseActiveRecord
{

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{wallet_supported_token_catetory}}';
    }
}