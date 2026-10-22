<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class CoreAsset
 * @package backend\assets
 */
class CoreAsset extends AssetBundle
{
    public $sourcePath = '@common/metronic';

    public $css = [
        'global/plugins/font-awesome/css/font-awesome.min.css',
        'global/plugins/simple-line-icons/simple-line-icons.min.css',
        'global/plugins/bootstrap/css/bootstrap.min.css',
		'other/css/style.css?2018.0510',
    ];

	public $js = [
//		'global/plugins/bootstrap/js/bootstrap.min.js',
		//'global/plugins/js.cookie.min.js',
//		'global/scripts/app.min.js',
	];

	public $depends = [
		'backend\assets\JqueryAsset',
        //'yii\web\YiiAsset',
        //'yii\grid\GridViewAsset'
    ];
}
