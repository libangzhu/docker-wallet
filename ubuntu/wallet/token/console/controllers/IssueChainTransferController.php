<?php

namespace console\controllers;

use common\components\Tools;
use common\models\psources\CoinIssueRevokeRecord;
use common\models\psources\CoinPlatformWithHold;
use common\models\psources\WalletChain;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use common\models\psources\Coin;
use common\models\psources\CoinIssueChainRecord;
use common\models\psources\CoinIssueCoin;
use common\models\psources\CoinIssueRecord;
use common\models\psources\CoinIssueTransfer;
use common\service\chain33\Chain33Service;


class IssueChainTransferController extends Controller
{
    /**
     * 自动发币
     * @param $step 1 生成预创建token 的交易 2 生成完成创建token 的交易
     * @param $type 0 自动发布 1 人工发布
     * @return
     */
    public function actionSendTransaction($step, $type)
    {
        $manual_review = Yii::$app->redis->get('issue_chain_manual_review');
        if ('close' == $manual_review) {
            $status = CoinIssueCoin::STATUS_CONFIRM;
        } else {
            $status = CoinIssueCoin::STATUS_ALLOW;
        }

        $issue_coin_model = CoinIssueCoin::find()->select('id')->where(['status' => $status])->asArray()->all();
        if (false == $issue_coin_model) {
            echo date('Y-m-d H:i:s') . '暂无执行任务' . PHP_EOL;
            return 0;
        }
        $issue_coin_ids = ArrayHelper::getColumn($issue_coin_model, 'id');

        if (1 == $step) {
            $columns = ['id', 'pre_create_tx', 'issue_coin_id'];
            $condition = [
                'pre_send_transaction' => 'standby',
                'pre_query_transaction' => 'standby'
            ];
        }

        if (2 == $step) {
            $columns = ['id', 'finish_tx', 'issue_coin_id'];
            $condition = [
                'pre_query_transaction' => 'success',
                'finish_tx' => 'standby',
                'finish_send_transaction' => 'standby',
                'finish_query_transaction' => 'standby'
            ];
        }

        $model = CoinIssueChainRecord::find()->select($columns)->where($condition)->andWhere(['in', 'issue_coin_id', $issue_coin_ids])->all();

        if (false == $model) {
            echo date('Y-m-d H:i:s') . ' STEP: ' . $step . '暂无交易计划' . PHP_EOL;
            return 0;
        }


        foreach ($model as $val) {
            $platform = CoinPlatformWithHold::find()->select('platform')->where(['id' => $val->coin->chain_id])->one();
            $node = Yii::$app->params['chain_nodes'][strtoupper($platform->platform)];
            $chain_service = new Chain33Service($node);
            //执行1.2构造
            $result = $chain_service->createRawTokenFinishTx($val->coin->symbol, $val->coin->owner);
            if (null != $result['error']) {
                $status = CoinIssueCoin::STATUS_FAILED;
                $data = [
                    'status' => CoinIssueCoin::STATUS_FAILED,
                    'finish_query_transaction' => $result['msg']
                ];
                goto doEnd;
            }

            $txHex = $result['result'];
            $privkey = Yii::$app->params['chain_nodes'][strtoupper($platform->platform)]['privkey'];
            $expire = '1m';
            //执行1.2签名
            $signRawTx = $chain_service->signRawTx($privkey, $txHex, $expire);
            if (0 != $signRawTx['code']) {
                $status = CoinIssueCoin::STATUS_FAILED;
                $data = [
                    'finish_query_transaction' => $signRawTx['msg']
                ];
                goto doEnd;
            }
            //执行1.2交易
            $result = $chain_service->sendTransaction($signRawTx['result']);
            if (0 != $result['code']) {
                $status = CoinIssueCoin::STATUS_FAILED;
                $data = [
                    'finish_tx' => $signRawTx['result'],
                    'finish_query_transaction' => $result['msg']
                ];
                goto doEnd;
            }
            $status = CoinIssueCoin::STATUS_SUCCESS;
            $data = [
                'finish_tx' => $signRawTx['result'],
                'finish_send_transaction' => $result['result']
            ];

            doEnd :
            CoinIssueChainRecord::updateAll($data, [
                'id' => $val->id,
            ]);
            //交易失败
            if ($status == CoinIssueCoin::STATUS_FAILED) {
                CoinIssueCoin::updateAll(['status' => $status], [
                    'id' => $val->coin->id,
                ]);
            }
        }
        echo date('Y-m-d H:i:s') . '执行完成' . PHP_EOL;
        return 0;
    }

