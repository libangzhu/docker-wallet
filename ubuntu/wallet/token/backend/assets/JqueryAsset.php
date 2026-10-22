<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class JqueryAsset
 * @package backend\assets
 */
class JqueryAsset extends AssetBundle
{
    public $sourcePath = '@common/metronic';

    public $css = [
        'global/plugins/layui/css/layui.css',
    ];

    public $js = [
        'global/plugins/jquery.min.js',
        'global/plugins/layui/layui.all.js',
    ];

    public $depends = [];

    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}
