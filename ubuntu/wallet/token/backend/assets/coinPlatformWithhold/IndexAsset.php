<?php
namespace backend\assets\coinPlatformWithhold;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/coin-platform-withhold';
    public $js = [
        'index.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];

}