<?php

namespace console\controllers;

use common\business\Chain33Business;
use common\models\psources\CoinGameBet;
use common\service\chain33\Chain33Service;
use yii\console\Controller;
use Yii;

class GameBetController extends Controller
{
    /**
     * 获取游戏状态
     *
     * @return array
     */
    public function actionGameStatus()
    {
        $nodes = \Yii::$app->params['chain_parallel']['wasm'];
        if (empty($nodes)) {
            echo date('Y-m-d H:i:s') . '无节点' . PHP_EOL;
            return 0;
        }
        foreach ($nodes as $key => $node) {
            $service = new Chain33Business();
            $result = $service->getGameStatus($node);
            if (0 !== $result['code']) {
                echo $key . ':' . date('Y-m-d H:i:s') . $result['msg'] . PHP_EOL;
                continue;
            }

            $queryResultItems = $result['result'] ?? [];
            if (empty($queryResultItems)) {
                echo $key . ':' . date('Y-m-d H:i:s') . 'error' . PHP_EOL;
                continue;
            }

            $resultJSON = json_decode($queryResultItems['queryResultItems'][0]['resultJSON'], true);
            $current_round = $resultJSON['current_round'];
            $cache_current_round = Yii::$app->redis->get('chain33_game_bet_status_' . $key);
            if (empty($cache_current_round)) {
                $cache_current_round = CoinGameBet::find()->where(['platform' => $key])->max('round');
                Yii::$app->redis->set('chain33_game_bet_status_' . $key, $cache_current_round, 'EX', 300);
            }
            $cache_current_round = (false == $cache_current_round ? 0 : $cache_current_round);
            if ($cache_current_round >= $current_round) {
                echo $key . ':' . date('Y-m-d H:i:s') . '数据已为最新' . PHP_EOL;
                continue;
            }
            Yii::$app->redis->set('chain33_game_bet_status_' . $key, $current_round, 'EX', 300);
            $result = $service->getBetStatus($cache_current_round, $current_round, '', $node);
            if (0 !== $result['code']) {
                echo $key . ':' . date('Y-m-d H:i:s') . '数据错误' . PHP_EOL;
                continue;
            }

            $queryResultItems = $result['result'] ?? [];
            if (empty($queryResultItems)) {
                echo $key . ':' . date('Y-m-d H:i:s') . '数据错误' . PHP_EOL;
                continue;
            }
            $platform = $key;
            foreach ($queryResultItems['queryResultItems'] as $key => $val) {
                if (false == $val['found']) continue;
                $resultArr = json_decode($val['resultJSON'], true);
                $datas[] = [
                    $resultArr['round'], $resultArr['player'], $resultArr['amount'], $resultArr['height'], $resultArr['guess_num'], $resultArr['rand_num'], $resultArr['player_win'], $platform
                ];
            }
            CoinGameBet::loadArray($datas);
            echo $platform . ':' . date('Y-m-d H:i:s') . '数据更新成功' . PHP_EOL;
            continue;
        }
        return 0;
    }

    public function actionBetUpdate()
    {
        $service = new Chain33Business();
        $node_params = [
            'scheme' => 'https',
            'host' => 'jiedian1.bityuan.com',
            'port' => 8801
        ];
        $result = $service->getLastHeader($node_params);
        $main_height = isset($result['result']['height']) ? $result['result']['height'] : 0;
        $safe_main_height = $main_height - 36;
        $nodes = \Yii::$app->params['chain_parallel']['wasm'];
        if (empty($nodes)) {
            echo date('Y-m-d H:i:s') . '无节点' . PHP_EOL;
            return 0;
        }

        foreach ($nodes as $key => $node) {
            $service = new Chain33Service($node);
            $result = $service->getLastHeader();
            $height = $result['result']['height'];
            $param = [
                'start' => $height - 36,
                'end' => $height,
                'isDetail' => false
            ];
            $main_info = $service->chainQuery('paracross', 'GetBlock2MainInfo', $param);
            if (!isset($main_info['result']['items'])) continue;
            $items = $main_info['result']['items'];
            $items = $this->arraySort($items, 'mainHeight');
            $safe_height = 0;
            foreach ($items as $item) {
                if ($safe_main_height > $item['mainHeight']) {
                    $safe_height = $item['height'];
                    break;
                }
            }
            echo $key . ':' . date('Y-m-d H:i:s') . '当前主链高度为:' . $main_height . ';当前平行链高度为:' . $height . ';当前安全高度为:' . $safe_height . PHP_EOL;
            $models = CoinGameBet::find()->select('round')->where([
                'and',
                ['valid' => CoinGameBet::VAILD_FALSE],
                ['<=', 'height', $safe_height],
                ['platform' => $key]
            ])->all();
            if (empty($models)) {
                echo $key . ':' . date('Y-m-d H:i:s') . '无需更新的数据' . PHP_EOL;
                continue;
            }
            $valid_arr = [];
            foreach ($models as $model) {
                $valid_arr[] = $model->round;
            }
            $business = new Chain33Business();
            $result = $business->getBetStatus('', '', $valid_arr, $node);
            if (0 !== $result['code']) {
                echo $key . ':' . date('Y-m-d H:i:s') . '数据错误' . PHP_EOL;
                continue;
            }

            $queryResultItems = $result['result'] ?? [];
            if (empty($queryResultItems)) {
                echo $key . ':' . date('Y-m-d H:i:s') . '数据错误' . PHP_EOL;
                continue;
            }
            $platform = $key;
            foreach ($queryResultItems['queryResultItems'] as $key => $val) {
                if (false == $val['found']) continue;
                $resultArr = json_decode($val['resultJSON'], true);
                CoinGameBet::updateAll([
                    'amount' => $resultArr['amount'],
                    'height' => $resultArr['height'],
                    'guess_num' => $resultArr['guess_num'],
                    'rand_num' => $resultArr['rand_num'],
                    'player_win' => $resultArr['player_win'],
                    'valid' => CoinGameBet::VAILD_TRUE
                ], [
                    'round' => $resultArr['round'],
                    'platform' => $platform
                ]);
            }
            echo $platform . ':' . date('Y-m-d H:i:s') . '数据更新成功' . PHP_EOL;
            continue;
        }
        return 0;
    }


    protected function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
}