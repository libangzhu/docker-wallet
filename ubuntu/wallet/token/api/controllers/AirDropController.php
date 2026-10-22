<?php

namespace api\controllers;

use Yii;
use common\components\Tools;
use api\base\BaseController;
use common\models\psources\AirDrop;
use common\service\chain33\Chain33Service;
use common\models\psources\AirDropRulePool;
use common\models\psources\CoinAirDropTrade;
use common\models\psources\AirDropApplyRecord;

class AirDropController extends BaseController
{
    public function actionValidateIdentifier()
    {
        $data = Yii::$app->request->get();

        $identifier = $data['identifier'] ?? null;
        if (false == $identifier) {
            $this->code = -1;
            $this->msg = '设备号错误，请重新输入.';
            goto doEnd;
        }

        $exist = AirDropRulePool::find()->where(['identifier' => $identifier])->one();
        if (false == $exist || false == $exist->rule) {
            $this->code = -1;
            $this->msg = '设备号错误，请重新输入.';
            goto doEnd;
        }

//        $miner_address = $data['miner_address'] ?? null;
//        if (!empty($miner_address)) {
//            $exist = AirDrop::find()->where(['identifier' => $identifier, 'miner_address' => $miner_address])->one();
//            if (false == $exist) {
//                $this->code = -1;
//                $this->msg = '设备号和矿工地址不匹配，请重新确认.';
//                goto doEnd;
//            }
//        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }


    /**
     * 参于空投申请/空投申请列表
     * @param identifier    树莓派编号
     * @param miner_address 矿工地址
     * @param apply_ids
     * @return array
     */
    public function actionApply()
    {
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            $identifier = $data['identifier'] ?? null;
            if (false == $identifier) {
                $this->code = -1;
                $this->msg = 'Validation failed.';
                goto doEnd;
            }
            $exist = AirDropRulePool::find()->where(['identifier' => $identifier])->one();
            if (false == $exist || false == $exist->rule) {
                $this->code = -1;
                $this->msg = 'Validation failed.';
                goto doEnd;
            }

            $model = new AirDrop();
            $model->setScenario(AirDrop::SCENARIOS_CREATE);
            $model->load($data, '');
            if (!$model->validate()) {
                $this->code = -1;
                $this->msg = $model->errors;
                goto doEnd;
            }
            $model->save();
            $apply_id = $model->id;

            $time = $exist->rule->duration - 1;
            $create_time = date('Y-m-d');
            $finish_time = date('Y-m-d', strtotime("+$time day"));
            $expiry_date = Tools::getDatesFromRange($create_time, $finish_time);
            $apply_record_model = new AirDropApplyRecord();
            foreach ($expiry_date as $key => $val) {
                $apply_record_model->setIsNewRecord(true);
                $apply_record_model->apply_id = $apply_id;
                $apply_record_model->reach = AirDropApplyRecord::REACH_NO;
                $apply_record_model->amount = $exist->rule->amount;
                $apply_record_model->token = $exist->rule->token;
                $apply_record_model->draw_status = AirDropApplyRecord::STATUS_UNDRAW;
                $apply_record_model->create_time = $val;
                $apply_record_model->update_time = $val;
                $apply_record_model->save() && $apply_record_model->id = 0;;
            }

            $redis = Yii::$app->redis_app;
            $redis->hmset('airdrop:' . $data['identifier'], 'reach', 0, 'draw_success', 0, 'income', 0, 'un_draw', 0);
            goto doEnd;
        }

        if (Yii::$app->request->isGet) {
            $page = \Yii::$app->request->get('page', 1);
            $size = \Yii::$app->request->get('size', 10);

            $data = Yii::$app->request->get();
            $identifier = $data['identifier'] ?? '';
            $miner_address = $data['miner_address'] ?? '';
            $apply_ids = isset($data['apply_ids']) ? $data['apply_ids'] : '';
            if (false == $identifier || false == $miner_address) {
                $this->code = -1;
                $this->msg = 'Validation failed.';
                goto doEnd;
            }
            $model = AirDrop::find()->select('id')->where(['identifier' => $identifier, 'miner_address' => $miner_address])->one();
            if (empty($model) || empty($model->record)) {
                goto doEnd;
            }

            $query = AirDropApplyRecord::find()
                ->select('id, reach, amount, token,draw_status, create_time, update_time')
                ->where(['apply_id' => $model['id']]);
            if (!empty($apply_ids)) {
                $apply_ids = rtrim($apply_ids, ',');
                $apply_id_arr = explode(',', $apply_ids);
                $query->andWhere(['in', 'id', $apply_id_arr]);
            }
            $query->andWhere(['>', 'draw_status', AirDropApplyRecord::STATUS_UNDRAW]);

            $items = $query->offset(($page - 1) * $size)->orderBy('update_time desc')->limit($size)->all();
            $countQuery = clone $query;
            $total = (int)$countQuery->count();

            $this->data = [
                'items' => $items,
                'total' => $total,
            ];
        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionIncome()
    {
        $this->data = 0;
        $data = Yii::$app->request->get();
        $identifier = $data['identifier'] ?? '';
        $miner_address = $data['miner_address'] ?? '';
        if (false == $identifier || false == $miner_address) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }

        $model = AirDrop::find()->select('id')->where(['identifier' => $identifier, 'miner_address' => $miner_address])->one();
        if (empty($model) || empty($model->record)) {
            goto doEnd;
        }

        $this->data = AirDropApplyRecord::find()
            ->where(['apply_id' => $model['id'], 'draw_status' => AirDropApplyRecord::STATUS_DRAW_SUEEESS])
            ->sum('amount');
        if (empty($this->data)) {
            $this->data = 0;
            goto doEnd;
        }
        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => (int)$this->data];
    }

