<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-1
 * Time: 下午5:13
 */

namespace common\models\pwallet;

use Yii;
use common\core\BaseActiveRecord;


class Bill extends BaseActiveRecord
{
    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'txid'      => 'HashId',
            'chain'     => '主链',
            'coinname'  => '币种',
            'from'      => '转出地址',
            'to'        => '转入地址',
            'value'     => '金额',
            'fee'       => '矿工费',
            'status'    => '状态',
            'type'      => '交易类型',
            'height'    => '区块高度',
            'note'      => '备注',
            'blocktime' => '更新时间'
        ];
    }

    public function rules()
    {
        return [
            [['txid', 'chain', 'coinname', 'from', 'to', 'value', 'fee', 'type'], 'required', 'message' => '{attribute} 不能为空'],
            [['txid', 'from', 'to', 'note'], 'string', 'max' => 255],
            [['chain', 'coinname'], 'string', 'max' => 32],
            [['value', 'fee'], 'double'],
            [['status', 'type'], 'number', 'max' => 2],
        ];
    }
}