<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class LoginAsset
 * @package backend\assets
 */
class LoginAsset extends AssetBundle
{
	public $sourcePath = '@common/metronic';

	public $css = [
		'global/plugins/uniform/css/uniform.default.css',
		'global/css/components-rounded.min.css',
		'global/css/plugins.min.css',
		'pages/css/login-4.min.css',
	];

	public $js = [
		'global/plugins/jquery-validation/js/jquery.validate.min.js',
		'global/plugins/jquery-validation/js/additional-methods.min.js',
		'global/plugins/backstretch/jquery.backstretch.min.js',
		'global/plugins/uniform/jquery.uniform.min.js',
		'pages/scripts/login-4.js',
	];

	public $depends = [
		'backend\assets\CoreAsset',
	];
}
