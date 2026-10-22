<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 17:31:23
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace backend\assets\coinPublishRule;

use yii\web\AssetBundle;
use yii\web\View;

class ListAsset extends AssetBundle
{
    public $sourcePath = '@backend/web/js/coin-publish-rule';
    public $js         = [
        'list.js',
    ];
    public $jsOptions = [
        'position' => View::POS_END,
    ];
}
