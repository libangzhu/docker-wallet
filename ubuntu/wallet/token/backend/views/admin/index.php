<?php

use yii\helpers\Html;
use yii\grid\GridView;

$columns = [
    /*[
        'class' => \common\core\CheckboxColumn::className(),
        'name'  => 'id',
		'options' => ['width' => '30px'],
        'checkboxOptions' => function ($model, $key, $index, $column) {
            return ['value' => $key,'label'=>'<span></span>','labelOptions'=>['class' =>'mt-checkbox mt-checkbox-outline','style'=>'padding-left:19px;']];
        },
    ],*/

	['class' => 'yii\grid\SerialColumn', 'headerOptions'=>['style'=>'width:2%;']],
    [
        'header' => '登录账号',
        'attribute' => 'username',
		'options' => ['width' => '80px'],
		'filter' => Html::dropDownList('AdminSearch[from]', $searchModel->from, [
            '' => '全部',
			'backend' => '后台用户',
			'fxee'    => 'Fxee用户',
        ]),
		'headerOptions'=>['style'=>'width:10px'], 'contentOptions' => ['style' => 'white-space: nowrap;']
    ],
	[
		'label' => '真实姓名',
		'attribute' => 'name.name',
		'options' => ['width' => '80px'],
	],
//	[
//		'label' => '后台角色',
//		'attribute' => 'role',
//		'options' => ['width' => '150px'],
//		'contentOptions' => ['style' => 'white-space: nowrap;'],
//		'content' => function($model){
//			$DbManager = new \common\core\rbac\DbManager();
//			$role = $DbManager->getRolesByUser($model['uid']);
//			if ($role && is_array($role)) {
//				$r = [];
//				foreach ($role as $key => $value) {
//					$r[] = $key;
//				}
//				/* 超级管理员判断 */
//				if(Yii::$app->params['admin'] == $model['uid']){
//					$r[] = '[超管]';
//					return implode(', ', $r);
//				}
//				return implode(', ', $r);
//			}
//			return '';
//		}
//	],
	[
		'label' => 'fxee角色',
		'attribute' => 'group.group',
		'options' => ['width' => '80px'],
	],
    [
        'header' => '状态',
        'attribute' => 'status',
		'options' => ['width' => '80px'],
		'contentOptions' => ['style' => 'white-space: nowrap;'],
        'content' => function($model){
            return $model['status'] ?
				'<li class="fa fa-check"></li> 正常':
                '<li class="fa fa-times"></li> 禁用';
        }
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => '操作',
        'template' => '{auth}',
        'options' => ['width' => '120px'],
		'contentOptions' => ['style' => 'white-space: nowrap;'],
        'buttons' => [

            'auth' => function ($url, $model, $key) {
                return Html::a('<i class="fa fa-male"></i> 分配角色', ['auth','uid'=>$key], [
                    'title' => Yii::t('app', '分配角色'),
                    'class' => 'btn btn-xs btn-success'
                ]);
            }
        ],
    ],
];

?>

<div class="row admin-admin-index">

    <div class="col-md-10 col-sm-12" style="max-width: 1000px">
        <?=Html::a(Yii::$app->params['config_page']['add'] . '<i class="fa fa-plus"></i>',['add'],['class'=>"btn ".Yii::$app->params['config_page']['color_opt']])?>

        <div class="portlet-body">
            <?php \yii\widgets\Pjax::begin(['options'=>['id'=>'pjax-container']]); ?>

            <div class="table-container">
                <form class="ids">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
					'filterModel' => $searchModel, // 搜索模型
					'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                    'options' => ['class' => 'grid-view'],
                    /* 表格配置 */
                    'tableOptions' => ['class' => 'table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer'],
                    /* 重新排版 摘要、表格、分页 */
                    'layout' => '{items}<div><div class="col-md-5 col-sm-5">{summary}</div><div class="col-md-7 col-sm-7"><div class="dataTables_paginate paging_bootstrap_full_number" style="text-align:right;">{pager}</div></div></div>',
                    /* 配置摘要 */
                    'summaryOptions' => ['class' => 'pagination'],
                    /* 配置分页样式 */
					'pager' => Yii::$app->params['pager'],
                    /* 定义列表格式 */
                    'columns' => $columns,
                ]); ?>
                </form>
            </div>
            <?php \yii\widgets\Pjax::end(); ?>
        </div>
    </div>
</div>
