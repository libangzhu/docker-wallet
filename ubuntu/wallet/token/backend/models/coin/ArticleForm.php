<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 上午10:22
 */

namespace backend\models\coin;

use yii\base\Model;

class ArticleForm extends Model
{
    public $id;
    public $title;
    public $url;
    public $status;
    public $image_url;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['title','image_url'], 'required', 'on' => 'add'],
            [['id', 'title'], 'required', 'on' => 'edit'],
            [['url'], 'safe']
        ];
    }

    public function scenarios()
    {
        return [
            'add'  => ['title', 'url', 'status', 'image_url'],
            'edit' => ['id', 'title', 'url', 'status'],
        ];
    }
}