<?php

namespace api\controllers;

use common\components\ErrorMessage;
use common\models\psources\CoinIssueRevokeRecord;
use common\models\psources\CoinIssueAttachment;
use common\service\chain33\Chain33Service;
use Yii;
use yii\data\Pagination;
use api\base\BaseController;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinIssueCoin;
use common\models\psources\CoinIssueChainRecord;

class IssueCoinController extends BaseController
{

    public function actionValidate()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $coin_platform = CoinPlatform::findOne($platform_id);
        if (false == $coin_platform) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }

        $result = Yii::$app->request->post();
        $chain_id = isset($result['chain_id']) ? $result['chain_id'] : 0;
        if (false == $chain_id) {
            $msg = '不存在的链';
            $code = -1;
            goto doEnd;
        }

        $params = [
            'name' => isset($result['name']) ? $result['name'] : '',
            'symbol' => isset($result['symbol']) ? $result['symbol'] : '',
            'total' => isset($result['total']) ? $result['total'] : '',
            'owner' => isset($result['owner']) ? $result['owner'] : '',
            'introduction' => isset($result['introduction']) ? $result['introduction'] : '',
            'category' => isset($result['category']) ? $result['category'] : 0,
            'type' => isset($result['type']) ? $result['type'] : 0,
            'platform_id' => $platform_id,
            'chain_id' => $chain_id,
            'charge_unit' => isset($result['charge_unit']) ? $result['charge_unit'] : '',
            'charge' => $coin_platform->issue_charge,
            'token_type' => isset($result['token_type']) ? $result['token_type'] : 0,
            'nickname' => isset($result['nickname']) ? $result['nickname'] : ''
        ];
        $model = new CoinIssueCoin();
        if (0 == $params['token_type']) {
            $model->setScenario(CoinIssueCoin::SCENARIOS_CREATE);
        }

        if (in_array($params['token_type'], [1, 2])) {
            $model->setScenario(CoinIssueCoin::SCENARIOS_NO_CHARGE);
        }

        if (in_array($params['token_type'], [3, 4])) {
            $params['template'] = isset($result['template']) ? $result['template'] : [];
            $model->setScenario(CoinIssueCoin::SCENARIOS_VERIFY_MALL);
        }

        $model->load($params, '');
        if (!$model->validate()) {
            $msg = $model->errors;
            $code = -1;
            goto doEnd;
        }
        $msg = 'ok';
        $code = 0;

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }


    /**
     * 发行申请
     * @return array
     */
    public function actionApply()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $coin_platform = CoinPlatform::findOne($platform_id);
        if (false == $coin_platform) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }

        if (!Yii::$app->request->isPost) {
            $msg = '错误的请求方式';
            $code = -1;
            goto doEnd;
        }
        $result = Yii::$app->request->post();
        $chain_id = isset($result['chain_id']) ? $result['chain_id'] : 0;
        if (false == $chain_id) {
            $msg = '不存在的链';
            $code = -1;
            goto doEnd;
        }

        $pre_create_tx = isset($result['pre_create_tx']) ? $result['pre_create_tx'] : '';
        $pre_send_transaction = isset($result['pre_send_transaction']) ? $result['pre_send_transaction'] : '';
        if (false == $pre_create_tx || false == $pre_send_transaction) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }
        $params = [
            'name' => isset($result['name']) ? $result['name'] : '',
            'symbol' => isset($result['symbol']) ? strtoupper($result['symbol']) : '',
            'total' => isset($result['total']) ? $result['total'] : '',
            'owner' => isset($result['owner']) ? $result['owner'] : '',
            'introduction' => isset($result['introduction']) ? $result['introduction'] : '',
            'category' => isset($result['category']) ? $result['category'] : 0,
            'type' => isset($result['type']) ? $result['type'] : 0,
            'platform_id' => (int)$platform_id,
            'chain_id' => $chain_id,
            'charge_unit' => isset($result['charge_unit']) ? $result['charge_unit'] : '',
            'charge' => isset($result['type']) ? ((CoinIssueCoin::TYPE_NO == $result['type']) ? $coin_platform->issue_charge : 0) : 0,
            'token_type' => isset($result['token_type']) ? $result['token_type'] : 0,
            'nickname' => isset($result['nickname']) ? $result['nickname'] : ''
        ];

        $model = new CoinIssueCoin();
        if (0 == $params['token_type']) {
            $model->setScenario(CoinIssueCoin::SCENARIOS_CREATE);
        }

        if (in_array($params['token_type'], [1, 2])) {
            $model->setScenario(CoinIssueCoin::SCENARIOS_NO_CHARGE);
        }

        if (in_array($params['token_type'], [3, 4])) {
            $model->setScenario(CoinIssueCoin::SCENARIOS_CREATE_MALL);
        }

        $model->load($params, '');

        if (!$model->save()) {
            $msg = $model->errors;
            $code = -1;
            goto doEnd;
        }
        $msg = 'ok';
        $code = 0;
        $data = $model->getPrimaryKey();
        if (0 != $params['token_type']) {
            $template = new CoinIssueAttachment();
            $template->issue_coin_id = $data;
            $template->origin = isset($result['origin']) ? $result['origin'] : '';
            $template->template = isset($result['template']) ? $result['template'] : '{}';
            $template->save();
        }

        $params = [
            'pre_create_tx' => $pre_create_tx,
            'pre_send_transaction' => $pre_send_transaction,
            'pre_query_transaction' => 'standby',
            'finish_tx' => 'standby',
            'finish_send_transaction' => 'standby',
            'finish_query_transaction' => 'standby',
            'issue_coin_id' => $data,
        ];
        $transfer_model = new CoinIssueChainRecord();
        $transfer_model->setScenario(CoinIssueChainRecord::SCENARIOS_PRE_CREATE);
        $transfer_model->load($params, '');
        $transfer_model->save();

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 申请列表
     * @param integer page
     * @param integer size
     * @return array
     */
    public function actionApplyList()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $address = Yii::$app->request->get('address', '');
        $status = Yii::$app->request->get('status', '');
        if (false == $status) {
            $status = [CoinIssueCoin::STATUS_PEDDING, CoinIssueCoin::STATUS_CANCEL_SUCCESS, CoinIssueCoin::STATUS_CANCEL_FAILED, CoinIssueCoin::STATUS_CONFIRM, CoinIssueCoin::STATUS_ALLOW, CoinIssueCoin::STATUS_REFUSE, CoinIssueCoin::STATUS_SUCCESS, CoinIssueCoin::STATUS_FAILED];
        }

        if (empty($platform_id) || empty($address)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $page = \Yii::$app->request->get('page', 1);
        $size = \Yii::$app->request->get('size', 5);

        $query = CoinIssueCoin::find()
            ->select('id, name, symbol, type, total, create_time, status, chain_id, token_type')
            ->where(['owner' => $address, 'platform_id' => $platform_id])
            ->andWhere(['in', 'status', $status])
            ->orderBy('create_time desc');

        $countQuery = clone $query;
        $models = $query->offset(($page - 1) * $size)->limit($size)->all();
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $size]);
        foreach ($models as &$val) {
            $val->chain_id = $val->chain->platform;
            if (35 != $platform_id) {
                $val->total = (int)$val->total * 1e4;
            }
        }
        $data = [
            'list' => $models,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize' => $size,
                'currentPage' => $page,
            ]
        ];
        $msg = 'ok';
        $code = 0;

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 申请详情
     * @param integer id
     * @return array
     */
    public function actionApplyDetail()
    {
        $id = Yii::$app->request->get('id', 0);
        $data = null;
        if (empty($id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $data = CoinIssueCoin::find()->where(['id' => $id])->one();
        if (false == $data) {
            $msg = '不存在的记录';
            $code = -1;
            goto doEnd;
        }
        if (35 != $data->platform_id) {
            $data->total = (int)$data->total * 1e4;
        }
        $data->chain_name = $data->chain->platform;
        $data->issue_charge = rtrim(sprintf('%.3f', floatval($data->charge)), '0');
        $data->template = isset($data->templated->template) ? $data->templated->template : null;
        $data->origin = isset($data->templated->origin) ? $data->templated->origin : null;
        $code = 0;
        $msg = 'success';
        if (CoinIssueCoin::STATUS_FAILED == $data->status) {
            $code = -1;
            if ($data->transfer->pre_query_transaction != 'success') {
                $msg = '预发行失败。失败原因：' . ErrorMessage::getMessage($data->transfer->pre_query_transaction);
            }
            if ($data->transfer->pre_query_transaction == 'success' && $data->transfer->finish_query_transaction != 'success') {
                $msg = '发行失败。失败原因：' . ErrorMessage::getMessage($data->transfer->finish_query_transaction);
            }
        }

        if (CoinIssueCoin::STATUS_REFUSE == $data->status) {
            $code = -1;
            $msg = '发行失败。失败原因：' . $data->transfer->finish_query_transaction;
        }

        if (CoinIssueCoin::STATUS_CANCEL_FAILED == $data->status) {
            $code = -1;
            $msg = '撤消失败。失败原因：' . ErrorMessage::getMessage($data->revoke->revoke_query_transaction);
        }

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 申请撤消/确认
     * @param integer id
     * @param string pre_create_tx
     * @param string pre_send_transaction
     * @return array
     */

    public function actionSendTransfer()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $params = Yii::$app->request->post();
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $pre_create_tx = isset($params['pre_create_tx']) ? $params['pre_create_tx'] : '';
        $pre_send_transaction = isset($params['pre_send_transaction']) ? $params['pre_send_transaction'] : '';
        if (false == $id || false == $pre_create_tx || false == $pre_send_transaction) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $model = CoinIssueChainRecord::find()->where(['issue_coin_id' => $id])->one();
        if (true == $model) {
            $msg = '交易记录已存在';
            $code = -1;
            goto doEnd;
        }

        $params = [
            'pre_create_tx' => $pre_create_tx,
            'pre_send_transaction' => $pre_send_transaction,
            'pre_query_transaction' => 'standby',
            'finish_tx' => 'standby',
            'finish_send_transaction' => 'standby',
            'finish_query_transaction' => 'standby',
            'issue_coin_id' => $id,
        ];
        $transfer_model = new CoinIssueChainRecord();
        $transfer_model->setScenario(CoinIssueChainRecord::SCENARIOS_PRE_CREATE);
        $transfer_model->load($params, '');
        if (!$transfer_model->save()) {
            $msg = current($transfer_model->firstErrors);
            $code = -1;
            goto doEnd;
        }

        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    /**
     * 确认申请
     * @param integer id
     * @return array
     */
    public function actionVerify()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $params = Yii::$app->request->post();
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        if (false == $id) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $model = CoinIssueCoin::findOne($id);
        if (false == $model) {
            $msg = '不存在的记录';
            $code = -1;
            goto doEnd;
        }

        if (CoinIssueCoin::STATUS_PEDDING != $model->status) {
            $msg = '该状态下禁止发行';
            $code = -1;
            goto doEnd;
        }

        $record_model = CoinIssueChainRecord::find()->where(['issue_coin_id' => $id])->one();
        if (false == $record_model) {
            $msg = '交易记录不存在';
            $code = -1;
            goto doEnd;
        }

        $data = [
            'status' => CoinIssueCoin::STATUS_CONFIRM,
        ];
        $model->setScenario(CoinIssueCoin::SCENARIOS_CANCEL);
        $model->load($data, '');
        if (!$model->save()) {
            $msg = current($model->firstErrors);
            $code = -1;
            goto doEnd;
        }

        $record_model->pre_query_transaction = 'success';
        $record_model->update();

        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    /**
     * 申请撤消
     * @param integer id
     * @param string revoke_tx
     * @param string revoke_send_transaction
     * @return array
     */
    public function actionRevoke()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $params = Yii::$app->request->post();
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $revoke_tx = isset($params['revoke_tx']) ? $params['revoke_tx'] : '';
        $revoke_send_transaction = isset($params['revoke_send_transaction']) ? $params['revoke_send_transaction'] : '';
        if (false == $revoke_tx || false == $revoke_send_transaction || false == $id) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $issue_coin = CoinIssueCoin::findOne($id);
        if (false == $issue_coin) {
            $msg = '不存在的记录';
            $code = -1;
            goto doEnd;
        }

        $isExist = CoinIssueRevokeRecord::find()->where(['issue_coin_id' => $id])->count();
        if (0 != $isExist) {
            $msg = '撤消记录已存在';
            $code = -1;
            goto doEnd;
        }

        $issue_coin_data = [
            'status' => CoinIssueCoin::STATUS_CANCEL,
        ];

        $issue_coin->setScenario(CoinIssueCoin::SCENARIOS_CANCEL);
        $issue_coin->load($issue_coin_data, '');
        if (!$issue_coin->save()) {
            $msg = current($issue_coin->firstErrors);
            $code = -1;
            goto doEnd;
        }

        $issue_revoke_record = new CoinIssueRevokeRecord();
        $issue_revoke_data = [
            'issue_coin_id' => $id,
            'revoke_tx' => $revoke_tx,
            'revoke_send_transaction' => $revoke_send_transaction,
            'revoke_query_transaction' => 'standby',
        ];
        $issue_revoke_record->setScenario(CoinIssueRevokeRecord::SCENARIOS_CREATE);
        $issue_revoke_record->load($issue_revoke_data, '');
        $issue_revoke_record->save();

        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    public function actionSymbolValidate()
    {
        $code = 0;
        $msg = 'success';
        $symbol = Yii::$app->request->post('symbol', '');
        $token_type = Yii::$app->request->post('token_type', '');
        if (false == $symbol || false == $token_type) {
            $msg = '缺少必要的参数';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $model = CoinIssueCoin::find()
            ->where(['like', 'symbol', $symbol . '%', false])
            ->andWhere(['platform_id' => 35])
            ->andWhere(['token_type' => (int)$token_type])
            ->orderBy('id desc')
            ->asArray()
            ->one();
        if (empty($model)) {
            $data = $symbol;
            goto doEnd;
        }

        if ($symbol == $model['symbol']) {
            $data = $model['symbol'] . '01';
            goto doEnd;
        }

        $prefix = (explode($symbol, $model['symbol']))[1];
        $prefix++;
        if ($prefix < 10) {
            $data = $symbol . '0' . $prefix;
            goto doEnd;
        }
        $data = $symbol . $prefix;

//        $suffix = substr($data, -1);
//        if (is_numeric($suffix)) {
//            $prefix = substr($data, 0, -1);
//            $data = $prefix . ($suffix + 1);
//        } else {
//            $data = $data . 1;
//        }

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}