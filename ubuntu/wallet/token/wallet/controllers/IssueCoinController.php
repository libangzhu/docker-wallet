<?php

namespace wallet\controllers;

use common\models\psources\CoinIssueChainRecord;
use Yii;
use yii\data\Pagination;
use wallet\base\BaseController;
use common\models\psources\CoinIssueCoin;

class IssueCoinController extends BaseController
{
    /**
     * 申请列表
     * @param integer page
     * @param integer size
     * @param integer status
     * @param string owner
     * @param integer chain_id
     * @return array
     */
    public function actionApplyList()
    {
        $platform_id = Yii::$app->request->getPlatformId();
        $page = \Yii::$app->request->get('page', 1);
        $size = \Yii::$app->request->get('size', 10);
        $status = \Yii::$app->request->get('status', -1);
        $owner = \Yii::$app->request->get('owner', '');
        $chain_id = \Yii::$app->request->get('chain_id', '');
        if (1 == $platform_id) {
            $query = CoinIssueCoin::find()
                ->select('id, name, total, status, chain_id, charge_unit, charge, platform_id, owner, category, type, symbol, introduction, create_time')
                ->where(['in', 'status', [CoinIssueCoin::STATUS_CONFIRM, CoinIssueCoin::STATUS_ALLOW, CoinIssueCoin::STATUS_REFUSE, CoinIssueCoin::STATUS_SUCCESS, CoinIssueCoin::STATUS_FAILED]])
                ->orderBy('create_time desc');
        } else {
            $query = CoinIssueCoin::find()
                ->select('id, name, total, status, chain_id, charge_unit, charge, platform_id, owner, category, type, symbol, introduction, create_time')
                ->where(['platform_id' => $platform_id])
                ->andWhere(['in', 'status', [CoinIssueCoin::STATUS_CONFIRM, CoinIssueCoin::STATUS_ALLOW, CoinIssueCoin::STATUS_REFUSE, CoinIssueCoin::STATUS_SUCCESS, CoinIssueCoin::STATUS_FAILED]])
                ->orderBy('create_time desc');
        }
        if ($status > -1) {
            $query->andWhere(['status' => $status]);
        }

        if (false != $owner) {
            $query->andWhere(['owner' => $owner]);
        }

        if (false != $chain_id) {
            $query->andWhere(['chain_id' => $chain_id]);
        }

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $size]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        foreach ($models as &$val) {
            $platform = isset($val->chain->platform) ? $val->chain->platform : '';
            $val->chain_name = $platform;
            $val->url = Yii::$app->redis->hget('platform_brower_info', $platform);
            $val->total = (int)$val->total * 1e4;
        }
        $data = [
            'list' => $models,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize' => $size,
                'currentPage' => (int)$page,
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
        $id = Yii::$app->request->get('id', '');
        $data = null;
        if (empty($id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $data = CoinIssueCoin::find()->where(['id' => $id])->one();
        $platform = isset($data->chain->platform) ? $data->chain->platform : '';
        $data->total = (int)$data->total * 1e4;
        $data->issue_charge = rtrim(sprintf('%.3f', floatval($data->charge)), '0');
        $data->url = Yii::$app->redis->hget('platform_brower_info', $platform);
        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 申请操作管理
     * @param integer id
     * @param integer status
     * @param string msg
     * @return array
     */
    public function actionVerify()
    {
        $id = Yii::$app->request->post('id', '');
        $status = Yii::$app->request->post('status', '');
        $msg = Yii::$app->request->post('msg', '');
        if (false == $id || false == $status) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        if (!in_array($status, [CoinIssueCoin::STATUS_ALLOW, CoinIssueCoin::STATUS_REFUSE])) {
            $msg = '状态值错误';
            $code = -1;
            goto doEnd;
        }

        $model = CoinIssueCoin::findOne($id);
        if (false == $model) {
            $msg = '不存在的记录';
            $code = -1;
            goto doEnd;
        }
        $data = [
            'status' => $status,
            #'msg' => $msg
        ];
        $model->setScenario(CoinIssueCoin::SCENARIOS_UPDATE);
        $model->load($data, '');
        if (!$model->save()) {
            $msg = current($model->firstErrors);
            $code = -1;
            goto doEnd;
        }

        $record = CoinIssueChainRecord::find()->where(['issue_coin_id' => $id])->one();
        $record->finish_tx = 'failed';
        $record->finish_send_transaction = 'failed';
        $record->finish_query_transaction = $msg;
        $record->save();

        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }
}