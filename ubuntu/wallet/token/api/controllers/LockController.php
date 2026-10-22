<?php

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\psources\LockEs;
use common\models\psources\LockCreator;

class LockController extends BaseController
{
    public function actionLockCreator()
    {
        $lock_creator = new LockCreator();
        $lock_creator->primaryKey = 1;//primaryKey 定义 _id

        $lock_creator->creator = 'fei';
        $lock_creator->create = [
            'means' => 'fix_amount',
            'fix_amount' => [
                'period' => 2400,
                'amount' => 153500
            ],
            'left_proportion' => [
                'period' => 2400,
                'tenThousandth' => 153500
            ],
            'total_count' => 4600000,
            'start_time' => 6430000,
            'asset_symbol' => 'bty',
            'asset_exec' => 'coins',
        ];
        $lock_creator->unfreeze_id = 'the-id';
        $lock_creator->block = [
            'hash' => '',
            'height' => 2,
            'ts' => 6632560,
            'index' => 2
        ];
        $lock_creator->action_type = 'create'; //create/withdraw/terminate
        $lock_creator->beneficiary = 'xuan';
        $lock_creator->success = true;
        $lock_creator->withdraw = [
            'amount' => 2000021
        ];
        $lock_creator->terminate = [
            'amount_left' => 2024121,
            'amount_back' => 1536584
        ];
        var_dump($lock_creator->save());
        exit;
    }

    public function actionSearch()
    {
        $data = null;
        $msg = 'ok';
        $code = 0;
        $action_type = Yii::$app->request->get('action_type', '');
        $beneficiary = Yii::$app->request->get('beneficiary','');

        if(empty($action_type) || empty($beneficiary)){
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }

        $lock_creator = new LockEs();
        $query_arr = [
            "bool" => [
                "must" => [
                    ['match' => ['action_type' => $action_type]],
                    ['match' => ['beneficiary' => $beneficiary]],
                    ['match' => ['success' => true]],
                ]
            ],
        ];
        $resp = $lock_creator::find()->query($query_arr)->asArray()->limit(100)->all();
        if(empty($resp)){
            $msg = '数据为空';
            $code = -1;
            goto doEnd;
        }
        foreach ($resp as $key => $val){
            $data[] = [
                'block' => [
                    'height' => $val['_source']['block']['height'],
                    'ts' => $val['_source']['block']['ts'],
                    'hash' => $val['_source']['block']['hash'],
                    'index' => $val['_source']['block']['index'],
                    'send' => $val['_source']['block']['send'],
                    'txHash' => $val['_source']['block']['txHash'],
                ],
                'creator' => $val['_source']['creator'],
                'beneficiary' => $val['_source']['beneficiary'],
                'unfreeze_id' => $val['_source']['unfreeze_id'],
                'success' => $val['_source']['success'],
                'action_type' => $val['_source']['action_type'],
                'create' => [
                    'start_time' => $val['_source']['create']['start_time'],
                    'asset_exec' => $val['_source']['create']['asset_exec'],
                    'asset_symbol' => $val['_source']['create']['asset_symbol'],
                    'total_count' => $val['_source']['create']['total_count'],
                    'means' => $val['_source']['create']['means'],
//                'left_proportion' => [
//                    'period' => $val['_source']['create']['left_proportion']['period'],
//                    'tenThousandth' => $val['_source']['create']['left_proportion']['tenThousandth']
//                ],
                ]
            ];
        }

        doEnd :

        return ['code' => $code, 'data' => $data, 'msg' => $msg];;
    }

    public function actionDeleteCreator()
    {
        $lock_creator = new LockCreator();
        $lock_creator_v2 = new LockEs();
        var_dump($lock_creator->deleteIndex(),$lock_creator_v2->deleteIndex());
        exit;
    }

    public function actionLockBlock()
    {

    }

    public function actionLockBeneficiary()
    {

    }
}