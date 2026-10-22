<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-07 15:56:52
 * @authors rlgy <rlgyzhcn@qq.com>
 */

return [
    'user.passwordResetTokenExpire' => 3600,

    'admin'                         => 1,

    /* 后台页面属性配置 */
    'config_page'                   => [
        'add'       => '添加',
        'delete'    => '删除',
        'update'    => '更新',
        'tool_bar'  => '工具箱',
        'export'    => '导出Excel',
        'others'    => '其它',
        'details'   => '详情',
        'color_opt' => 'btn-primary',
        'color_bar' => 'btn-primary',
    ],

    'pager'                         => [
        'options'        => ['class' => 'pagination', 'style' => 'visibility: visible;'],
        'firstPageLabel' => "首页",
        'prevPageLabel'  => '上一页',
        'nextPageLabel'  => '下一页',
        'lastPageLabel'  => '末页',
    ],
    'adminEmail'                    => 'admin@example.com',
    /* 后台错误页面模板 */
    'action_error'                  => '@backend/views/public/error.php', // 默认错误跳转对应的模板文件
    'action_success'                => '@backend/views/public/success.php', // 默认成功跳转对应的模板文件
];
