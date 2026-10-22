<?php

namespace console\controllers;

use yii\console\Controller;
use common\service\es\Elastic;

class IpExceptionController extends Controller
{
    public function actionIndex()
    {
        $now = time();
        $elastic = new Elastic('106.15.39.94:9200');
        $params = [
            'index' => 'bwallet_access',
            'body' => [
                "size" => 0,
                "aggs" => [
                    "ips" => [
                        "terms" => [
                            "field" => "clientip.keyword",
                            "size" => 50,
                            "order" => [
                                "_count" => "desc"
                            ]
                        ],
                        "aggs" => [
                            "request" => [
                                "significant_terms" => [
                                    "field" => "request.keyword",
                                    "size" => 10,
                                ]
                            ]
                        ]
                    ]
                ],
                "query" => [
                    "bool" => [
                        "must" => [
                            [
                                "range" => [
                                    "@timestamp" => [
                                        'gte' => ($now - 300) * 1000,
                                        'lt' => $now * 1000,
                                        "format" => "epoch_millis"
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
        $elastic->connect();
        $response = $elastic->search($params);

        $hits = $response['hits'] ?? null;
        $total = $hits['total'];
        if (empty($total)) {
            return false;
        }

        $buckets = $response['aggregations']['ips']['buckets'] ?? null;
        if (empty($buckets)) {
            return false;
        }

        //$redis_ticker = \Yii::$app->redis_es;
        $limit = \Yii::$app->params['api_ip_limit']['limit'];
        $white_list = \Yii::$app->params['api_ip_limit']['white_list'];
        $ips = '';
        foreach ($buckets as $key => $val) {
            if (in_array($val['key'], $white_list)) continue;
            if (!isset($val['request']['buckets'])) continue;
            foreach ($val['request']['buckets'] as $b => $item) {
                if ($item['doc_count'] > $limit) {
                    $ips .= $val['key'] . ',';
                }
//                if (strpos($item['key'],'/interface/ticker/hot-ticker') !== false) {
//                    if ($item['doc_count'] > $limit) {
//                        foreach ($val['request']['buckets'] as $bucket) {
//                            $redis_ticker->hmset($val['key'], $bucket['key'], $bucket['doc_count']);
//                        }
//                        $ips .= $val['key'] . ',';
//                    }
//                }
            }
        }

        $ips = substr($ips, 0, -1);
        if (!empty($ips)) {
            $file = fopen("/data_wallet/static/wallet_interface_ip_limit.txt", "w");
            $ip_arr = explode(',', $ips);
            foreach ($ip_arr as $key => $ip) {
                fwrite($file, $ip . "\n");
            }
            fclose($file);
        };
        return false;
    }
}