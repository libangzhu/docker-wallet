<?php

namespace console\controllers;

use common\components\Tools;
use common\models\psources\AirDropRulePool;
use Yii;
use yii\console\Controller;
use common\models\psources\AirDrop;
use common\service\chain33\Chain33Service;
use common\models\psources\AirDropApplyRecord;
use common\models\psources\CoinAirDropTransfer;
use common\models\psources\AirDropApplyTransferRecord;
use yii\db\Exception;

class AirDropController extends Controller
{
    /**
     * 统计每个树莓派达标次数，已领取，未领取
     * @return
     */
    public function actionStatistics()
    {
        $expiry_date = date("Y-m-d", strtotime("+1 day"));
        $apply = AirDrop::find()->select('id, identifier, finish_time')->all();

        if (empty($apply)) {
            return 0;
        }
        $redis = Yii::$app->redis_app;
        foreach ($apply as $val) {
            go(function () use ($val, $redis, $expiry_date) {
                \Co::sleep(0.5);
                if (!empty($val->record)) {

                    //达标次数
                    $reach = AirDropApplyRecord::find()
                        ->where(['apply_id' => $val['id'], 'reach' => AirDropApplyRecord::REACH_YES])
                        ->andWhere(['<', 'create_time', $expiry_date])
                        ->sum('reach');
                    $reach = empty($reach) ? 0 : (int)$reach;

                    //已领取次数
                    $reached_times = AirDropApplyRecord::find()
                        ->where(['apply_id' => $val['id'], 'draw_status' => AirDropApplyRecord::STATUS_DRAW_SUEEESS])
                        //->andWhere(['<', 'create_time', $expiry_date])
                        ->count();
                    $reached_times = empty($reached_times) ? 0 : (int)$reached_times;

                    //已领取空投总量
                    $draw_success = AirDropApplyRecord::find()
                        ->where(['apply_id' => $val['id'], 'draw_status' => AirDropApplyRecord::STATUS_DRAW_SUEEESS])
                        ->andWhere(['<', 'create_time', $expiry_date])
                        ->sum('amount');
                    $draw_success = empty($draw_success) ? 0 : (int)$draw_success;

                    //总收益
                    $income = AirDropApplyRecord::find()
                        ->where(['apply_id' => $val['id'], 'reach' => AirDropApplyRecord::REACH_YES])
                        ->andWhere(['<', 'create_time', $expiry_date])
                        ->sum('amount');
                    $income = empty($income) ? 0 : (int)$income;

                    //未领取
                    $un_draw = $income - $draw_success;

                    $redis->hmset('airdrop:' . $val->identifier, 'reach', $reach, 'reached_times', $reached_times, 'draw_success', $draw_success, 'income', $income, 'un_draw', $un_draw);
                }
            });
        }
        return 0;
    }

    /**
     * 获取矿工地址的对应的冷钱包地址
     * @return
     */
    public function actionMinerSourceList()
    {
        $model = AirDrop::find()->where(['wallet_address' => ''])->asArray()->all();
        if (empty($model)) {
            return 0;
        }
        $service = new Chain33Service();
        foreach ($model as $key => $val) {
            go(function () use ($val, $service) {
                \Co::sleep(0.5);
                $playload = [
                    'data' => $val['miner_address']
                ];
                $result = $service->chainQuery('ticket', 'MinerSourceList', $playload);
                if (0 == $result['code']) {
                    $wallet_address = isset($result['result']['datas'][0]) ? $result['result']['datas'][0] : '';
                    if (!empty($wallet_address)) {
                        $current_model = AirDrop::findOne($val['id']);
                        $current_model->wallet_address = $wallet_address;
                        $current_model->save();
                    }
                }
            });
        }
    }