    public function actionQueryTransaction($step)
    {
        $redis = Yii::$app->redis;
        if (1 == $step) {
            $model = CoinIssueChainRecord::find()->where(['pre_query_transaction' => 'standby'])->all();
        }

        if (2 == $step) {
            $model = CoinIssueChainRecord::find()
                ->where(['pre_query_transaction' => 'success'])
                ->andWhere(['not in', 'finish_send_transaction', ['standby', 'success', 'failed']])
                ->andWhere(['finish_query_transaction' => 'standby'])
                ->all();
        }

        if (false == $model) {
            echo date('Y-m-d H:i:s') . ' STEP: ' . $step . '暂无完成的发送交易' . PHP_EOL;
            return 0;
        }


        $current_time = time();
        foreach ($model as $val) {
            $platform = CoinPlatformWithHold::find()->select('platform')->where(['id' => $val->coin->chain_id])->one();
            $node = Yii::$app->params['chain_nodes'][strtoupper($platform->platform)];
            if (1 == $step) {
                $result = $this->queryTransaction($node, $val->pre_send_transaction);
                #$result = $service->QueryTransaction($val->pre_send_transaction);
                $column = 'pre_query_transaction';
            }
            if (2 == $step) {
                $result = $this->queryTransaction($node, $val->finish_send_transaction);
                #$result = $service->QueryTransaction($val->finish_send_transaction);
                $column = 'finish_query_transaction';
            }
            if (isset($result['result']['actionName']) && 'unknown' == $result['result']['actionName']) {
                $data = [
                    $column => 'success'
                ];
                if (1 == $step) {
                    if (CoinIssueCoin::TYPE_YES == $val->coin->type) {
                        $data['finish_tx'] = 'success';
                        $data['finish_send_transaction'] = 'success';
                        $data['finish_query_transaction'] = 'success';
                        $status = CoinIssueCoin::STATUS_SUCCESS;
                    } else {
                        $status = CoinIssueCoin::STATUS_PEDDING;
                    }
                } else {
                    $status = CoinIssueCoin::STATUS_SUCCESS;
                }
                $redis->hdel(CoinIssueCoin::ISSUE_TOKEN, $val->id);
                goto doEnd;
            } else if (isset($result['result']['receipt']['ty']) && 2 == $result['result']['receipt']['ty']) {
                $data = [
                    $column => 'success'
                ];
                if (1 == $step) {
                    if (CoinIssueCoin::TYPE_YES == $val->coin->type) {
                        $data['finish_tx'] = 'success';
                        $data['finish_send_transaction'] = 'success';
                        $data['finish_query_transaction'] = 'success';
                        $status = CoinIssueCoin::STATUS_SUCCESS;
                    } else {
                        $status = CoinIssueCoin::STATUS_PEDDING;
                    }
                } else {
                    $status = CoinIssueCoin::STATUS_SUCCESS;
                }
                $redis->hdel(CoinIssueCoin::ISSUE_TOKEN, $val->id);
                goto doEnd;
            } else {
                $status = CoinIssueCoin::STATUS_FAILED;
                if (isset($result['result']['receipt']['logs'])) {
                    foreach ($result['result']['receipt']['logs'] as $log) {
                        if (isset($log['tyName']) && 'LogErr' == $log['tyName']) {
                            $data = [
                                $column => $log['log']
                            ];
                            break;
                        }
                    }
                } else {
                    $data = [
                        $column => $result['msg']
                    ];
                }
                if (CoinIssueCoin::TYPE_YES == $val->coin->type) {
                    $data['finish_tx'] = 'failed';
                    $data['finish_send_transaction'] = 'failed';
                    $data['finish_query_transaction'] = 'failed';
                }
                $cache_error_time = $redis->hget(CoinIssueCoin::ISSUE_TOKEN, $val->id);
                if (false == $cache_error_time) {
                    $redis->hmset(CoinIssueCoin::ISSUE_TOKEN, $val->id, $current_time);
                    continue;
                }

                if (($current_time - $cache_error_time) < 30) {
                    continue;
                }

                $redis->hdel(CoinIssueCoin::ISSUE_TOKEN, $val->id);
                goto doEnd;
            }
            doEnd :

            CoinIssueChainRecord::updateAll($data, [
                'id' => $val->id,
            ]);

            //查询后，交易失败
            if ($status == CoinIssueCoin::STATUS_FAILED) {
                CoinIssueCoin::updateAll(['status' => $status], [
                    'id' => $val->coin->id,
                ]);
            }

            $platform_ids = array($val->coin->platform_id);

            //1.1查询后，交易成功
            if (1 == $step) {
                CoinIssueCoin::updateAll(['status' => $status], [
                    'id' => $val->coin->id,
                ]);
                if (CoinIssueCoin::TYPE_YES == $val->coin->type) {
                    $params = [
                        'name' => $val->coin->name,
                        'nickname' => $val->coin->nickname,
                        'symbol' => $val->coin->symbol,
                        'introduction' => $val->coin->introduction,
                        'total' => (int)$val->coin->total,
                        'price' => isset($val->coin->platform->issue_charge) ? (int)$val->coin->platform->issue_charge : 0,
                        'category' => (int)$val->coin->category,
                        'owner' => $val->coin->owner,
                        'platform_id' => $platform_ids,
                        'platform' => $val->coin->chain->platform
                    ];
                    $this->syncCoin($params);
                }
            }

            //1.2查询后，交易成功
            if (2 == $step && $status == CoinIssueCoin::STATUS_SUCCESS) {
                CoinIssueCoin::updateAll(['status' => $status, 'charge_pay' => CoinIssueCoin::SUCCESS_PAY], [
                    'id' => $val->coin->id,
                ]);
                if (CoinIssueCoin::TYPE_NO == $val->coin->type) {
                    $params = [
                        'name' => $val->coin->name,
                        'nickname' => $val->coin->nickname,
                        'symbol' => $val->coin->symbol,
                        'introduction' => $val->coin->introduction,
                        'total' => (int)$val->coin->total,
                        'price' => isset($val->coin->platform->issue_charge) ? (int)$val->coin->platform->issue_charge : 0,
                        'category' => (int)$val->coin->category,
                        'owner' => $val->coin->owner,
                        'platform_id' => $platform_ids,
                        'platform' => $val->coin->chain->platform
                    ];
                    $this->syncCoin($params);
                }
            }
        }
        echo date('Y-m-d H:i:s') . '查询完成，同步完成' . PHP_EOL;
        return 0;
    }

