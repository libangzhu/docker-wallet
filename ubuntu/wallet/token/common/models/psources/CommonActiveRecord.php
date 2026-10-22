<?php

namespace common\models\psources;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\Linkable;
use yii\web\Link;

class CommonActiveRecord extends ActiveRecord
{
    public $cacheKeyStorage = [];      //缓存key寄存器

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = [];

        $cachePrefix = __CLASS__;
        $cache = Yii::$app->cache;

        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            if (strpos($field, '.')) {
                $fieldChunks = explode('.', $field);

                $primaryKey = is_array($this->primaryKey) ? implode('-', $this->primaryKey) : $this->primaryKey;
                $uniqueRelationCacheKey = "{$cachePrefix}_{$primaryKey}_{$fieldChunks[0]}";
                $this->addCacheKeyToStorage($uniqueRelationCacheKey);
                $relation = $cache->get($uniqueRelationCacheKey);

                if (!$relation) {
                    $relation = $this->{$fieldChunks[0]};
                    $cache->set($uniqueRelationCacheKey, $relation);
                }

                if (is_array($relation)) {
                    foreach ($relation as $relatedField => $relatedObject) {
                        if (!is_object($relatedObject)) {
                            continue;
                        }
                        $data[$fieldChunks[0]][$relatedField][$fieldChunks[1]] = $this->getRelationField($relatedObject, $fieldChunks[1]);
                    }
                } else {
                    if (!is_object($relation)) {
                        continue;
                    }
                    $data[$fieldChunks[0]][$fieldChunks[1]] = $this->getRelationField($relation, $fieldChunks[1]);
                }

            } else {
                $data[$field] = is_string($definition) ? $this->$definition : call_user_func($definition, $this, $field);
            }
        }
        $this->deleteStorageCaches();

        if ($this instanceof Linkable) {
            $data['_links'] = Link::serialize($this->getLinks());
        }

        return $recursive ? ArrayHelper::toArray($data) : $data;
    }

    /**
     * This method also will check relations which are declared in [[extraFields()]]
     * to determine which related fields can be returned.
     * @inheritdoc
     */
    protected function resolveFields(array $fields, array $expand)
    {
        $result = [];

        foreach ($this->fields() as $field => $definition) {
            if (is_integer($field)) {
                $field = $definition;
            }
            if (empty($fields) || in_array($field, $fields, true)) {
                $result[$field] = $definition;
            }
        }

        if (empty($expand)) {
            return $result;
        }

        $extraFieldsKeys = array_keys($this->extraFields());
        foreach($expand as $expandedAttribute) {

            if(in_array(explode('.',$expandedAttribute)[0], $this->extraFields())) {
                $result[$expandedAttribute] = $expandedAttribute;
            }else if(in_array($expandedAttribute, $extraFieldsKeys)) {
                $result[$expandedAttribute] = $this->extraFields()[$expandedAttribute];
            }
        }

        return $result;
    }

    /**
     * Additional method to check the related model has specified field
     */
    private function getRelationField($relatedRecord, $field)
    {
        if (!$relatedRecord->hasAttribute($field) && !$relatedRecord->isRelationPopulated($field)) {
            throw new \yii\web\ServerErrorHttpException(sprintf(
                    "Related record '%s' does not have attribute '%s'",
                    get_class($relatedRecord), $field)
            );
        }

        if($relatedRecord->hasAttribute($field)) {
            return ArrayHelper::toArray($relatedRecord)[$field];
        } else {
            return $relatedRecord->{$field};
        }
    }


    public static function quoteDbName($dbname)
    {
        return '`' . $dbname . '`';
    }


    /**
     * 添加cache key到寄存器
     * @param type $cacheKey
     */
    public function addCacheKeyToStorage($cacheKey)
    {
        if(!in_array($cacheKey, $this->cacheKeyStorage)) {
            $this->cacheKeyStorage[] = $cacheKey;
        }
    }


    /**
     * 删除寄存器里所有的cache
     */
    public function deleteStorageCaches()
    {
        foreach($this->cacheKeyStorage as $cacheKey) {
            Yii::$app->cache->delete($cacheKey);
        }
    }
}