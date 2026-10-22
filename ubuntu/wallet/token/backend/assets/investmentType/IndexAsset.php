<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 17:31:23
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace backend\assets\investmentType;

use yii\web\AssetBundle;
use yii\web\View;

class IndexAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/investment-type';
    public $js         = [
        'index.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
