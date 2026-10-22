<?php
/**
 * Created by PhpStorm.
 * User: shaji
 * Date: 2019/4/19
 * Time: 16:14
 */

namespace api\controllers;

use api\base\BaseController;
use common\business\Chain33Business;
use common\models\psources\CoinDailyStatistics;
use common\models\psources\CoinDailyNumbers;
use common\models\psources\CoinDailyTimes;
use Yii;

class VisitStatisticsController extends BaseController
{
    /**
     * 单个DAPP停留时长
     *
     * @param  string $dapp_name
     * @param  string $identify
     * @param  string $duration
     * @param  string $dapp_address
     * @param  string $coins_address
     * @param  string $platform_id
     * @return array
     */

    public function actionIndex()
    {
        $dapp_name = Yii::$app->request->get('dapp_name', '');
        $identify = Yii::$app->request->get('identify', '');
        $duration = Yii::$app->request->get('duration', '');
        $dapp_address = Yii::$app->request->get('dapp_address', '');
        $coins_address = Yii::$app->request->get('coins_address', '');
        $platform_id = Yii::$app->request->get('platform_id', '');

        if(empty($dapp_name) || empty($identify) || empty($duration) || empty($dapp_address) || empty($coins_address) || empty($platform_id)){
            return ['code' => -1,'data' => [], 'msg' => '缺少必要的参数'];
        }

        $isExistModel = CoinDailyStatistics::find()->where(['identify' => $identify, 'platform_id' => $platform_id])->one();
        if(!$isExistModel) {
            $model = new CoinDailyStatistics();
            $model->dapp_name = $dapp_name;
            $model->dapp_address = $dapp_address;
            $model->duration = $duration;
            $model->identify = $identify;
            $model->coins_address = $coins_address;
            $model->platform_id = $platform_id;
            $model->save();
            return ['code' => 0,'data' => [], 'msg' => '数据记录插入成功'];
        }

        if($duration > $isExistModel->duration){
            $isExistModel->duration = $duration;
            $isExistModel->save();
            return ['code' => 0,'data' => [], 'msg' => '数据记录更新成功'];
        }
    }

    /**
     * 日打开/进入DAPP的个数/次数
     *
     * @param  string $dapp_name
     * @param  string $dapp_address
     * @param  string $coins_address
     * @param  string $platform_id
     * @return array
     */

    public function actionDailyTimes()
    {
        $dapp_name = Yii::$app->request->get('dapp_name', '');
        $dapp_address = Yii::$app->request->get('dapp_address', '');
        $coins_address = Yii::$app->request->get('coins_address', '');
        $platform_id = Yii::$app->request->get('platform_id', '');

        if(empty($dapp_name) || empty($dapp_address) || empty($coins_address) || empty($platform_id)){
            return ['code' => -1,'data' => [], 'msg' => '缺少必要的参数'];
        }

        $begin = date("Y-m-d",time())." 00:00:00";
        $end = date("Y-m-d",time())." 23:59:59";
        $isExist_coinDailyNumbers = CoinDailyNumbers::find()
                                ->where(['dapp_name' => $dapp_name, 'dapp_address' => $dapp_address, 'coins_address' => $coins_address, 'platform_id' => $platform_id])
                                ->andWhere(['between', 'create_time', $begin, $end])
                                ->one();
        if(!$isExist_coinDailyNumbers){
            $model_coinDailyNumbers = new CoinDailyNumbers();
            $model_coinDailyNumbers->setScenario(CoinDailyNumbers::SCENARIOS_CREATE);
            $model_coinDailyNumbers->load(Yii::$app->request->get(),'');
            $model_coinDailyNumbers->save();
        }

        $model_coinDailyTimes = new CoinDailyTimes();
        $model_coinDailyTimes->setScenario(CoinDailyTimes::SCENARIOS_CREATE);
        if($model_coinDailyTimes->load(Yii::$app->request->get(),'') && $model_coinDailyTimes->save()) {
            return ['code' => 0,'data' => null, 'msg' => '数据记录插入成功'];
        } else {
            return ['code' => -1,'data' => null, 'msg' => current($model_coinDailyTimes->firstErrors)];
        }
    }


    /**
     * 获取coin地址下DAPP交易信息
     *
     * @param  string $coins_address
     * @return array
     */
    public function actionDappResult()
    {
        $coins_address = Yii::$app->request->get('coins_address', '');

        if(empty($coins_address)){
            return ['code' => -1,'data' => [], 'msg' => '缺少必要的参数'];
        }

        $service = new Chain33Business();
        $result  = $service->getDappTradeResult($coins_address);

        if( 0 !== $result['code']){
            return ['code' => -1,'data' => [], 'msg' => $result['msg']];
        }

        $queryResultItems = $result['result'] ?? [];
        if(empty($queryResultItems)){
            return ['code' => -1,'data' => [], 'msg' => 'error'];
        }

        $resultJSON = json_decode($queryResultItems['queryResultItems'][0]['resultJSON'],true);

        $betting_times = $resultJSON['betting_times'] ?? 0;
        $betting_amount = $resultJSON['betting_amount'] ?? 0;
        $earnings = $resultJSON['earnings'] ?? 0;

        $data = [
            'betting_times' => $betting_times,
            'betting_amount' => $betting_amount,
            'earnings' => $earnings
        ];

        return ['code' => 0,'data' => $data, 'msg' => 'ok'];
    }
}