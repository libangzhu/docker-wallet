<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;


class CoinPlatformCoins extends BaseActiveRecord
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
        return '{{%coin_platform_coins}}';
    }

    public function rules()
    {
        return [
            [['coins_name', 'coins_address', 'type', 'deviceCode', 'platform_id'], 'required'],
            [['device', 'version', 'contact'], 'safe'],
        ];
    }

    public function scenarios()
    {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['coins_name', 'coins_address', 'type', 'deviceCode', 'platform_id', 'device', 'version', 'contact'],
        ];
        return array_merge(parent:: scenarios(), $scenarios);
    }

    /**
     * 导入数据
     *
     * @param  array $datas
     * @return boolean
     */
    public static function batchImport(array $datas)
    {
        if (!is_array($datas)) {
            return false;
        }
        try {
            $result = self::getDb()->createCommand()->batchInsert(self::tableName(), ['coins_name', 'coins_address', 'type', 'deviceCode', 'platform_id', 'device', 'version', 'mobile', 'mail'], $datas)->execute();
            return $result;
        } catch (\Exception $e) {
        }
        return false;
    }
}
