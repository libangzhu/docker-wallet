<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 上午10:22
 */

namespace backend\models\coin;

use yii\base\Model;

class NoticeForm extends Model
{
    public $id;
    public $title;
    public $author;
    public $content;
    public $status;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['title', 'content', 'author'], 'required', 'on' => 'add'],
            [['id', 'title', 'content', 'author'], 'required', 'on' => 'edit'],
        ];
    }

    public function scenarios()
    {
        return [
            'add'  => ['title', 'content', 'status', 'author'],
            'edit' => ['id', 'title', 'content', 'status', 'author'],
        ];
    }
}