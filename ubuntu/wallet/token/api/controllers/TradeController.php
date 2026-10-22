<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/14
 * Time: 18:53
 */

namespace api\controllers;

use api\base\BaseController;
use common\models\psources\CoinHashMemo;
use common\models\psources\CoinTokenTransfer;
use common\service\chain33\Chain33Service;
use Yii;


class TradeController extends BaseController
{
    /**
     * 添加本地备注
     */
    public function actionAddMemo()
    {
        $request = Yii::$app->request;
        $hash = $request->post('hash');
        if (empty($hash)) {
            return ['code' => 1, 'data' => [], 'msg' => '交易hash值不能为空'];
        }
        $model = new CoinHashMemo();
        $model->setScenario(CoinHashMemo::SCENARIOS_CREATE);
        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return ['code' => 0, 'data' => [], 'msg' => '本地备注添加成功'];
        } else {
            return ['code' => 1, 'data' => [], 'msg' => current($model->firstErrors)];
        }
    }

    public function actionUpdateMemo()
    {
        $request = Yii::$app->request;
        $hash = $request->post('hash');
        $memo = $request->post('memo');
        if (empty($hash)) {
            return ['code' => 1, 'data' => [], 'msg' => '交易hash值不能为空'];
        }
        $model = CoinHashMemo::find()->where(['hash' => $hash])->one();
        if (empty($model)) {
            return ['code' => 1, 'data' => [], 'msg' => '记录不存在'];
        }

        CoinHashMemo::updateAll(['memo' => $memo], 'hash = :hash', [':hash' => $hash]);
        return ['code' => 0, 'data' => [], 'msg' => '本地备注更新成功'];
    }

    /**
     * 查看本地备注
     */
    public function actionGetMemo()
    {
        $request = Yii::$app->request;
        $hash = $request->post('hash');
        $version = $request->post('version', '');
        if (empty($hash)) {
            return ['code' => 1, 'data' => [], 'msg' => '交易hash值不能为空'];
        }
        $model = CoinHashMemo::find()->where(['hash' => $hash])->asArray()->one();
        if (empty($version)) {
            if (empty($model)) {
                return ['code' => 1, 'data' => '', 'msg' => '记录不存在'];
            }
            return ['code' => 0, 'data' => (string)sprintf("%0.4f", $model['memo'])];
        } else {
            if (empty($model)) {
                return ['code' => 0, 'data' => ['memo' => '', 'coins_fee' => ''], 'msg' => '记录不存在'];
            }
            return ['code' => 0, 'data' => [
                'memo' => $model['memo'],
                'coins_fee' => (string)sprintf("%0.4f", $model['coins_fee'])
            ]];
        }
    }

    public function actionSyncToDb()
    {
        $data = [];
        $result = Yii::$app->redis->HGETALL('trade_hash_memo');
        foreach ($result as $key => $val) {
            if (($key + 2) % 2 == 0) {
                $data[] = [
                    'hash' => $val,
                    'memo' => $result[$key + 1]
                ];
            }
        }
        foreach ($data as $val) {
            if ('0xc080c6a0937f25d1017e5e88bdcff5c3979810c8a6cc1f3a64cf50970da28fc9' == $val['hash']) continue;
            $coin_memo_hash = new CoinHashMemo();
            $coin_memo_hash->hash = $val['hash'];
            $coin_memo_hash->memo = $val['memo'];
            $coin_memo_hash->save();
        }
        exit;
    }

    public function actionAddressIsExist()
    {
        $get = Yii::$app->request->get();
        $to = $get['coin_address'] ?? '';
        if (empty($to)) {
            return ['code' => -1, 'msg' => '参数不能为空'];
        }

        $coinTokenTransfer = CoinTokenTransfer::find()->where(['coin_address' => $to, 'code' => 1])->one();
        if (!$coinTokenTransfer) {
            return ['code' => 0, 'msg' => 'record does not exist'];
        }

        return ['code' => 1, 'msg' => 'record already exists'];
    }


    public function actionTokenTransfer()
    {
        $get = Yii::$app->request->get();
        $to = $get['coin_address'] ?? '';
        if (empty($to)) {
            return ['code' => -1, 'msg' => '参数不能为空'];
        }

        $coinTokenTransfer = CoinTokenTransfer::find()->where(['coin_address' => $to, 'code' => 1])->one();
        if ($coinTokenTransfer) {
            return ['code' => -1, 'msg' => 'record already exists'];
        }

        $fee = 100000;
        $amount = 1 * 1e8;
        $note = '2050糖果';
        $execer = 'user.p.youngplus.token';
        $isToken = true;
        $tokenSymbol = 'YPLUS';


        $service = new Chain33Service();
        $createRawTransaction = $service->createTokenRawTransaction($to, $amount, $isToken, $tokenSymbol, $fee, $note, $execer);
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
        $coinTokenTransfer = new CoinTokenTransfer();
        $coinTokenTransfer->coin_address = $to;
        $coinTokenTransfer->msg = $msg;
        $coinTokenTransfer->code = $code;
        $coinTokenTransfer->save();

        return ['code' => $code, 'msg' => $msg];
    }
}