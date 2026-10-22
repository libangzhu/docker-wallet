<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/12
 * Time: 10:08
 */

namespace common\service\chain33;

use Yii;
use common\helpers\Curl;

/**
 * Lottery平行链 区块链接口
 */
class LotteryService
{
    /**
     * @return string
     * 平行链上操作彩票
     */
    public static function urlLotteryBuild()
    {
        $config = Yii::$app->params['lottery'];
        $service = $config['service'];
        $scheme = $service['scheme'] ?? 'http';
        $host = $service['host'] ?? '127.0.0.1';
        $port = (string)$service['port'] ?? '';
        if ($port) {
            return $scheme . '://' . $host . ':' . $port;
        } else {
            return $scheme . '://' . $host;
        }
    }

    public static function jsonRpcBuild($params = [], $method = 'Chain33.Query')
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => 0,
            'method' => $method,
            'params' => [],
        ];
        if (!empty($params)) {
            $data['params'][] = $params;
        }
        return json_encode($data);
    }

    public function send($params = [], $method = 'Chain33.Query')
    {
        $ch = new Curl();
        $jsonrpc = self::jsonRpcBuild($params, $method);
        $ch->setHeader('Content-Type', 'application/json');
        $ch->setRawPostData($jsonrpc);
        $result = $ch->post(self::urlLotteryBuild(), false);
        if (!$result) {
            return ['code' => -1, 'msg' => $ch->errorText];
        }
        if (0 == $result['id'] && empty($result['error'])) {
            $result['code'] = $result['id'];
            unset($result['id']);
            return $result;
        } else {
            return ['code' => -1, 'msg' => $result['error']];
        }
    }


    /**
     * 参与交易(未签名)
     * @param $amount
     * @param $number
     * @param $way
     * @param $fee
     * @return array|mixed
     */
    public function trade($amount, $number, $way, $fee )
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['lotteryId'];
        $params = [
            "execer"        => "lottery",
            "actionName"    => "LotteryBuy",
            "payload"       => [
                "lotteryId"     => $lotterId,
                "amount"        => $amount,
                "number"        => $number,
                "way"           => $way,
                "fee"           => $fee,
            ]

        ];
        return $this->send($params, 'Chain33.CreateTransaction');
    }

    /**
     * 参与交易(未签名)
     * @param $amount
     * @param $number
     * @param $way
     * @param $fee
     * @return array|mixed
     */
    public function newtrade($amount, $number, $way, $fee )
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['new_lotteryId'];
        $params = [
            "execer"        => "lottery",
            "actionName"    => "LotteryBuy",
            "payload"       => [
                "lotteryId"     => $lotterId,
                "amount"        => $amount,
                "number"        => $number,
                "way"           => $way,
                "fee"           => $fee,
            ]

        ];
        return $this->send($params, 'Chain33.CreateTransaction');
    }

    /**
     * 开奖
     */
    public function lottery($fee)
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['lotteryId'];
        $params = [
            "execer"        => "lottery",
            "actionName"    => "LotteryDraw",
            "payload"       => [
                "lotteryId"     => $lotterId,
                "fee"           => $fee
            ]
        ];
        return $this->send($params, 'Chain33.CreateTransaction');
    }

    /**
     * 开奖
     */
    public function newlottery($fee)
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['new_lotteryId'];
        $params = [
            "execer"        => "lottery",
            "actionName"    => "LotteryDraw",
            "payload"       => [
                "lotteryId"     => $lotterId,
                "fee"           => $fee
            ]
        ];
        return $this->send($params, 'Chain33.CreateTransaction');
    }

    /**
     * 获取lottery当前信息
     */
    public function getLotteryCurrentInfo()
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['lotteryId'];
        $params = [
            "execer"        => 'lottery',
            "funcName"      => "GetLotteryCurrentInfo",
            "payload"       => ['lotteryId' => $lotterId]
        ];
        return $this->send($params, 'Chain33.Query');
    }

    /**
     * 获取lottery当前信息
     */
    public function getNewLotteryCurrentInfo()
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['new_lotteryId'];
        $params = [
            "execer"        => 'lottery',
            "funcName"      => "GetLotteryCurrentInfo",
            "payload"       => ['lotteryId' => $lotterId]
        ];
        return $this->send($params, 'Chain33.Query');
    }

    /**
     * @param $round
     * @return array|mixed
     * 获取中奖号码
     */
    public function getLuckyNumber($round)
    {
        $config = Yii::$app->params['lottery'];
        $lotterId = $config['lotteryId'];
        $params = [
            "execer"        => 'lottery',
            "funcName"      => "GetLotteryRoundLuckyNumber",
            "payload"       => ['lotteryId' => $lotterId,'round' => $round]
        ];
        return $this->send($params, 'Chain33.Query');
    }



    /**
     * @param $txhex
     * @return array|mixed
     * 签名
     */
    public function sign($key,$txhex)
    {
        $params = [
            //'addr' => $addr,
            'privkey' => $key,
            'txhex' => $txhex,
            'expire' => '3600s',
            'index' => 0
        ];
        return $this->send($params, 'Chain33.SignRawTx');
    }

    public function sendTransaction($data)
    {
        $params = [
           'data' => $data,
        ];
        return $this->send($params, 'Chain33.SendTransaction');
    }

    /**
     * 获取最新的区块
     */
    public function getLastHeader()
    {
        return $this->send([], 'Chain33.GetLastHeader');
    }

}