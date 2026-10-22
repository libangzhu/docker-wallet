<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 17:31:23
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace backend\assets\site;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/site';
    public $js         = [
        'index.js',
    ];
    public $depends = [
        'backend\assets\TableAsset',
        'backend\assets\TableZhAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