    /**
     * 验证今日是否可领取
     * @param identifier    树莓派编号
     * @return array
     */
    public function actionValidate()
    {
        $data = Yii::$app->request->get();
        $identifier = $data['identifier'] ?? '';
        if (false == $identifier) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }

        $air_drop = AirDrop::find()->select('id')->where(['identifier' => $identifier])->one();
        if (empty($air_drop) || empty($air_drop->record)) {
            $this->code = -1;
            $this->msg = '设备号错误，请重新确认.';
            goto doEnd;
        }

        $amount = AirDropApplyRecord::find()
            ->where(['apply_id' => $air_drop['id'], 'draw_status' => AirDropApplyRecord::STATUS_DRAW_SUEEESS])
            ->sum('amount');
        if ($amount >= 1000) {
            $this->data = false;
            goto doEnd;
        }

        $redis = Yii::$app->redis_app;
        if (true == $redis->exists($identifier)) {
            $this->data = false;
            goto doEnd;
        }
        $this->data = true;

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    /**
     * 空投领取情况
     * @param identifier    树莓派编号
     * @param miner_address 矿工地址
     * @return array
     */
    public function actionInfo()
    {
        if (Yii::$app->request->isPost) {
            $this->code = -1;
            $this->msg = '请求方式错误!';
            goto doEnd;
        }

        $data = Yii::$app->request->get();
        $identifier = $data['identifier'] ?? null;
        if (false == $identifier) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }

        $redis = Yii::$app->redis_app;
        list($reached_times, $draw_success, $income, $un_draw) = $redis->hmget('airdrop:' . $identifier, 'reached_times', 'draw_success', 'income', 'un_draw');
        $exist = AirDropRulePool::find()->where(['identifier' => $identifier])->one();
        if (false == $exist) {
            $reach = 0;
        } else {
            $reach = $exist->rule->duration;
        }
        $this->data = [
            'reach' => empty($reach) ? 0 : (int)$reach,
            'reached_times' => empty($reached_times) ? 0 : (int)$reached_times,
            'draw_success' => empty($draw_success) ? 0 : (int)$draw_success,
            'income' => empty($income) ? 0 : (int)$reach,
            'un_draw' => empty($un_draw) ? 0 : (int)$un_draw,
        ];

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionBatchDraw()
    {
        $this->code = -1;
        $this->msg = '请求方式错误!';
        goto doEnd;

        if (!Yii::$app->request->isPost) {
            $this->code = -1;
            $this->msg = '请求方式错误!';
            goto doEnd;
        }

        $data = Yii::$app->request->post();
        $identifier = $data['identifier'] ?? '';
        $miner_address = $data['miner_address'] ?? '';
        if (false == $identifier || false == $miner_address) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }
        $model = AirDrop::find()->select('id')->where(['identifier' => $identifier, 'miner_address' => $miner_address])->one();

        if (empty($model) || empty($model->record)) {
            $this->code = -1;
            $this->msg = '暂无符合条件的空投申请记录';
            goto doEnd;
        }

