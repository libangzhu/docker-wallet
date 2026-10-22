<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 上午10:01
 */

namespace common\service\brower;

class Brower implements BrowerInterface
{
    const TRANS_OK      = 1;//交易成功
    const TRANS_FAIL    = -1;//交易失败
    const TRANS_PADDING = 0;//交易状态未知
    const ERROR         = 4;//接口返回错误
    public static $msg = [
        self::TRANS_OK      => 'SUCCEED',
        self::TRANS_FAIL    => 'FAIL',
        self::TRANS_PADDING => 'UNKNOW',
        self::ERROR         => 'ERROR',
    ];

    public function getStatus(string $txhash)
    {
        return ['code' => self::ERROR, 'msg' => 'Coin not supported!'];
    }

    public function StatusReturn($code, $msg = '', $data = [])
    {
        if ($msg) {
            return ['code' => $code, 'msg' => $msg, 'data' => $data];
        }
        return ['code' => $code, 'msg' => self::$msg[$code], 'data' => $data];
    }
}