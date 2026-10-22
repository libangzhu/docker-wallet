<?php
/**
 * Created By Sublime Text 3
 *
 * @authors rlgy <rlgyzhcn@qq.com>
 * @date    2018-09-07 17:41:04
 */

namespace backend\assets\wallet;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/wallet';
    public $js = [
        'index.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];

}