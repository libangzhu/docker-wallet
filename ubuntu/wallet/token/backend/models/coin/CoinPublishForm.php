<?php
/**
 * Created By Sublime Text 3
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

namespace backend\models\coin;

use yii\base\Model;
use Yii;

class CoinPublishForm extends Model
{
    const SCENARIOS_ADD    = 'add';
    const SCENARIOS_UPDATE = 'update';

    public $id;
    public $company_name;
    public $coin_name_en;
    public $coin_name_zh;
    public $address;
    public $owner;
    public $platform_id;

    public function rules()
    {
        return [
            [['company_name', 'coin_name_zh', 'coin_name_en', 'address'], 'string', 'max' => 255],
            [['owner'], 'ownerValidate'],
            [['id', 'platform_id'], 'integer', 'min' => 0],
            [
                ['id', 'company_name', 'coin_name_zh', 'coin_name_en'],
                'required',
                'on' => self::SCENARIOS_UPDATE
            ],
            [['company_name', 'coin_name_zh', 'coin_name_en'], 'required', 'on' => self::SCENARIOS_ADD],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'company_name' => '公司名称',
            'coin_name_zh' => '币种中文名',
            'coin_name_en' => '币种英文名',
            'address'      => '热钱包地址',
            'platform_id'  => '平台id',
            'owner'        => '企业用户组',
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIOS_ADD    => ['company_name', 'coin_name_zh', 'coin_name_en', 'address', 'owner'],
            self::SCENARIOS_UPDATE => [
                'id',
                'company_name',
                'coin_name_zh',
                'coin_name_en',
                'address',
                'owner'
            ],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function ownerValidate($attribute, $params)
    {
        $roleName   = $this->$attribute;
        $auth       = Yii::$app->authManager;
        $child_role = $auth->getRole($roleName);
        if (!$child_role) {
            $this->addError($attribute, '企业用户组不存在');
        }
        return true;
    }
}