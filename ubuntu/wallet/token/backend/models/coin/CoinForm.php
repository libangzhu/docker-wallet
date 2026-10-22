<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午4:05
 */

namespace backend\models\coin;

use yii\base\Model;

class CoinForm extends Model
{
    public $id;
    public $name;
    public $optional_name;
    public $nickname;
    public $sid;
    public $icon;
    public $introduce;
    public $official;
    public $paper;
    public $release;
    public $platform;
    public $chain;
    public $price;
    public $area_search;
    public $publish_count;
    public $circulate_count;
    public $address;
    public $decimals;
    public $treaty;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['name'], 'required', 'on' => 'add'],
            [['id', 'name'], 'required', 'on' => 'update'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'optional_name' => '可选简称',
            'nickname' => '别称',
            'sid' => '全称',
            'icon' => '图标',
            'introduce' => '介绍',
            'official' => '官网',
            'paper' => '白皮书',
            'release' => '发行时间',
            'platform' => '平台',
            'chain' => '主链',
            'price' => '众筹价格',
            'area_search' => '区块链查询',
            'publish_count' => '发行总量',
            'circulate_count' => '流通总量',
            'address' => '合约地址',
            'decimals' => '精度',
            'treaty' => '合约'
        ];
    }

    public function scenarios()
    {
        return [
            'add' => [
                'id',
                'name',
                'optional_name',
                'nickname',
                'sid',
                'icon',
                'introduce',
                'official',
                'paper',
                'release',
                'platform',
                'chain',
                'price',
                'area_search',
                'publish_count',
                'circulate_count',
                'address'
            ],
            'update' => [
                'id',
                'name',
                'optional_name',
                'nickname',
                'sid',
                'icon',
                'introduce',
                'official',
                'paper',
                'release',
                'platform',
                'address',
                'chain',
                'price',
                'area_search',
                'publish_count',
                'circulate_count',
            ],
        ];
    }


}