<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/6/20
 * Time: 11:11
 */

return [
    'auth_all' => [
        [
            'class' => 'wallet\controllers\UserController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\WalletController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\MonitorController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\IssueChainController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\IssueCoinController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\ExploreAppController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\ExploreBannerController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\OssController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\CoinCurrencyController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\BannerController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\NoticeController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\FlashController',
            'actions' => []
        ], [
            'class' => 'wallet\controllers\AssetController',
            'actions' => []
        ]
    ],
    'auth_common' => [
        [
            #'class' => 'api\controllers\AirDropController',
            #'actions' => []
        ]
    ],

    'user_auth' => [
        //bwallet
        1 => [
            1 => 'auth_all',
            2 => 'auth_all'
        ],
        //DAG
        9 => [
            54 => 'auth_all',
        ],
        //TS
        10 => [
            31 => 'auth_all'
        ],
        //GBT
        12 => [
            34 => 'auth_all'
        ],
        //SFT
        13 => [
            41 => 'auth_all'
        ],
        //HL
        15 => [
            32 => 'auth_all'
        ],
        //BN
        17 => [
            33 => 'auth_all'
        ],
        //BBH
        20 => [
            44 => 'auth_all'
        ],
        //CIC
        24 => [
            39 => 'auth_all'
        ],
        //Bavala
        26 => [
            42 => 'auth_all'
        ],
        //CKWallet
        30 => [
            48 => 'auth_all'
        ],
        //火焰山
        31 => [
            49 => 'auth_all'
        ],
        //SEC
        33 => [
            51 => 'auth_all'
        ],
        //SUM
        34 => [
            50 => 'auth_all'
        ],
        //上链通
        35 => [
            53 => 'auth_all'
        ],
        //Zeuswallet
        36 => [
            55 => 'auth_all'
        ],
        //扑克钱包
        38 => [
            56 => 'auth_all'
        ],
        //贵州车秘
        41 => [
            58 => 'auth_all'
        ],
        //GM云
        42 => [
            59 => 'auth_all'
        ],
        //币钱包Test
        43 => [
            57 => 'auth_all'
        ],
        //倍多链
        44 => [
            60 => 'auth_all'
        ],
        //Hitoken
        45 => [
            61 => 'auth_all'
        ],
        //佰特链
        47 => [
            62 => 'auth_all'
        ],
        //链销钱包
        48 => [
            63 => 'auth_all'
        ],
        //太庆能源
        49 => [
            64 => 'auth_all'
        ],
        //青鸟社交
        50 => [
            66 => 'auth_all'
        ],
        //电池银行
        51 => [
            65 => 'auth_all'
        ],
        //正元链
        52 => [
            68 => 'auth_all'
        ],
        //乐乐食品
        53 => [
            67 => 'auth_all'
        ],
        //Honor Decent
        54 => [
            69 => 'auth_all'
        ],
        //巴马水
        58 => [
            70 => 'auth_all'
        ],
        //离九紫
        60 => [
            73 => 'auth_all'
        ],
        //离九紫
        60 => [
            73 => 'auth_all'
        ],
        //匿名公链
        62 => [
            75 => 'auth_all'
        ]
    ]
];
