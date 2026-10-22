<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/5
 * Time: 10:15
 */
namespace h5\controllers;

use common\models\psources\CoinLottery;
use common\models\psources\CoinNewLottery;
use h5\base\ResponseBuild;
use Yii;
use h5\base\BaseController;

/**
 * Class CoinController
 * 时时彩控制器
 *
 * @package h5\controllers
 */
class LotteryController extends BaseController
{
    public function actionLotteryInfo()
    {
        $lottery_config = Yii::$app->params['lottery'];
        $period_num = $lottery_config['period_num'];
        $response_ = new ResponseBuild();
        $data = CoinLottery::getLastRecord();
        $data['end_round'] = $period_num - $data['stage'] + $data['round'];
        $response_->build(ResponseBuild::STATUS_SUCCEED, '', $data);
        return $response_;
    }

    /**
     * @return ResponseBuild
     * 新时时彩最近一期详情
     */
    public function actionNewLotteryInfo()
    {
        $lottery_config = Yii::$app->params['lottery'];
        $period_num = $lottery_config['period_num'];
        $response_ = new ResponseBuild();
        $data = CoinNewLottery::getLastRecord();
        $data['end_round'] = $period_num - $data['stage'] + $data['round'];
        $response_->build(ResponseBuild::STATUS_SUCCEED, '', $data);
        return $response_;
    }
}

