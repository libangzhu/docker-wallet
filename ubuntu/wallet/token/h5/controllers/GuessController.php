<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 上午10:23
 */

namespace h5\controllers;

use h5\constant\ResponseConstant;
use Yii;
use h5\base\ResponseBuild;
use h5\base\BaseController;
use common\models\psources\CoinMidGuess;
use common\models\psources\CoinActivity;
use common\business\Chain33Business;

class GuessController extends BaseController
{
    /**
     * 获取新手投注机会
     */
    public function actionGetNewBeeChance()
    {
        $request = Yii::$app->request->post();
        $field   = ['guessNumber', 'address', 'deviceId', 'activity'];

        $guessNumber = '';
        $activity    = '';
        $deviceId    = '';
        $address     = '';

        $response = new ResponseBuild();
        foreach ($field as $key => $value) {
            if (isset($request[$value]) && !empty($request[$value])) {
                $$value = $request[$value];
            } else {
                $response->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE, $value . ' can not be blank');
                return $response;
            }
        }
        $activity = strtoupper($activity);

        $coin_activity = CoinActivity::isValiable($activity);
        if ($coin_activity) {
            $count = CoinMidGuess::existsCount($deviceId, $address);
            if ($count >= 3) {
                $response->build(-1, '用户经参次数达到上限');
            } else {
                //打币,返回txhash
                $config  = Yii::$app->params['h5_activity'][$activity];
                $from    = $config['from'];
                $to      = $config['to'];
                $isToken = $config['isToken'];
                $token   = $config['token'];
                $amount  = intval($config['amount'] * 1e8);
                $note    = $guessNumber . ' ' . $address;
                $result  = Chain33Business::transToken($from, $to, $amount, $note, $isToken, $token);
                if (true) {
                    //添加数据库记录
                    $model            = new CoinMidGuess();
                    $model->device_id = $deviceId;
                    $model->address   = $address;
                    $model->txhash    = $result;
                    $model->aid       = $coin_activity->id;
                    if ($model->save()) {
                        $response->build(ResponseBuild::STATUS_SUCCEED, '', $result);
                    }
                } else {
                    $response->build($result['code'], $result['msg']);
                }
            }
        } else {
            $response->build(-1, '活动已经失效');
        }

        return $response;
    }

    public function actionGetVoteRecordByAddress()
    {
        $response = new ResponseBuild();
        $post     = Yii::$app->request->post();
        $address  = $post['address'] ?? '';
        $activity = $post['activity'] ?? '';
        if (empty($address) || empty($activity)) {
            $response->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE, '');
            return $response;
        }
        $coin_activity = CoinActivity::isExists($activity);

        if (!$coin_activity) {
            $response->build(-1, '活动不存在!');
            return $response;
        }

        $info = CoinMidGuess::find()->where(['address' => $address, 'aid' => $coin_activity->id])->asArray()->all();
        if ($info) {
            $txhash = array_column($info, 'txhash');
            $response->build(ResponseBuild::STATUS_SUCCEED, '', $txhash);
        } else {
            $response->build(1, '记录不存在');
        }
        return $response;
    }

}