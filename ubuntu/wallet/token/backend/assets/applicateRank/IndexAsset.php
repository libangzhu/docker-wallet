<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/1
 * Time: 17:31
 */

namespace backend\assets\applicateRank;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/applicate-rank';
    public $js         = [
        'index.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}