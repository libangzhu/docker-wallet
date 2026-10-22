<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 17:31:23
 * @author rlgy <rlgyzhcn@qq.com>
 */

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

class TableZhAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js';
    public $js         = [
        'bootstrap-table-zh-CN.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