        $record = AirDropApplyRecord::find()->where(['apply_id' => $model['id'], 'reach' => AirDropApplyRecord::REACH_YES, 'draw_status' => AirDropApplyRecord::STATUS_UNDRAW])->all();
        if (empty($record)) {
            $this->code = -1;
            $this->msg = '暂无符合条件的领取记录';
            goto doEnd;
        }
        foreach ($record as $val) {
            $val->draw_status = AirDropApplyRecord::STATUS_DRAWING;
            $val->save();
        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    /**
     * 领取某日空投
     * @param identifier    树莓派编号
     * @param miner_address 矿工地址
     * @return array
     */
    public function actionDraw()
    {
        if (!Yii::$app->request->isPost) {
            $this->code = -1;
            $this->msg = '请求方式错误!';
            goto doEnd;
        }

        $data = Yii::$app->request->post();
        $identifier = $data['identifier'] ?? '';
        $miner_address = $data['miner_address'] ?? '';
        if (false == $identifier || false == $miner_address) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }

        $model = AirDrop::find()->select('id, wallet_address')->where(['identifier' => $identifier, 'miner_address' => $miner_address])->one();

        if (empty($model) || empty($model->wallet_address) || empty($model->record)) {
            $this->code = -1;
            $this->msg = '设备初始化中，请稍后再试.';
            goto doEnd;
        }
        $expiry_date = date("Y-m-d", strtotime("+1 day"));
        $record = AirDropApplyRecord::find()
            ->where(['apply_id' => $model->id, 'draw_status' => AirDropApplyRecord::STATUS_UNDRAW])
            ->andWhere(['<', 'create_time', $expiry_date])
            ->orderBy('id')
            ->limit(1)
            ->one();

        if (empty($record)) {
            $this->code = -1;
            $this->msg = '暂无符合条件的领取记录';
            goto doEnd;
        }

        $redis = Yii::$app->redis_app;
        $value = date('Y-m-d H:i:s');
        $is_locked = $redis->setnx($data['identifier'], $value);
        if (!$is_locked) {
            $this->msg = '已达今日领取上限，请明天再来!';
            $this->code = -1;
            goto doEnd;
        }

        $service = new Chain33Service();
        $execer = 'ticket';
        $result = $service->getBalance($model->wallet_address, $execer);

        if (0 == $result['code']) {
            $balance = $result['result'][0]['balance'] ?? 0;
            $frozen = $result['result'][0]['frozen'] ?? 0;
            if (($balance + $frozen) / 1e8 < 3000) {
                $this->code = -1;
                $this->msg = '未达标，请确认矿机正在挖矿后重新尝试';
                $redis->del($data['identifier']);
                goto doEnd;
            }
        }

        $record->reach = AirDropApplyRecord::REACH_YES;
        $record->draw_status = AirDropApplyRecord::STATUS_DRAWING;
        $record->update_time = date('Y-m-d');
        if (false == $record->save()) {
            $this->code = -1;
            $this->msg = $record->errors;
            goto doEnd;
        }

        $redis->setex($data['identifier'], strtotime('23:59:59') - time(), $value);

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    /**
     * 领取某日空投 （暂停用）
     * @param id    某日id
     * @param identifier    树莓派编号
     * @param miner_address 矿工地址
     * @return array
     */
    public function actionDrawBak()
    {
        if (!Yii::$app->request->isPost) {
            $this->code = -1;
            $this->msg = '请求方式错误!';
            goto doEnd;
        }

        $data = Yii::$app->request->post();
        $id = $data['id'] ?? '';
        $identifier = $data['identifier'] ?? '';
        $miner_address = $data['miner_address'] ?? '';
        if (false == $id || false == $identifier || false == $miner_address) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }
        $model = AirDrop::find()->select('id')->where(['identifier' => $identifier, 'miner_address' => $miner_address])->one();

        if (empty($model) || empty($model->record)) {
            $this->code = -1;
            $this->msg = '暂无符合条件的空投申请记录';
            goto doEnd;
        }

        $record = AirDropApplyRecord::find()->where(['id' => $id, 'reach' => AirDropApplyRecord::REACH_YES, 'draw_status' => AirDropApplyRecord::STATUS_UNDRAW])->one();
        if (empty($record)) {
            $this->code = -1;
            $this->msg = '暂无符合条件的领取记录';
            goto doEnd;
        }

        $redis = Yii::$app->redis_app;
        $value = date('Y-m-d H:i:s');
        $is_locked = $redis->setnx($data['identifier'], $value);
        if (!$is_locked) {
            $this->msg = '已达今日领取上限，请明天再来!';
            $this->code = -1;
            goto doEnd;
        }
        $redis->setex($data['identifier'], strtotime('23:59:59') - time(), $value);
        $record->draw_status = AirDropApplyRecord::STATUS_DRAWING;
        if (false == $record->save()) {
            $this->code = -1;
            $this->msg = $record->errors;
            goto doEnd;
        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionRule()
    {
        $data = Yii::$app->request->get();
        $identifier = $data['identifier'] ?? null;
        $miner_address = $data['miner_address'] ?? null;
        if (false == $identifier || false == $miner_address) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }
        $exist = AirDropRulePool::find()->where(['identifier' => $identifier])->one();
        if (false == $exist || false == $exist->rule) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }
        $token = $exist->rule->token;
        $amount = $exist->rule->amount;
        $apply = AirDrop::find()->select('id')->where(['identifier' => $identifier, 'miner_address' => $miner_address])->asArray()->one();
        if (empty($apply)) {
            $this->code = -1;
            $this->msg = 'Validation failed.';
            goto doEnd;
        }
        $record = AirDropApplyRecord::find()->select('create_time')->where(['apply_id' => $apply['id']])->orderBy('id')->asArray()->all();
        if (empty($record)) {
            $begin_time = '?';
            $over_time = '?';
        } else {
            $create_time_arr = array_column($record, 'create_time');
            $begin_time = current($create_time_arr);
            $over_time = end($create_time_arr);
        }
        $this->data = [
            'token' => $token,
            'amount' => $amount,
            'begin_time' => $begin_time,
            'over_time' => $over_time
        ];
        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionGameTrade()
    {
        $coins_address = Yii::$app->request->post('coins_address', '');

        if (empty($coins_address)) {
            return ['code' => -1, 'msg' => '参数不能为空'];
        }

        $coinTokenTransfer = CoinAirDropTrade::find()->where(['coins_address' => $coins_address, 'type' => CoinAirDropTrade::TYPE_GAME])->one();
        if ($coinTokenTransfer) {
            return ['code' => -1, 'data' => null, 'msg' => 'record already exists'];
        }

        $fee = 100000;
        $amount = CoinAirDropTrade::AMOUNT_GAME * 1e8;
        $execer = 'coins';
        $note = '';

        $service = new Chain33Service();
        $createRawTransaction = $service->createRawTransaction($coins_address, $amount, $fee, $note, $execer);
        if (0 != $createRawTransaction['code']) {
            $msg = $createRawTransaction['msg'];
            $code = -1;
            goto doEnd;
        }

        $txHex = $createRawTransaction['result'];
        $privkey = '72c3879f1f9b523f266a9545b69bd41c0251483a93e21e348e85118afe17a5e2';
        $expire = '1m';

        $signRawTx = $service->signRawTx($privkey, $txHex, $expire);
        if (0 != $signRawTx['code']) {
            $msg = $signRawTx['msg'];
            $code = -1;
            goto doEnd;
        }
        $sign_str = $signRawTx['result'];
        $result = $service->sendTransaction($sign_str);

        if (0 != $result['code']) {
            $msg = $result['msg'];
            $code = -1;
            goto doEnd;
        }
        $code = 1;
        $msg = $result['result'];

        doEnd :
        $airDropModel = new CoinAirDropTrade();
        $airDropModel->coins_address = $coins_address;
        $airDropModel->amount = CoinAirDropTrade::AMOUNT_GAME;
        $airDropModel->msg = $msg;
        $airDropModel->attach = 0;
        $airDropModel->balance = 0;
        $airDropModel->type = CoinAirDropTrade::TYPE_GAME;
        $airDropModel->save();

        return ['code' => $code, 'msg' => $msg];
    }

    public function actionGameTradeUpdate()
    {
        $coinAirDropTrade = CoinAirDropTrade::find()->where(['attach' => 2, 'msg' => '0'])->limit(30)->all();
        foreach ($coinAirDropTrade as $val) {
            $fee = 100000;
            $amount = 1 * 1e8;
            $execer = 'coins';
            $note = '';

            $service = new Chain33Service();
            $createRawTransaction = $service->createRawTransaction($val->coins_address, $amount, $fee, $note, $execer);
            if (0 != $createRawTransaction['code']) {
                continue;
            }

            $txHex = $createRawTransaction['result'];
            $privkey = '72c3879f1f9b523f266a9545b69bd41c0251483a93e21e348e85118afe17a5e2';
            $expire = '1m';

            $signRawTx = $service->signRawTx($privkey, $txHex, $expire);
            if (0 != $signRawTx['code']) {
                continue;
            }
            $sign_str = $signRawTx['result'];
            $result = $service->sendTransaction($sign_str);

            if (0 != $result['code']) {
                continue;
            }
            $currentModel = CoinAirDropTrade::findOne($val->id);
            $currentModel->attach = 2;
            $currentModel->balance = 0;
            $currentModel->msg = $result['result'];
            $currentModel->save();
        }
        return ['code' => 1, 'msg' => 'ok'];
    }

    public function actionGetBalance()
    {
        $coinAirDropTrade = CoinAirDropTrade::find()->where(['balance' => 0])->limit(60)->all();
        $address = [];
        foreach ($coinAirDropTrade as $val) {
            $address[] = $val->coins_address;
        }
        $service = new Chain33Service();
        $execer = 'coins';
        $result = $service->getBalance($address, $execer);
        if (0 == $result['code']) {
            $result_balance = $result['result'];
            foreach ($result_balance as $val) {
                $coinAirDropTrade = CoinAirDropTrade::find()->where(['coins_address' => $val['addr']])->one();
                if (empty($coinAirDropTrade)) continue;
                $coinAirDropTrade->balance = $val['balance'];
                $coinAirDropTrade->save();
            }
        }
        return ['code' => 1, 'msg' => 'ok'];
    }
}