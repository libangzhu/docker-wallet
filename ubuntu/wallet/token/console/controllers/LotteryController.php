<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/8
 * Time: 11:00
 */
namespace console\controllers;

use common\business\Chain33Business;
use common\business\LotteryBusiness;
use common\models\psources\CoinLottery;
use common\models\psources\CoinNewLottery;
use yii\console\Controller;
use Yii;

class LotteryController extends Controller
{

    /**
     * 交易并开奖
     */
    public function actionTradeAndLottery()
    {
        echo date('Y-m-d H:i:s').': '.'每天10点彩票活动准时和大家见面'.PHP_EOL;
        $lottery_config = Yii::$app->params['lottery'];
        $start_date = date('Y-m-d');
        $lotter_status = Yii::$app->redis_app->get('lottery_status');
        if($lotter_status == 'running'){
            exit;
        }
        Yii::$app->redis_app->set('lottery_status','running');
        while(true){
            try{
                //获取最近lottery状态
                $lottery_current_info = LotteryBusiness::getLotteryCurrentInfo();
                if($lottery_current_info['code'] != 0) {
                    echo '获取lottery_current_info失败: ' . $lottery_current_info['msg'].PHP_EOL;
                    sleep(2);
                    continue;
                }
                $lottery_current_result = $lottery_current_info['result'];
                //获取最新的区块高度
                $last_header = LotteryBusiness::getLastHeader();
                if ($last_header['code'] != 0) {
                    echo '获取区块高度失败: ' . $last_header['msg'].PHP_EOL;
                    sleep(2);
                    continue;
                }
                $last_header = $last_header['result'];
                //获取最新竞猜期数以及区块高度
                $last = CoinLottery::getMaxStage($start_date);
                if($last && $last->stage == $lottery_config['period_num'] && $last->status == 3){    //达到80期并且开完奖
                    echo date('Y-m-d H:i:s').': '.'今天彩票期数已达到80期,下期明天10点不见不散'.PHP_EOL;
                    Yii::$app->redis_app->set('lottery_status','ending');
                    exit;
                }
                $start_height = $lottery_current_result['lastTransToPurStateOnMain'];
                $last_lottery_height = $lottery_current_result['lastTransToDrawStateOnMain'];
                $round = $lottery_current_result['round'];
                if ($last) {
                    if($lottery_current_result['status'] == 2){    //交易状态，等待开奖
                        if($last_header['height'] >= $start_height + $lottery_config['period_total_step']){   //超过起始高度40个区块可以开奖
                            $lottery_result = LotteryBusiness::lottery();
                            if($lottery_result['code'] == 0){  //如果开奖操作成功
                                echo date('Y-m-d H:i:s').': '.'开奖成功 '.PHP_EOL;
                            }else{
                                echo date('Y-m-d H:i:s').': '.'开奖异常: '.$lottery_result['msg'].PHP_EOL;
                            }
                            sleep(10);
                        }else{
                            if($last->start_height != $start_height){
                                CoinLottery::addOneRecord($start_height,$last_lottery_height,$last->stage+1,$round,$start_date);
                                echo  date('Y-m-d H:i:s').': '.'总期数:'.$round." 交易数据添加成功!".PHP_EOL;
                            }
                            sleep(10);
                            continue;
                        }
                    }else if($lottery_current_result['status'] == 3){    //已开奖
                        if($last->status != 3 ){   //开奖成功后数据库记录还没更新
                            $last->status = 3;
                            $last->lottery_height = $last_lottery_height;
                            $last->result = $lottery_current_result['luckyNumber'];
                            $last->save();
                            echo  date('Y-m-d H:i:s').': '.'总期数:'.$round." 开奖数据更新成功!".PHP_EOL;
                        }
                        if($last_header['height'] > $last_lottery_height){       //当前区块高度大于上期开奖高度，可以进行交易进入下一期
                            $trade_result = LotteryBusiness::trade(1,12345,5,0);
                            if($trade_result['code'] == 0){
                                echo date('Y-m-d H:i:s').': '.'交易成功 '.PHP_EOL;
                                //CoinLottery::addOneRecord($start_height,$last_lottery_height,$last->stage+1,$round,$start_date);
                            }else{
                                echo date('Y-m-d H:i:s').': '.'参与交易异常: '.$trade_result['msg'].PHP_EOL;
                            }
                            sleep(60);
                        }
                    }
                } else {
                    //今天第一期
                    $stage        = 1;
                    if($lottery_current_result['status'] == 2){    //交易状态,等待开奖
                        if($last_header['height'] >= $start_height + $lottery_config['period_total_step']){   //超过起始高度40个区块可以开奖
                            CoinLottery::addOneRecord($start_height,$last_lottery_height,$stage,$round,$start_date);
                            $lottery_result = LotteryBusiness::lottery();
                            if($lottery_result['code'] == 0){  //如果开奖操作成功

                            }else{
                                echo date('Y-m-d H:i:s').': '.'开奖异常: '.$lottery_result['msg'].PHP_EOL;
                            }
                            sleep(10);
                        }
                    }else if($lottery_current_result['status'] == 3){  //开完奖状态，等待交易进入下一期
                        if($last_header['height'] > $last_lottery_height){       //当前区块高度大于上期开奖高度，可以进行交易进入下一期
                            $trade_result = LotteryBusiness::trade(1,12345,5,0);
                            if($trade_result['code'] == 0){
                                echo date('Y-m-d H:i:s').': '.'交易成功 '.PHP_EOL;
                            }else{
                                echo date('Y-m-d H:i:s').': '.'参与交易异常: '.$trade_result['msg'].PHP_EOL;
                            }
                            sleep(60);
                        }
                    }
                }
            }catch (\Exception $e){
                echo date('Y-m-d H:i:s').': '.'异常: '.$e->getMessage().PHP_EOL;
                sleep(10);
            }
        }
        Yii::$app->redis_app->set('lottery_status','ending');
        exit;
    }

