<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/8
 * Time: 11:36
 */

namespace common\business;

use common\service\chain33\ChainService;
use common\service\chain33\LotteryService;
use Yii;

class LotteryBusiness
{
    /**
     * 参与/构造交易
     */
    public static function trade($amount, $number, $way, $fee)
    {
        $config = Yii::$app->params['lottery'];
        $key = $config['key'];
        $service = new LotteryService();
        $trade_result  = $service->trade($amount, $number, $way, $fee);
        if ($trade_result['code'] == 0) {   //交易成功
            $trade_txhex = $trade_result['result'];
            $sign_result = $service->sign($key,$trade_txhex);
            if($sign_result['code'] == 0){  //签名成功
                $data = $sign_result['result'];
                $send_transaction_result = $service->sendTransaction($data);
                if($send_transaction_result['code'] == 0){  //发送交易成功
                    return ['code' => 0,'result' => $send_transaction_result['result']];
                }else{
                    $error = $send_transaction_result['msg'];
                }
            }else{
                $error = $sign_result['msg'];
            }
        }else{
            $error = $trade_result['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * 参与/构造交易(新)
     */
    public static function newtrade($amount, $number, $way, $fee)
    {
        $config = Yii::$app->params['lottery'];
        $key = $config['key'];
        $service = new LotteryService();
        $trade_result  = $service->newtrade($amount, $number, $way, $fee);
        if ($trade_result['code'] == 0) {   //交易成功
            $trade_txhex = $trade_result['result'];
            $sign_result = $service->sign($key,$trade_txhex);
            if($sign_result['code'] == 0){  //签名成功
                $data = $sign_result['result'];
                $send_transaction_result = $service->sendTransaction($data);
                if($send_transaction_result['code'] == 0){  //发送交易成功
                    return ['code' => 0,'result' => $send_transaction_result['result']];
                }else{
                    $error = $send_transaction_result['msg'];
                }
            }else{
                $error = $sign_result['msg'];
            }
        }else{
            $error = $trade_result['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * 开奖
     */
    public static function lottery($fee = 0)
    {
        $config = Yii::$app->params['lottery'];
        $key = $config['createor_key'];
        $error = '';
        $service = new LotteryService();
        $lottery_result  = $service->lottery($fee);
        if ($lottery_result['code'] == 0) {   //交易成功
            $lottery_txhex = $lottery_result['result'];
            $sign_result = $service->sign($key,$lottery_txhex);
            if($sign_result['code'] == 0){  //签名成功
                $data = $sign_result['result'];
                $send_transaction_result = $service->sendTransaction($data);
                if($send_transaction_result['code'] == 0){  //发送交易成功
                    return ['code' => 0,'result' => $send_transaction_result['result']];
                }else{
                    $error = $send_transaction_result['msg'];
                }
            }else{
                $error = $sign_result['msg'];
            }
        }else{
            $error = $lottery_result['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * 开奖(新)
     */
    public static function newlottery($fee = 0)
    {
        $config = Yii::$app->params['lottery'];
        $key = $config['createor_key'];
        $error = '';
        $service = new LotteryService();
        $lottery_result  = $service->newlottery($fee);
        if ($lottery_result['code'] == 0) {   //交易成功
            $lottery_txhex = $lottery_result['result'];
            $sign_result = $service->sign($key,$lottery_txhex);
            if($sign_result['code'] == 0){  //签名成功
                $data = $sign_result['result'];
                $send_transaction_result = $service->sendTransaction($data);
                if($send_transaction_result['code'] == 0){  //发送交易成功
                    return ['code' => 0,'result' => $send_transaction_result['result']];
                }else{
                    $error = $send_transaction_result['msg'];
                }
            }else{
                $error = $sign_result['msg'];
            }
        }else{
            $error = $lottery_result['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * @return array
     * 获取当前彩票信息
     */
    public static function getLotteryCurrentInfo()
    {
        $service = new LotteryService();
        $lottery_current_info  = $service->getLotteryCurrentInfo();
        if ($lottery_current_info['code'] == 0) {   //交易成功
            $result = $lottery_current_info['result'];
            return ['code' => 0,'result' => $result];
        }else{
            $error = $lottery_current_info['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * @return array
     * 获取当前彩票信息(新)
     */
    public static function getNewLotteryCurrentInfo()
    {
        $service = new LotteryService();
        $lottery_current_info  = $service->getNewLotteryCurrentInfo();
        if ($lottery_current_info['code'] == 0) {   //交易成功
            $result = $lottery_current_info['result'];
            return ['code' => 0,'result' => $result];
        }else{
            $error = $lottery_current_info['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * @param $round
     * 获取指定期数中奖号码
     */
    public static function getLuckyNumber($round)
    {
        $service = new LotteryService();
        $lottery_current_info  = $service->getLuckyNumber($round);
        if ($lottery_current_info['code'] == 0) {   //交易成功
            $result = $lottery_current_info['result'];
            return ['code' => 0,'result' => $result];
        }else{
            $error = $lottery_current_info['msg'];
        }
        return ['code' => -1, 'msg' => $error];
    }

    /**
     * 获取最新的区块
     */
    public static function getLastHeader()
    {
        $service = new ChainService();
        $result  = $service->getLastHeader();
        return $result;
    }
}
