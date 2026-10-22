<?php

namespace common\models\psources;

use yii\elasticsearch\ActiveRecord;

class LockBlock extends ActiveRecord
{
    // 索引名相当于库名
    public static function index()
    {
        return 'wangjian';
    }

    // 类别名相当于表名
    public static function type()
    {
        return 'test';
    }

    // 属性
    public function attributes()
    {
        $mapConfig = self::mapConfig();
        return array_keys($mapConfig['properties']);
    }

    /**
     *[mapConfig mapping配置]
     *返回这个模型的映射
     */
    public static function mapConfig()
    {
        return [
            'properties' => [
                'block' => [
                    'type' => 'nested',
                    'properties' => [
                        'index' => ['type' => 'long'],
                        'height' => ['type' => 'long'],
                        'ts' => ['type' => 'long'],
                        'hash' => ['type' => 'string']
                    ]
                ],
                'beneficiary' => ['type' => 'string'],
                'unfreeze_id' => ['type' => 'long'],
                'creator' => ['type' => 'string'],
                'action_type' => ['type' => 'long'],
                'success' => ['type' => 'long'],
                'withdraw' => [
                    'type' => 'nested',
                    'properties' => [
                        'amount' => ['type' => 'long']
                    ]
                ]
            ]
        ];
    }

    public static function mapping()
    {
        return [
            static::type() => self::mapConfig(),
        ];
    }

    /**
     * 设置（更新）此模型的映射
     */
    public static function updateMapping()
    {
        $db = self::getDb();
        $command = $db->createCommand();
        if (!$command->indexExists(self::index())) {
            $command->createIndex(self::index());
        }
        $command->setMapping(self::index(), self::type(), self::mapping());
    }

    //获取此模型的映射
    public static function getMapping()
    {
        $db = self::getDb();
        $command = $db->createCommand();
        return $command->getMapping();
    }
}