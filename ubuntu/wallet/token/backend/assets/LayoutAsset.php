<?php


namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class LayoutAsset
 * @package backend\assets
 */
class LayoutAsset extends AssetBundle
{
    public $sourcePath = '@common/metronic';

    public $css = [
		'global/css/components-md.min.css',
        //'global/css/components-rounded.min.css',
        //'global/css/plugins-md.min.css',
        'global/css/plugins.min.css',

		'layouts/layout3/css/layout.min.css',
		'layouts/layout3/css/themes/blue-hoki.min.css'
    ];

    public $js = [
//		'layouts/layout3/scripts/layout.min.js',
//        'other/js/common.js?01101'
    ];

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];
}
