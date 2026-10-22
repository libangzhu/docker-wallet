<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class IeAsset
 * @package backend\assets
 */
class IeAsset extends AssetBundle
{
    public $sourcePath = '@common/metronic';

    public $css = [
    ];

    public $js = [
        'global/plugins/respond.min.js',
        'global/plugins/excanvas.min.js',
    ];

    public $jsOptions = ['condition' => 'lt IE9'];

    public $depends = [
        //'yii\web\YiiAsset',
        //'yii\grid\GridViewAsset'
    ];
}