    public function actionCancel()
    {
        $redis = Yii::$app->redis;
        $issue_coin_model = CoinIssueCoin::find()->where(['status' => CoinIssueCoin::STATUS_CANCEL])->all();
        if (false == $issue_coin_model) {
            echo date('Y-m-d H:i:s') . '暂无需要撤消的发行' . PHP_EOL;
            return 0;
        }
        $node = Yii::$app->params['chain_nodes']['STO'];
        $service = new Chain33Service($node);
        $current_time = time();
        foreach ($issue_coin_model as $val) {
            $result = $this->QueryTransaction($node, $val->revoke->revoke_send_transaction);
            #$result = $service->QueryTransaction($val->revoke->revoke_send_transaction);
            if (isset($result['result']['actionName']) && 'unknown' == $result['result']['actionName']) {
                $data = [
                    'revoke_query_transaction' => 'success'
                ];
                $status = CoinIssueCoin::STATUS_CANCEL_SUCCESS;
                $redis->hdel(CoinIssueCoin::REVOKE_TOKEN, $val->id);
                goto doEnd;
            } else if (isset($result['result']['receipt']['ty']) && 2 == $result['result']['receipt']['ty']) {
                $data = [
                    'revoke_query_transaction' => 'success'
                ];
                $status = CoinIssueCoin::STATUS_CANCEL_SUCCESS;
                $redis->hdel(CoinIssueCoin::REVOKE_TOKEN, $val->id);
                goto doEnd;
            } else {
                $status = CoinIssueCoin::STATUS_CANCEL_FAILED;
                if (isset($result['result']['receipt']['logs'])) {
                    foreach ($result['result']['receipt']['logs'] as $log) {
                        if (isset($log['tyName']) && 'LogErr' == $log['tyName']) {
                            $data = [
                                'revoke_query_transaction' => $log['log']
                            ];
                            break;
                        }
                    }
                } else {
                    $data = [
                        'revoke_query_transaction' => $result['msg']
                    ];
                }

                $cache_error_time = $redis->hget(CoinIssueCoin::REVOKE_TOKEN, $val->id);
                if (false == $cache_error_time) {
                    $redis->hmset(CoinIssueCoin::REVOKE_TOKEN, $val->id, $current_time);
                    continue;
                }

                if (($current_time - $cache_error_time) < 30) {
                    continue;
                }

                $redis->hdel(CoinIssueCoin::REVOKE_TOKEN, $val->id);

                goto doEnd;
            }

            doEnd :
            CoinIssueCoin::updateAll(['status' => $status], [
                'id' => $val->id,
            ]);
            CoinIssueRevokeRecord::updateAll($data, [
                'issue_coin_id' => $val->id,
            ]);
        }
        echo date('Y-m-d H:i:s') . '已撤消' . PHP_EOL;
        return 0;
    }

