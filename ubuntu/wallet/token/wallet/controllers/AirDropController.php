<?php

namespace wallet\controllers;

use common\models\psources\AirDropApplyRecord;
use common\models\psources\AirDropRefund;
use common\models\psources\AirDropRulePool;
use common\service\chain33\Chain33Service;
use Yii;
use wallet\base\BaseController;
use common\models\psources\AirDrop;

class AirDropController extends BaseController
{
    /**
     * 空投地址余额
     * @param
     * @return array
     */
    public function actionGetBalance()
    {
        $address = Yii::$app->params['AirDrop']['address'];
        $service = new Chain33Service();
        $execer = 'coins';
        $result = $service->getBalance($address, $execer);
        if (0 != $result['code']) {
            $this->code = -1;
            $this->msg = '余额获取失败。失败原因:' . $result['error'];
            goto doEnd;
        }
        $this->data = $result['result'][0]['balance'] ?? 0;

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    /**
     * 总空投金额
     * @param
     * @return array
     */
    public function actionExpenses()
    {
        $expiry_date = date("Y-m-d", strtotime("+1 day"));
        $this->data = AirDropApplyRecord::find()
            ->where(['draw_status' => AirDropApplyRecord::STATUS_DRAW_SUEEESS])
            ->andWhere(['<', 'create_time', $expiry_date])
            ->sum('bonus_token');
        if (empty($this->data)) {
            $this->data = 0;
        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => (int)$this->data];
    }

    /**
     * 空投申请列表
     * @param page 页码
     * @param size 每页显示条数
     * @return array
     */
    public function actionIndex()
    {
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 10);

        if (!Yii::$app->request->isGet) {
            $this->code = -1;
            $this->msg = '错误的请求方式.';
            goto doEnd;
        }
        $query = AirDrop::find()->select('identifier, wallet_address,miner_address,create_time, finish_time');
        $model = $query->offset(($page - 1) * $size)->orderBy('id desc')->limit($size)->all();
        if (empty($model)) {
            goto doEnd;
        }
        $countQuery = clone $query;
        $redis = Yii::$app->redis_app;
        foreach ($model as &$val) {
            list($reach, $draw_success, $income, $un_draw) = $redis->hmget('airdrop:' . $val->identifier, 'reach', 'draw_success', 'income', 'un_draw');
            $val->reach = empty($reach) ? 0 : (int)$reach;
            $val->draw_success = empty($draw_success) ? 0 : (int)$draw_success;
            $val->income = empty($income) ? 0 : (int)$income;
            $val->un_draw = empty($un_draw) ? 0 : (int)$un_draw;
        }
        $this->data = [
            'items' => $model,
            'total' => (int)$countQuery->count()
        ];

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    /**
     * 每个树莓派的空投记录
     * @param identifier    树莓派编号
     * @param miner_address 矿工地址
     * @param apply_ids
     * @param page 页码
     * @param size 每页显示条数
     * @return array
     */
    public function actionApply()
    {
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
        $total = 0;
        $items = [];
        if (empty($model) || empty($model->record)) {
            goto doEnd;
        }

        $query = AirDropApplyRecord::find()
            ->select('reach, draw_status, create_time, update_time')
            ->where(['apply_id' => $model['id']]);
        if (!empty($apply_ids)) {
            $apply_ids = rtrim($apply_ids, ',');
            $apply_id_arr = explode(',', $apply_ids);
            $query->andWhere(['in', 'id', $apply_id_arr]);
        }
        $expiry_date = date("Y-m-d", strtotime("+1 day"));
        $query->andWhere(['<', 'create_time', $expiry_date]);

        $items = $query->offset(($page - 1) * $size)->orderBy('id desc')->limit($size)->all();
        $countQuery = clone $query;
        $total = (int)$countQuery->count();

        $this->data = [
            'items' => $items,
            'total' => $total,
        ];

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionStop()
    {
        $redis = Yii::$app->redis_app;
        $redis->del('airdrop:1000000000000000');
        exit;
        var_dump($redis->hget('airdrop', 1000000000000000));
        exit;
        var_dump($redis->hdel('airdrop:', 1000000000000000));
        exit;
        if (!Yii::$app->request->isPost) {
            $this->code = -1;
            $this->msg = '错误的请求方式.';
            goto doEnd;
        }
        $data = Yii::$app->request->post();
        $identifier = $data['identifier'] ?? null;
        if (empty($identifier)) {
            goto doEnd;
        }
        $identifier = array_unique($identifier);

        $model = AirDrop::find()->select('id,identifier,wallet_address,miner_address')->where(['in', 'identifier', $identifier])->asArray()->all();
        if (empty($model)) {
            goto doEnd;
        }
        $redis = Yii::$app->redis_app;
        $refund = new AirDropRefund();
        foreach ($model as $val) {
            $sum = AirDropApplyRecord::find()->where(['apply_id' => $val['id']])
                ->andWhere(['reach' => AirDropApplyRecord::REACH_YES])
                ->andWhere(['draw_status' => AirDropApplyRecord::STATUS_DRAW_SUEEESS])
                ->sum('amount');
            $refund->setIsNewRecord(true);
            $refund->identifier = $val['identifier'];
            $refund->wallet_address = $val['wallet_address'];
            $refund->miner_address = $val['miner_address'];
            $refund->draw_amount = $sum;
            $refund->save() && $refund->id = 0;

            AirDropRulePool::deleteAll('identifier = :identifier', [':identifier' => $val['identifier']]);
            AirDropApplyRecord::deleteAll('apply_id = :apply_id', [':apply_id' => $val['id']]);
            AirDrop::findOne($val['id'])->delete();
            $redis->del($val['identifier']);
            $redis->del('airdrop:' . $val['identifier']);
        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }
}