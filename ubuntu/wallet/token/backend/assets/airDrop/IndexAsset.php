<?php

namespace backend\assets\airDrop;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/air-drop';
    public $js         = [
        'list.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
