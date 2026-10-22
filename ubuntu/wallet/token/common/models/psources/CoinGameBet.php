<?php

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

/**
 * CoinReleaseMember
 *
 * @property int    $id
 * @property int    $status
 * @property string $address
 * @property string $amount
 */
class CoinGameBet extends BaseActiveRecord
{
    const SCENARIOS_ADD    = 'add';
    const SCENARIOS_UPDATE = 'update';

    const VAILD_TRUE = 1;
    const VAILD_FALSE = 0;

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_game_bet}}';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'round' => '地址',
            'player' => '数量',
            'amount' => '交易hash',
            'height' => '状态',
            'rand_num' => '状态',
            'player_win' => '状态',
        ];
    }

    public function rules()
    {
        return [
            [['round', 'amount', 'height', 'guess_num', 'rand_num', 'player_win', 'vaild'], 'int'],
            [['player'], 'string']
        ];
    }

    public function sercians()
    {
        return [
            self::SCENARIOS_ADD    => ['round', 'amount', 'height',  'guess_num', 'rand_num', 'player_win', 'player', 'vaild'],
            self::SCENARIOS_UPDATE => ['round', 'amount', 'height', 'guess_num',  'rand_num', 'player_win', 'player', 'vaild'],
        ];
    }

    public static function loadArray(array $data)
    {
        return self::getDb()->createCommand()->batchInsert(self::tableName(),
            ['round', 'player', 'amount', 'height', 'guess_num', 'rand_num', 'player_win', 'platform'],
            $data)->execute();
    }
}
