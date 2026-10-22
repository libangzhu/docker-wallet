<?php

namespace common\modelsgii;

/**
 * This is the model class for table "{{%menu}}".
 *
 * @property string $id
 * @property string $title
 * @property string $pid
 * @property string $sort
 * @property string $url
 * @property integer $hide
 * @property string $group
 * @property integer $status
 */
class Menu extends \common\core\BaseActiveRecord
{
	const HIDE_NO  = 0;//显示
	const HIDE_YES = 1;//隐藏

	const STATUS_SHOW = 0;//展示二级
	const STATUS_HIDE = 1;//隐藏二级

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'sort', 'hide', 'status'], 'integer'],
            [['title', 'group', 'icon'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'pid' => 'Pid',
            'sort' => 'Sort',
            'url' => 'Url',
            'hide' => 'Hide',
            'group' => 'Group',
			'icon' => 'Icon',
            'status' => 'Status',
        ];
    }
}
