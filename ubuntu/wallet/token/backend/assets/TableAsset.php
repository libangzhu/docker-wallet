<?php
namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Class TableAsset
 * @package backend\assets
 */
class TableAsset extends AssetBundle
{
    public $sourcePath = '@common/metronic';

    public $css = [
	];

	public $js = [
		'global/plugins/bootstrap-table/bootstrap-table.min.js',
		// 'global/plugins/bootstrap-table/bootstrap-table-fixed-columns.js',
	];

	public $depends = [
		'backend\assets\JqueryAsset'
    ];

	public $jsOptions = [
		'position' => \yii\web\View::POS_END,
	];
}
