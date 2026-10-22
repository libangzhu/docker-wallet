<?php

use yii\helpers\Html;
use yii\grid\GridView;

$columns = [
    [
        'class' => \common\core\CheckboxColumn::className(),
        'name'  => 'id',
        'options' => ['width' => '20px;'],
        'checkboxOptions' => function ($model, $key, $index, $column) {
            return ['value' => $key,'label'=>'<span></span>','labelOptions'=>['class' =>'mt-checkbox mt-checkbox-outline','style'=>'padding-left:19px;']];
        }
    ],
    [
        'header' => 'ID',
        'attribute' => 'id',
        'options' => ['width' => '40px;']
    ],
    [
        'header' => '配置名称',
        'options' => ['width' => '120px;'],
        'attribute' => 'name'
    ],
    [
        'header' => '名称',
        'attribute' => 'title',
		'options' => ['width' => '120px;'],
    ],
    [
        'header' => '类型',
		'options' => ['width' => '50px;'],
        'content' => function($model, $key){
            switch ($model['type']){
                case 0 :
                    return '数字'; 
                case 1 :
                    return '字符';
                case 2 :
                    return '文本';
                case 3 :
                    return '数组';
                case 4 :
                    return '枚举';
            }
        }
    ],
    [
        'label' => '分组',
        'options' => ['width' => '60px;'],
        'value' => function($model){
            switch ($model['group']){
                case 0 :
                    return ''; 
                case 1 :
                    return '基本';
                case 2 :
                    return '内容';
                case 3 :
                    return '用户';
                case 4 :
                    return '系统';
            }
        }
    ],
    [
        'label' => '排序',
        'value' => 'sort',
        'options' => ['width' => '50px;'],
    ],
    [
        'label' => '状态',
        'options' => ['width' => '50px;'],
        'content' => function($model){
            if ($model['status']) {
                return '正常';
            }
            return '<span style="color:#f00;">隐藏</span>';
        }
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
<span style="color:#f00;">开发者配置菜单目录</span>
<div class="row admin-config-index">
    <div class="col-md-12">
            <?php \common\widgets\Pjax::begin(['options'=>['id'=>'pjax-container']]); ?>
            <?=Html::a(Yii::$app->params['config_page']['add'] . '<i class="fa fa-plus"></i>',['add'],['class'=>"btn ".Yii::$app->params['config_page']['color_opt']])?>

            <div class="table-container">
                <form class="ids">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider, // 列表数据
                    'filterModel' => $searchModel, // 搜索模型
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