    public function actionUpdateChain()
    {
        $current_time =  date('Y-m-d H:i:s',strtotime('-10 minute'));
        $chains = CoinPlatformWithHold::find()
            ->where(['status' => CoinPlatformWithHold::STATUS_CREATEING, 'origin' => CoinPlatformWithHold::ORIGIN_USER])
            ->andWhere(['<', 'create_time', $current_time])
            ->all();
        
        if (empty($chains)) {
            echo date('Y-m-d H:i:s') . '无需更新的链' . PHP_EOL;
            return 0;
        }

        foreach ($chains as $chain) {
            $chain->setScenario(CoinPlatformWithHold::SCENARIOS_CONSOLE);
            $chain->status = CoinPlatformWithHold::STATUS_YES;
            $chain->save();
        }
        echo date('Y-m-d H:i:s') . '更新成功' . PHP_EOL;
        return 0;
    }

    public function actionAsyncCoin()
    {
        $chains = CoinPlatformWithHold::find()->select('host, platform')
            ->where(['status' => 1, 'origin' => CoinPlatformWithHold::ORIGIN_USER])
            ->asArray()
            ->all();
        foreach ($chains as $chain){
            $node = [
                'scheme' => 'http',
                'host' => $chain['host'],
                'port' => 8901
            ];
            $chain_service = new Chain33Service($node);
            $result = $chain_service->getTokens('token', 'GetTokens');
            if (null != $result['error']) continue;
            $tokens = isset($result['result']['tokens']) ? $result['result']['tokens'] : null;
            if (null == $tokens) continue;

            foreach ($tokens as $key => $token) {
                $model_coin = Coin::find()->where(['name' => $token['symbol'], 'platform' => $chain['platform'], 'chain' => 'BTY'])->one();
                if (empty($model_coin)) {
                    $model = new Coin();
                    $model->name = $token['symbol'];
                    $model->sid = $token['name'];
                    $model->nickname = ['ja' => '', 'en-US' => '', 'zh-CN' => ''];
                    $model->introduce = ['ja' => '', 'en-US' => '', 'zh-CN' => $token['introduction']];
                    $model->platform = $chain['platform'];
                    $model->publish_count = $token['total'];
                    $model->chain = 'BTY';
                    $model->platform_id = 1;
                    $model->treaty = 1;
                    $model->save();
                }
            }
        }
    }


    public function syncCoin($params = [])
    {
        $model_coin = Coin::find()->where(['name' => $params['symbol'], 'platform' => $params['platform']])->one();
        if (false == $model_coin) {
            $model = new Coin();
            $model->name = $params['symbol'];
            $model->sid = $params['name'];
            $model->nickname = ['ja' => '', 'en-US' => '', 'zh-CN' => $params['nickname']];
            $model->platform = $params['platform'];
            $model->publish_count = $params['total'] * 1e4;
            $model->chain = 'BTY';
            $model->platform_id = $params['platform_id'];
            $model->treaty = 1;
            $model->save();
        } else {
            $model_coin->publish_count = $model_coin->publish_count + $params['total'] * 1e4;
            $model_coin->save();
        }
    }

    public function syncRecord($params = [])
    {
        $coin_issue_record = new CoinIssueRecord();
        $coin_issue_record->platform_id = $params['platform_id'];
        $coin_issue_record->total = $params['total'];
        $coin_issue_record->save();
    }

    protected function queryTransaction($node_params, $send_result)
    {
        static $result = [];
        $service = new Chain33Service($node_params);
        $result = $service->QueryTransaction($send_result);
        if (isset($result['result']['receipt']) && is_array($result['result']['receipt']['logs'])) {
            foreach ($result['result']['receipt']['logs'] as $log) {
                if (isset($log['tyName']) && 'logerr' == strtolower($log['tyName'])) {
                    return $result;
                }
            }
        }

        if (isset($result['result']['tx']['next'])) {
            $this->queryTransaction($node_params, $result['result']['tx']['next']);
        }
        return $result;
    }
}