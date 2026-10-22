<?php

namespace common\models\psources;

use Yii;
use common\core\BaseActiveRecord;

class CoinAirDropTransfer extends BaseActiveRecord
{
    const ORIGIN_IMPORT = 1;
    const ORIGIN_ADD = 2;
    const ORIGIN_API = 3;

    const TYPE_COINS = 1;
    const TYPE_TOKEN = 2;

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_airdrop_transfer}}';
    }

    //定义场景
    const SCENARIOS_CREATE = 'create';
    const SCENARIOS_UPDATE = 'update';


    public function rules() {
        return [
            [['to_address', 'amount', 'coin_name'], 'required','message'=>"请输入{attribute}"],
            [['origin', 'txhash', 'msg', 'balance'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'to_address' => '钱包地址',
            'amount' => '数量',
            'coin_name' => '币种名称',
            'origin' => '来源',
            'txhash' => '返回hash',
            'msg' => '返回结果',
            'balance' => '钱包余额',
        ];
    }

    public function scenarios() {
        $scenarios = [
            self:: SCENARIOS_CREATE => ['to_address', 'amount','coin_name', 'origin'],
        ];
        return array_merge( parent:: scenarios(), $scenarios);
    }

    public static function loadArray(array $data)
    {
        return self::getDb()->createCommand()->batchInsert(self::tableName(),
            ['to_address', 'coin_name', 'amount', 'origin', 'txhash', 'msg', 'balance'],
            $data)->execute();
    }

    public static function getOrigin()
    {
        return [
            self::ORIGIN_IMPORT    => '批量导入',
            self::ORIGIN_ADD   => '单独添加',
            self::ORIGIN_API => '接口导入',
        ];
    }
}