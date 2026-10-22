<?php

namespace common\models\psources;

use yii\elasticsearch\ActiveRecord;

class LockCreator extends ActiveRecord
{
    // 索引名相当于库名
    public static function index()
    {
        return 'suo_cang';
    }

    // 类别名相当于表名
    public static function type()
    {
        return 'lock_creator';
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
                'creator' => ['type' => 'string'],
                'create' => [
                    'type' => 'nested',
                    'properties' => [
                        'means' => ['type' => 'long'],
                        'fix_amount' => [
                            'type' => 'nested',
                            'properties' => [
                                'period' => ['type' => 'long'],
                                'amount' => ['type' => 'long']
                            ]
                        ],
                        'total_count' => ['type' => 'long'],
                        'start_time' => ['type' => 'long'],
                        'asset_symbol' => ['type' => 'string'],
                        'asset_exec' => ['type' => 'string']
                    ]
                ],
                'unfreeze_id' => ['type' => 'string'],
                'block' => [
                    'type' => 'nested',
                    'properties' => [
                        'hash' => ['type' => 'string'],
                        'height' => ['type' => 'long'],
                        'ts' => ['type' => 'long'],
                        'index' => ['type' => 'long']
                    ]
                ],
                'action_type' => ['type' => 'long'],
                'beneficiary' => ['type' => 'string'],
                'success' => ['type' => 'string']
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

    /**
     * Delete this model's index
     */
    public static function deleteIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(static::index(), static::type());
    }

    public static function updateRecord($book_id, $columns)
    {
        try {
            $record = self::get($book_id);
            foreach ($columns as $key => $value) {
                $record->$key = $value;
            }
            return $record->update();
        } catch (\Exception $e) { //handle error here return false; } }
            return false;
        }
    }
}