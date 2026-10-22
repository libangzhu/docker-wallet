<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinSupportedChain extends BaseActiveRecord
{
    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_supported_chain}}';
    }

    public function rules()
    {
        return [
            [['coin_id', 'platform_id'], 'required'],
            [['coin_id', 'platform_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['coin_id', 'platform_id'],
            self:: SCENARIOS_UPDATE => ['id', 'coin_id', 'platform_id']
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['chain', 'icon']);
    }

    public function getCoin()
    {
        return $this->hasOne(Coin::className(), ['id' => 'coin_id'])
            ->select('id, icon, name, introduce, nickname, platform, chain, treaty');
    }
}
