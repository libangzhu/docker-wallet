<?php

namespace app\models;

use yii\elasticsearch\ActiveRecord;

class LockBeneficiary extends ActiveRecord
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
                'beneficiary' => ['type' => 'string'],
                'terminate' => [
                    'type' => 'nested',
                    'properties' => [
                        'amount_left' => ['type' => 'long'],
                        'amount_back' => ['type' => 'long']
                    ]
                ],
                'success' => ['type' => 'long'],
                'block' => [
                    'type' => 'nested',
                    'properties' => [
                        'hash' => ['type' => 'string'],
                        'index' => ['type' => 'long'],
                        'height' => ['type' => 'long'],
                        'ts' => ['type' => 'long']
                    ]
                ],
                'unfreeze_id' => ['type' => 'long'],
                'action_type' => ['type' => 'long'],
                'creator' => ['type' => 'string']
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