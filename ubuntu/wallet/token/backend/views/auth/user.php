<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;


$columns = [
    [
        'class'           => \common\core\CheckboxColumn::className(),
        'name'            => 'id',
        'options'         => ['width' => '20px;'],
        'checkboxOptions' => function ($model, $key, $index, $column) {
            return ['value' => $key, 'label' => '<span></span>', 'labelOptions' => ['class' => 'mt-checkbox mt-checkbox-outline', 'style' => 'padding-left:19px;']];
        }
    ],
    [
        'header'    => 'UID',
        'attribute' => 'uid',
        'options'   => ['width' => '50px;']
    ],
    [
        'header'    => '用户名',
        'attribute' => 'username',
        'options'   => ['width' => '100px;']
    ],
    [
        'header'    => '邮箱',
        'attribute' => 'email',
        'options'   => ['width' => '150px;']
    ],
    [
        'header'    => '手机',
        'attribute' => 'mobile',
        'options'   => ['width' => '150px;']
    ],
    [
        'header'    => '最后登录时间',
        'attribute' => 'last_login_time',
        'options'   => ['width' => '150px;'],
        'format'    => ['date', 'php:Y-m-d H:i:s']
    ],
    [
        'header'    => '最后登录IP',
        'attribute' => 'last_login_ip',
        'options'   => ['width' => '150px;'],
        'content'   => function ($model) {
            return long2ip($model['last_login_ip']);
        }
    ],
    [
        'header'    => '状态',
        'attribute' => 'status',
        'options'   => ['width' => '60px;'],
        'content'   => function ($model) {
            return $model['status'] ? '正常' : '<span style="color: red">删除</span>';
        }
    ],
    [
        'class'    => 'yii\grid\ActionColumn',
        'header'   => '操作',
        'template' => '{edit} {auth}',
        'options'  => ['width' => '200px;'],
        'buttons'  => [
            'edit'   => function ($url, $model, $key) {
                return Html::a('<i class="fa fa-edit"></i> 编辑', ['admin/edit', 'uid' => $key], [
                    'title' => Yii::t('app', '编辑'),
                    'class' => 'btn btn-xs blue'
                ]);
            },
            'auth'   => function ($url, $model, $key) {
                return Html::a('<i class="fa fa-user"></i> 授权', ['admin/auth', 'uid' => $key], [
                    'title' => Yii::t('app', '授权'),
                    'class' => 'btn btn-xs blue'
                ]);
            },
            'delete' => function ($url, $model, $key) {
                return Html::a('<i class="fa fa-times"></i>', ['admin/delete', 'id' => $key], [
                    'title' => Yii::t('app', '删除'),
                    'class' => 'btn btn-xs red ajax-get confirm'
                ]);
            }
        ],
    ],
];

?>
<div class="row admin-auth-user">
    <div class="col-md-12">
        <a href="/admin/admin/add?role=<?= $role ?>">
            <button class="btn btn-primary"><i class="fa fa-plus"></i>添加</button>
        </a>
        <div class="table-container">
            <form class="ids">
                <?= GridView::widget([
                    'dataProvider'   => $dataProvider, // 列表数据
                    //'filterModel' => $searchModel, // 搜索模型
                    'emptyText'      => Html::a('<i class="fa fa-mail-reply"></i> 无数据', Yii::getAlias('@web') . '/auth/index'),
                    'options'        => ['class' => 'grid-view'],
                    /* 表格配置 */
                    'tableOptions'   => ['class' => 'table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer'],
                    /* 重新排版 摘要、表格、分页 */
                    'layout'         => '{items}<div class=""><div class="col-md-5 col-sm-5">{summary}</div><div class="col-md-7 col-sm-7"><div class="dataTables_paginate paging_bootstrap_full_number" style="text-align:right;">{pager}</div></div></div>',
                    /* 配置摘要 */
                    'summaryOptions' => ['class' => 'pagination'],
                    /* 配置分页样式 */
                    'pager'          => Yii::$app->params['pager'],
                    /* 定义列表格式 */
                    'columns'        => $columns,
                ]); ?>
            </form>
        </div>
    </div>
</div>

