<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午4:05
 */

namespace backend\models\coin;

use yii\base\Model;

class CoinPlatformForm extends Model
{
    public $id;
    public $name;
    public $chain_id;
    public $download_url;
    public $introduce;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['name'], 'required', 'on' => 'add'],
            [['id', 'name'], 'required', 'on' => 'update'],
            [['chain_id', 'download_url', 'introduce'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chain_id' => '所属链',
            'name' => '名称',
            'download_url' => '下载链接',
            'introduce' => '介绍'
        ];
    }

    public function scenarios()
    {
        return [
            'add' => [
                'id',
                'name',
                'chain_id',
                'download_url',
                'introduce'
            ],
            'update' => [
                'id',
                'name',
                'chain_id',
                'download_url',
                'introduce'
            ],
        ];
    }


}