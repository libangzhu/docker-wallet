<?php

namespace backend\assets\coinSupportedCoin;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/coin-supported-coin';
    public $js         = [
        'index.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
