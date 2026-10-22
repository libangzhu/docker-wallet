<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-30
 * Time: 下午3:09
 */

namespace api\controllers;

use common\models\pwallet\Bill;
use Yii;
use api\base\BaseController;
use api\job\BillJob;

class BillController extends BaseController
{
    /**
     * 插入交易记录
     */
    public function actionInsert()
    {
        $post  = Yii::$app->request->post();
        $model = new Bill();
        if ($model->load($post) && $model->validate()) {
            $model->status = 0;
            try {
                if ($model->save(false)) {
                    //加入队列
                    Yii::$app->queue->push(new BillJob(['id' => $model->id, 'txid' => $model->txid, 'chain' => $model->chain, 'from' => $model->from]));
                    return ['code' => 0, 'msg' => 'succeed'];
                }
            } catch (\Exception $exception) {
                return ['code' => $exception->getCode(), 'msg' => '保存失败'];
            }
        } else {
            return ['code' => -1, 'msg' => '数据验证失败'];
        }
    }

    public function actionBillList()
    {
        $post     = Yii::$app->request->post();
        $from     = $post['from'] ?? '';
        $coinname = $post['coinname'] ?? '';
        $chain    = $post['chain'] ?? '';
        $page     = $post['page'] ?? 1;
        $limit    = $post['limit'] ?? 10;
        if (empty($from)) {
            return ['code' => -1, 'msg' => '数据不存在'];
        }
        $condition = ['AND'];
        if (!empty($coinname)) {
            $condition[] = "`coinname`='$coinname'";
        }
        if (!empty($chain)) {
            $condition[] = "`chain`='$chain'";
        }
        if (!empty($condition)) {
            $condition[] = ['OR', "`from`='$from'", "`to`='$from'"];
        } else {
            $condition = ['OR', ['from' => $from], ['to' => $from]];
        }
        $result = Bill::getList2($page, $limit, $condition,['blocktime'=>SORT_DESC]);
        foreach ($result['data'] as $key => $item) {
            if ($item['from'] != $from) {
                $result['data'][$key]['type'] = 1;
            }
        }
        return $result;
    }
}