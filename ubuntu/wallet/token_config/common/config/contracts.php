<?php

return [
    'handleTableAndDesc' => [
        'air-drop' => [
            'get-balance' => [
                'get' => '空投地址余额',
                'table' => '区块链'
            ],
            'expenses' => [
                'get' => '查询总空投金额',
                'table' => 'wallet_airdrop_apply_record'
            ],
            'index' => [
                'get' => '查询空投申请列表',
                'table' => 'wallet_airdrop_apply'
            ],
            'apply' => [
                'get' => '查询每个树莓派的空投记录',
                'table' => 'wallet_airdrop_apply'
            ],
        ],
        'banner' => [
            'index' => [
                'get' => '查询Banner列表',
                'post' => '新增Banner',
                'table' => 'coin_banner_item'
            ],
            'update' => [
                'get' => '查看Banner详情',
                'put' => '更新Banner',
                'table' => 'coin_banner_item'
            ],
            'remove' => [
                'delete' => '删除Banner',
                'table' => 'coin_banner_item'
            ],
        ],
        'explore-app' => [
            'apps' => [
                'get' => '查询探索应用列表',
                'post' => '新增应用',
                'table' => 'explore_app'
            ],
            'app-update' => [
                'get' => '应用详情',
                'put' => '更新应用',
                'table' => 'explore_app'
            ],
            'app-remove' => [
                'delete' => '删除应用 ',
                'table' => 'explore_app'
            ],
            'category' => [
                'get' => '查询应用类别列表',
                'post' => '新增应用类别',
                'table' => 'explore_app_category'
            ],
            'category-update' => [
                'get' => '应用类别详情',
                'put' => '更新应用类别',
                'table' => 'explore_app_category'
            ],
            'category-remove' => [
                'delete' => '删除应用类别',
                'table' => 'explore_app_category'
            ],
        ],
        'explore' => [
            'index' => [
                'get' => '查询探索Banner列表',
                'post' => '新增探索Banner',
                'table' => 'coin_banner_item'
            ],
            'update' => [
                'get' => '探索Banner详情',
                'put' => '更新探索Banner',
                'table' => 'coin_banner_item'
            ],
            'remove' => [
                'delete' => '删除探索Banner ',
                'table' => 'coin_banner_item'
            ],
        ],
        'flash' => [
            'flash-list-config' => [
                'get' => '查询闪兑获取币种支持的交易对',
                'table' => 'flash'
            ],
            'flash-default-config' => [
                'get' => '查询闪兑获取默认的币种列表',
                'table' => 'flash'
            ],
            'flash-config' => [
                'post' => '新增闪兑交易对',
                'put' => '更新兑交易对',
                'delete' => '删除闪兑交易对',
                'table' => 'flash'
            ],
            'flash-clone-config' => [
                'post' => '闪兑克隆交易对',
                'table' => 'flash'
            ],
            'index' => [
                'get' => '查询交易对列表',
                'table' => 'flash'
            ]
        ],
        'issue-chain' => [
            'index' => [
                'get' => '查询可发行链列表',
                'table' => 'coin_platform'
            ],
            'chain-info' => [
                'get' => '查询发行链信息',
                'table' => 'coin_platform'
            ],
            'set-charge' => [
                'post' => '设置手续费',
                'table' => 'coin_platform'
            ],
            'manage-review' => [
                'post' => '人工审核开启/关闭',
                'table' => 'coin_platform'
            ],
            'get-tokens' => [
                'get' => '查询所有预创建的token',
                'table' => '区块链'
            ]
        ],
        'issue-coin' => [
            'apply-list' => [
                'get' => '查询发币申请列表',
                'table' => 'coin_issue_coin'
            ],
            'apply-detail' => [
                'get' => '查看申请详情',
                'table' => 'coin_issue_coin'
            ],
            'verify' => [
                'post' => '申请操作管理',
                'table' => 'coin_issue_coin'
            ]
        ],
        'monitor' => [
            'list' => [
                'get' => '余额查询',
                'table' => 'trustee_ship'
            ],
        ],
        'notice' => [
            'index' => [
                'get' => '查询公告列表',
                'post' => '新增公告',
                'table' => 'notice'
            ],
            'update' => [
                'get' => '公告详情',
                'put' => '更新公告',
                'table' => 'notice'
            ],
            'remove' => [
                'delete' => '删除公告',
                'table' => 'notice'
            ]
        ],
        'oss' => [
            'upload' => [
                'post' => '上传文件/图片',
                'table' => 'oss'
            ]
        ],
        'user' => [
            'login' => [
                'post' => '登录了管理后台',
                'table' => 'admin'
            ],
            'user-info' => [
                'get' => '通过token查询了用户信息',
                'table' => 'admin'
            ],
            'user-list' => [
                'get' => '查询用户列表',
                'table' => 'trustee_ship',
            ],
            'manual-list' => [
                'get' => '查询人工实名全部列表',
                'table' => 'trustee_ship',
            ],
            'verify' => [
                'get' => '用户审核',
                'post' => '用户审核',
                'table' => 'trustee_ship'
            ]
        ],
        'wallet' => [
            'list' => [
                'get' => '查询钱包名称',
                'table' => 'coin_platform'
            ],
            'wallet-balance' => [
                'get' => '托管热/冷钱包监控',
                'table' => 'trustee_ship'
            ],
            'user-asset' => [
                'get' => '查看用户资产',
                'table' => 'trustee_ship'
            ],
            'currency' => [
                'get' => '查询支持的计价货币列表',
                'post' => '新增计价货币',
                'put' => '更新计价货币',
                'delete' => '删除计价货币',
                'table' => 'coin_currency'
            ]
        ],
        'asset' => [
            'index' => [
                '/backend/coin/coin/records' => '用户交易记录',
                '/backend/coin/coin/transfer' => '后台划转资产',
                '/backend/config/edit-withdraw-limit' => '编辑提币审核配置',
                '/backend/config/withdraw-limit' => '提币审核配置列表',
                '/backend/coin/withdraw-review-op' => '提币审批',
                '/backend/coin/withdraw-review-list' => '提币审批列表',
                '/backend/sbd/list' => '话费列表',
                '/backend/sbd/add' => '新增话费配置',
                '/backend/sbd/edit' => '编辑话费配置',
                '/backend/sbd/del' => '删除话费配置'
            ],
        ]
    ]
];