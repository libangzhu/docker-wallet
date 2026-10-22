<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
	public $sourcePath = '@common/metronic';

	public $css = [];

	public $js = [];

	public $depends = [
		'backend\assets\IeAsset',
		'backend\assets\CoreAsset',
		'backend\assets\LayoutAsset',
	];

	/**
	 * 定义按需加载JS方法，注意加载顺序在最后
	 * @param $view \yii\web\View
	 * @param $jsFile string
	 */
	public static function addScript($view, $jsFile)
	{
		$view->registerJsFile(
			$jsFile,
			[
				AppAsset::className(),
				'depends' => 'backend\assets\AppAsset'
			]
		);
	}

	/**
	 * 定义按需加载css方法，注意加载顺序在最后
	 * @param $view \yii\web\View
	 * @param $cssFile string
	 */
	public static function addCss($view, $cssFile)
	{
		$view->registerCssFile(
			$cssFile,
			[
				AppAsset::className(),
				'depends' => 'backend\assets\AppAsset'
			]
		);
	}
}
