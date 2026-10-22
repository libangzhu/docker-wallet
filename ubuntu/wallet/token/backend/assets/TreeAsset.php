<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * 树形插件
 * Class TreeAsset
 * @package backend\assets
 */
class TreeAsset extends AssetBundle
{
    public $sourcePath = '@common/metronic';

    public $css = [
        'global/plugins/jstree/dist/themes/default/style.min.css',
    ];

    public $js = [
        'global/plugins/jstree/dist/jstree.min.js',
    ];
}