    /**
     * 达标初始化
     * 通过矿主地址查询是否有余额，有则生成记录
     * @return
     */
    public function actionReachInit()
    {
        $applies = AirDrop::find()->where(['reach_init' => AirDrop::REACH_INIT_NO])->all();
        if (empty($applies)) return 0;
        $service = new Chain33Service();
        $execer = 'ticket';

        foreach ($applies as $apply) {
            $exist = AirDropRulePool::find()->where(['identifier' => $apply->identifier])->one();
            if (false == $exist || false == $apply->wallet_address) continue;
            $result = $service->getBalance($apply->wallet_address, $execer);
            if (0 != $result['code']) continue;
            $balance = $result['result'][0]['balance'] ?? 0;
            $frozen = $result['result'][0]['frozen'] ?? 0;
            if (($balance + $frozen) / 1e8 < 3000) continue;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $apply->reach_init = AirDrop::REACH_INIT_YES;
                $apply->save();
                $time = $exist->rule->duration - 1;
                $create_time = date('Y-m-d');
                $finish_time = date('Y-m-d', strtotime("+$time day"));
                $expiry_date = Tools::getDatesFromRange($create_time, $finish_time);
                $apply_record_model = new AirDropApplyRecord();
                foreach ($expiry_date as $key => $val) {
                    $apply_record_model->setIsNewRecord(true);
                    $apply_record_model->apply_id = $apply->id;
                    if (0 == $key) {
                        $apply_record_model->reach = AirDropApplyRecord::REACH_YES;
                    } else {
                        $apply_record_model->reach = AirDropApplyRecord::REACH_NO;
                    }
                    $apply_record_model->amount = $exist->rule->amount;
                    $apply_record_model->token = $exist->rule->token;
                    $apply_record_model->draw_status = AirDropApplyRecord::STATUS_UNDRAW;
                    $apply_record_model->create_time = $val;
                    $apply_record_model->save() && $apply_record_model->id = 0;;
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollback();
                $msg = $e->getMessage();
                echo date('Y-m-d H:i:s') . $msg . PHP_EOL;
            }
        }
    }

    /**
     * 查询地址余额
     * 通过矿主地址查询是否有冻结资金，有BTY代表达标
     * @return
     */
    public function actionReach()
    {
        $begin = date("Y-m-d", time()) . " 00:00:00";
        $end = date("Y-m-d", time()) . " 23:59:59";

        $record = AirDropApplyRecord::find()
            ->where(['reach' => AirDropApplyRecord::REACH_NO])
            ->andWhere(['between', 'create_time', $begin, $end])
            ->all();
        if (empty($record)) return 0;

        $service = new Chain33Service();
        $execer = 'ticket';
        foreach ($record as $val) {
            go(function () use ($val, $service, $execer) {
                \Co::sleep(0.5);
                $result = $service->getBalance($val->apply->wallet_address, $execer);
                if (0 == $result['code']) {
                    $balance = $result['result'][0]['balance'] ?? 0;
                    $frozen = $result['result'][0]['frozen'] ?? 0;
                    if (($balance + $frozen) / 1e8 >= 3000) {
                        $val->reach = AirDropApplyRecord::REACH_YES;
                        $val->save();
                    }
                }
            });
        }
    }

    /**
     * 领取符合条件的记录
     * @return
     */
    public function actionDraw()
    {
        $record = AirDropApplyRecord::find()->where(['reach' => AirDropApplyRecord::REACH_YES, 'draw_status' => AirDropApplyRecord::STATUS_DRAWING])->all();
        if (empty($record)) {
            return 0;
        }

        $node_params = Yii::$app->params['air_drop'];
        $service = new Chain33Service($node_params);
        foreach ($record as $val) {
            go(function () use ($val, $service) {
                \Co::sleep(0.5);
                $amount = $val->amount * 1e8;
                $fee = 1;
                $note = '';
                $execer = 'token';
                $isToken = true;
                $tokenSymbol = $val->token;

                $val->draw_status = AirDropApplyRecord::STATUS_DRAW_SUEEESS;

                $createTokenRawTransaction = $service->createTokenRawTransaction($val->apply->wallet_address, $amount, $isToken, $tokenSymbol, $fee, $note, $execer);

                if (0 != $createTokenRawTransaction['code']) {
                    $val->draw_status = AirDropApplyRecord::STATUS_DRAW_FAIL;
                    $transfer_record['create_result'] = $createTokenRawTransaction['msg'];
                    goto doEnd;
                }

                $privkey = '4ab1b1253e8984acf95797eed52ad9d8247ee96bd9bb49bb08a742d2c4477c0b';
                $expire = '1m';

                $signRawTx = $service->signRawTx($privkey, $createTokenRawTransaction['result'], $expire);
                if (0 != $signRawTx['code']) {
                    $val->draw_status = AirDropApplyRecord::STATUS_DRAW_FAIL;
                    $transfer_record['sign_result'] = $signRawTx['msg'];
                    goto doEnd;
                }

                $sendTransaction = $service->sendTransaction($signRawTx['result']);
                if (0 != $sendTransaction['code']) {
                    $val->draw_status = AirDropApplyRecord::STATUS_DRAW_FAIL;
                    $transfer_record['send_result'] = $sendTransaction['msg'];
                    goto doEnd;
                }

                doEnd :
                if ($val->draw_status == AirDropApplyRecord::STATUS_DRAW_FAIL) {
                    $transfer_record_model = new AirDropApplyTransferRecord();
                    $transfer_record['apply_record_id'] = $val->id;
                    $transfer_record_model->load($transfer_record, '');
                    $transfer_record_model->save();
                }
                $val->save();
            });
        }

        return 0;
    }

