<?php
return [
    'adminEmail'                    => 'admin@example.com',
    'supportEmail'                  => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,

    'admin'       => 1,

    /* 后台页面属性配置 */
    'config_page' => [
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

    'pager'             => [
        'options'        => ['class' => 'pagination', 'style' => 'visibility: visible;'],
        'firstPageLabel' => "首页",
        'prevPageLabel'  => '上一页',
        'nextPageLabel'  => '下一页',
        'lastPageLabel'  => '末页',
    ],

    /** 非小号数据爬取地址 */
    'feixiaohao_domain' => 'https://www.feixiaohao.com',
    'feixiaohao_page'   => [
        'assets'     => '/assets/',//代币
        'currencies' => '/currencies/',//币种详情
        'coinmarket' => '/coinmarket/',//上市交易所
    ],

    /** 爬虫数据缓存时间 */
    'curl_cache_time'   => [
        'exchange'       => 3600 * 3 * 24,
        'exchange_count' => 3600 * 3 * 24,
        'quotation'      => 60,
    ],

    'http_proxy' => [
        'host' => '127.0.0.1',
        'port' => '8118',
        'use'  => true
    ]
];
