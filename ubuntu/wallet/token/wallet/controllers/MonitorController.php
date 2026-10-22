<?php

namespace wallet\controllers;

use common\models\psources\CoinPlatformWithHold;
use common\service\chain33\Chain33Service;
use Yii;
use wallet\base\BaseController;
use yii\data\Pagination;

class MonitorController extends BaseController
{
    /**
     * landing
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        $msg = 'ok';
        $code = 0;
        $page = Yii::$app->request->get('page', 1);
        $query = CoinPlatformWithHold::find()->select('id, platform, address')->asArray();
        $count = $query->count();
        if( 0 == $count ) {
            $msg = '数据不存在';
            $code = -1;
            $data = null;
            goto doEnd;
        }
        $models = $query->offset(($page - 1) * 10)->limit(10)->asArray()->all();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => '10']);
        $address = [];
        foreach ($models as $model){
            $address[] = $model['address'];
        }
        if(empty($address)){
            $data = null;
            goto doEnd;
        }
        $service = new Chain33Service();
        $execer = 'coins';
        $result  = $service->getBalance($address, $execer);
        if(0 != $result['code']){
            $msg = $result['msg'];
            $code = -1;
            goto doEnd;
        }
        $result_balance = $result['result'];
        $balance_arr = [];
        foreach ($result_balance as $key => $val){
            $balance_arr[$val['addr']] = $val;
        }
        foreach ($models as &$model){
            $model['balance'] = $balance_arr[$model['address']]['balance'];
        }
        $data = [
            'list' => $models,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize'  => 10,
                'currentPage' => $page,
            ]
        ];
        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

}