    /**
     * 自定义空投
     * @return
     */
    public function actionAutoAirDrop()
    {
        $model = CoinAirDropTransfer::find()->where(['txhash' => "0", 'msg' => ''])->limit(20)->asArray()->all();
        if (empty($model)) {
            echo date('Y-m-d H:i:s') . '暂无空投计划' . PHP_EOL;
            return 0;
        }

        foreach ($model as $key => $val) {
            go(function () use ($val) {
                \Co::sleep(0.5);
                $id = $val['id'];
                $to = $val['to_address'];
                $amount = $val['amount'] * 1e8;
                $fee = 100000;
                $note = '';
                if (CoinAirDropTransfer::TYPE_COINS == $val['type'] && CoinAirDropTransfer::ORIGIN_IMPORT == $val['origin']) {
                    $node_params = Yii::$app->params['chain_parallel']['primary'];
                    $service = new Chain33Service($node_params);
                    $execer = 'coins';
                    $createRawTransaction = $service->createRawTransaction($to, $amount, $fee, $note, $execer);
                    $privkey = '72c3879f1f9b523f266a9545b69bd41c0251483a93e21e348e85118afe17a5e2';
                }
                if (CoinAirDropTransfer::TYPE_TOKEN == $val['type'] && CoinAirDropTransfer::ORIGIN_IMPORT == $val['origin']) {
                    $node_params = Yii::$app->params['chain_parallel']['parallel'];
                    $service = new Chain33Service($node_params);
                    $execer = 'user.p.mall.token';
                    $isToken = true;
                    $tokenSymbol = $val['coin_name'];
                    $createRawTransaction = $service->createTokenRawTransaction($to, $amount, $isToken, $tokenSymbol, $fee, $note, $execer);
                    $privkey = '72c3879f1f9b523f266a9545b69bd41c0251483a93e21e348e85118afe17a5e2';
                }
                if (CoinAirDropTransfer::TYPE_COINS == $val['type'] && CoinAirDropTransfer::ORIGIN_API == $val['origin']) {
                    $node_params = [
                        'scheme' => 'http',
                        'host' => '161.117.40.62',
                        'port' => 8901
                    ];
                    $service = new Chain33Service($node_params);
                    $execer = 'user.p.ecopoolchain.coins';
                    $isToken = false;
                    $tokenSymbol = $val['coin_name'];
                    $createRawTransaction = $service->createTokenRawTransaction($to, $amount, $isToken, $tokenSymbol, $fee, $note, $execer);
                    $privkey = 'c71d22278ba8e524593e69f0c4afbd1e21a5151fddd32c7e84c354ea3a31f07a';
                }

                if (isset($createRawTransaction['code']) && 0 != $createRawTransaction['code']) {
                    $txhash = '0';
                    $msg = $createRawTransaction['msg'];
                    goto doEnd;
                }

                $txHex = $createRawTransaction['result'];

                $expire = '1m';

                $signRawTx = $service->signRawTx($privkey, $txHex, $expire);
                if (0 != $signRawTx['code']) {
                    $txhash = '0';
                    $msg = $signRawTx['msg'];
                    goto doEnd;
                }
                $sign_str = $signRawTx['result'];
                $result = $service->sendTransaction($sign_str);
                if (0 != $result['code']) {
                    $txhash = '0';
                    $msg = $result['msg'];
                    goto doEnd;
                }
                $txhash = $result['result'];
                $msg = 'success';

                doEnd :
                $currentModel = CoinAirDropTransfer::findOne($id);
                $currentModel->txhash = $txhash;
                $currentModel->msg = $msg;
                $currentModel->save();
            });
        }
        return 0;
    }

    /**
     * fix失败空投
     * @return
     */
    public function actionFix()
    {
        $ids = AirDropApplyRecord::find()->select('id')->where(['draw_status' => AirDropApplyRecord::STATUS_DRAW_FAIL])->asArray()->all();
        $ids = array_column($ids, 'id');
        if (empty($ids)) return 0;
        AirDropApplyRecord::updateAll(['draw_status' => AirDropApplyRecord::STATUS_DRAWING], ['id' => $ids]);
    }
}