    /**
     * 交易并开奖(新)
     */
    public function actionNewTradeAndLottery()
    {
        echo date('Y-m-d H:i:s').': '.'每天10点彩票活动准时和大家见面'.PHP_EOL;
        $lottery_config = Yii::$app->params['lottery'];
        $start_date = date('Y-m-d');
        $lotter_status = Yii::$app->redis_app->get('new_lottery_status');
        if($lotter_status == 'running'){
            exit;
        }
        Yii::$app->redis_app->set('new_lottery_status','running');
        while(true){
            try{
                //获取最近lottery状态
                $lottery_current_info = LotteryBusiness::getNewLotteryCurrentInfo();
                if($lottery_current_info['code'] != 0) {
                    echo '获取lottery_current_info失败: ' . $lottery_current_info['msg'].PHP_EOL;
                    sleep(2);
                    continue;
                }
                $lottery_current_result = $lottery_current_info['result'];
                //获取最新的区块高度
                $last_header = LotteryBusiness::getLastHeader();
                if ($last_header['code'] != 0) {
                    echo '获取区块高度失败: ' . $last_header['msg'].PHP_EOL;
                    sleep(2);
                    continue;
                }
                $last_header = $last_header['result'];
                //获取最新竞猜期数以及区块高度
                $last = CoinNewLottery::getMaxStage($start_date);
                if($last && $last->stage == $lottery_config['period_num'] && $last->status == 3){    //达到80期并且开完奖
                    echo date('Y-m-d H:i:s').': '.'今天彩票期数已达到80期,下期明天10点不见不散'.PHP_EOL;
                    Yii::$app->redis_app->set('new_lottery_status','ending');
                    exit;
                }
                $start_height = $lottery_current_result['lastTransToPurStateOnMain'];
                $last_lottery_height = $lottery_current_result['lastTransToDrawStateOnMain'];
                $round = $lottery_current_result['round'];
                if ($last) {
                    if($lottery_current_result['status'] == 2){    //交易状态，等待开奖
                        if($last_header['height'] >= $start_height + $lottery_config['period_total_step']){   //超过起始高度40个区块可以开奖
                            $lottery_result = LotteryBusiness::newlottery();
                            if($lottery_result['code'] == 0){  //如果开奖操作成功
                                echo date('Y-m-d H:i:s').': '.'开奖成功 '.PHP_EOL;
                            }else{
                                echo date('Y-m-d H:i:s').': '.'开奖异常: '.$lottery_result['msg'].PHP_EOL;
                            }
                            sleep(10);
                        }else{
                            if($last->start_height != $start_height){
                                CoinNewLottery::addOneRecord($start_height,$last_lottery_height,$last->stage+1,$round,$start_date);
                                echo  date('Y-m-d H:i:s').': '.'总期数:'.$round." 交易数据添加成功!".PHP_EOL;
                            }
                            sleep(10);
                            continue;
                        }
                    }else if($lottery_current_result['status'] == 3){    //已开奖
                        if($last->status != 3 ){   //开奖成功后数据库记录还没更新
                            $last->status = 3;
                            $last->lottery_height = $last_lottery_height;
                            $last->result = $lottery_current_result['luckyNumber'];
                            $last->save();
                            echo  date('Y-m-d H:i:s').': '.'总期数:'.$round." 开奖数据更新成功!".PHP_EOL;
                        }
                        if($last_header['height'] > $last_lottery_height){       //当前区块高度大于上期开奖高度，可以进行交易进入下一期
                            $trade_result = LotteryBusiness::newtrade(1,12345,5,0);
                            if($trade_result['code'] == 0){
                                echo date('Y-m-d H:i:s').': '.'交易成功 '.PHP_EOL;
                                //CoinLottery::addOneRecord($start_height,$last_lottery_height,$last->stage+1,$round,$start_date);
                            }else{
                                echo date('Y-m-d H:i:s').': '.'参与交易异常: '.$trade_result['msg'].PHP_EOL;
                            }
                            sleep(60);
                        }
                    }
                } else {
                    //今天第一期
                    $stage        = 1;
                    if($lottery_current_result['status'] == 2){    //交易状态,等待开奖
                        if($last_header['height'] >= $start_height + $lottery_config['period_total_step']){   //超过起始高度40个区块可以开奖
                            CoinNewLottery::addOneRecord($start_height,$last_lottery_height,$stage,$round,$start_date);
                            $lottery_result = LotteryBusiness::newlottery();
                            if($lottery_result['code'] == 0){  //如果开奖操作成功

                            }else{
                                echo date('Y-m-d H:i:s').': '.'开奖异常: '.$lottery_result['msg'].PHP_EOL;
                            }
                            sleep(10);
                        }
                    }else if($lottery_current_result['status'] == 3){  //开完奖状态，等待交易进入下一期
                        if($last_header['height'] > $last_lottery_height){       //当前区块高度大于上期开奖高度，可以进行交易进入下一期
                            $trade_result = LotteryBusiness::newtrade(1,12345,5,0);
                            if($trade_result['code'] == 0){
                                echo date('Y-m-d H:i:s').': '.'交易成功 '.PHP_EOL;
                            }else{
                                echo date('Y-m-d H:i:s').': '.'参与交易异常: '.$trade_result['msg'].PHP_EOL;
                            }
                            sleep(60);
                        }
                    }
                }
            }catch (\Exception $e){
                echo date('Y-m-d H:i:s').': '.'异常: '.$e->getMessage().PHP_EOL;
                sleep(10);
            }
        }
        Yii::$app->redis_app->set('new_lottery_status','ending');
        exit;
    }

    public function actionTrade()
    {
        $res = LotteryBusiness::trade(1,12345,5,0);
        var_dump($res);die;
    }

    public function actionLottery()
    {
        $res = LotteryBusiness::lottery();
        var_dump($res);
    }

    public function actionLotteryCurrentInfo()
    {
        $res = LotteryBusiness::getLotteryCurrentInfo();
        var_dump($res);
    }

    public function actionLotteryLuckyNumber()
    {
        $res = LotteryBusiness::getLuckyNumber(206);
        var_dump($res);
    }

    public function actionUnLock()
    {
        $res = Chain33Business::unLockWallet("beanwalletGD666");
        var_dump($res);die;
    }


}