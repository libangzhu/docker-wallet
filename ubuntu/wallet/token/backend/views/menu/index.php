<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Menu;

/*if (isset($dataProvider->query->where['pid'])) {
	$pid = $menu::find()->select('pid')->where(['id' => $dataProvider->query->where['pid']])->scalar();
}*/

$columns = [
	[
		'class' => \common\core\CheckboxColumn::className(),
		'name'  => 'id',
		'options' => ['width' => '20px;'],
		'checkboxOptions' => function ($model, $key, $index, $column) {
			return ['value' => $key,'label'=>'<span></span>','labelOptions'=>['class' =>'mt-checkbox mt-checkbox-outline','style'=>'padding-left:19px;']];
		}
	],
	/*[
		'header' => 'ID',
		'attribute' => 'id',
		'options' => ['width' => '50px;']
	],*/
	[
		'label' => '排序',
		'value' => 'sort',
		'options' => ['width' => '50px;'],
	],
	[
		'header' => '名称',
		'attribute' => 'title',
		'options' => ['width' => '150px;'],
		'contentOptions' => ['style' => 'white-space: nowrap;'],
	],
	[
		'header' => '菜单路径',
		'content' => function($model, $key){
			$tip = Html::a('<li class="fa fa-home"></li>', ['menu/index']) . ': ';
			if($model['pid'] == 0){
				return $tip . Html::a($model['title'], ['index', 'pid' => $model['id']]);
			} else {
				$nav = Menu::getParentMenus($key);
				$nav_title = '';
				foreach ($nav as $value) {
					$nav_title .= Html::a($value['title'], ['index', 'pid'=>$value['id']]).' / ';
				}
				$nav_title = trim($nav_title, ' > ');
				return $tip . $nav_title;
			}
		},
		'contentOptions' => ['style' => 'white-space: nowrap;'],
	],
	[
		'label' => '分组',
		'options' => ['width' => '80px;'],
		'value' => function($model){
			return $model['group'] ? $model['group'] : '';
		}
	],
	[
		'label' => 'URL',
		'value' => 'url',
		'options' => ['width' => '150px;'],
		'contentOptions' => ['style' => 'white-space: nowrap;'],
	],

	[
		'label' => '是否隐藏',
		'options' => ['width' => '80px;'],
		'content' => function($model){
			return $model['hide'] ? Html::tag('span','隐藏',['style'=>'color:#f00;']) : '正常';
		},
		'contentOptions' => ['style' => 'white-space: nowrap;'],
	],
	[
		'class' => 'yii\grid\ActionColumn',
		'header' => '操作',
		'template' => '{edit} {delete}',
		'options' => ['width' => '120px;'],
		'buttons' => [
			'edit' => function ($url, $model, $key) {
				return Html::a('<i class="fa fa-edit"></i>', $url, [
					'title' => Yii::t('app', '更新'),
					'class' => 'btn btn-xs blue'
				]);
			},
			'delete' => function ($url, $model, $key) {
				return Html::a('<i class="fa fa-times"></i>', $url, [
					'title' => Yii::t('app', '删除'),
					'class' => 'btn btn-xs red ajax-get confirm'
				]);
			}
		],
	],
];

?>

<div class="row">

    <div class="col-md-2" style="min-width: 240px">
		<?php echo $this->render('_tree', ['dataProvider' => $dataProvider, 'menu' => $menu]); ?>
    </div>

    <div class="col-md-9">
        <?=Html::a(Yii::$app->params['config_page']['add'] . '<i class="fa fa-plus"></i>',['add'],['class'=>"btn ".Yii::$app->params['config_page']['color_opt']])?>

        <div class="portlet-body">
			<?php \common\widgets\Pjax::begin(['options'=>['id'=>'pjax-container']]); ?>

            <div class="table-container">
                <form class="ids">
                    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
					<?= GridView::widget([
						'dataProvider' => $dataProvider, // 列表数据
						'filterModel' => $searchModel, // 搜索模型
						'emptyText' => isset($pid) && is_numeric($pid) ?
							Html::a('<i class="fa fa-mail-reply"></i> 返回上一级', ['index', 'pid' => $pid]) :
                            Html::a('<i class="fa fa-mail-reply"></i> 无数据，返回主菜单', Yii::getAlias('@web').'/menu/index'),
						'options' => ['class' => 'grid-view'],
						/* 表格配置 */
						'tableOptions' => ['class' => 'table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer'],
						/* 重新排版 摘要、表格、分页 */
						'layout' => '{items}<div class=""><div class="col-md-5 col-sm-5">{summary}</div><div class="col-md-7 col-sm-7"><div class="dataTables_paginate paging_bootstrap_full_number" style="text-align:right;">{pager}</div></div></div>',
						/* 配置摘要 */
						'summaryOptions' => ['class' => 'pagination'],
						/* 配置分页样式 */
						'pager' => Yii::$app->params['pager'],
						/* 定义列表格式 */
						'columns' => $columns,
					]); ?>
                </form>
            </div>
			<?php \common\widgets\Pjax::end(); ?>
        </div>
    </div>

</div>