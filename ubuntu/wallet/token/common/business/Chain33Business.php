<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-18 10:21:53
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\business;

use common\service\chain33\Chain33Service;

/**
 * chain33 区块链接口
 */
class Chain33Business
{
    /**
     * 查询地址下的token合约下的token资产
     *
     * @param  string $token
     * @param  string $address
     * @return array
     */
    public static function getAccountTokenAssets($address, $token)
    {
        $service = new Chain33Service();
        $result  = $service->getAccountTokenAssets($address, $token);
        if ($result['code'] == 0) {
            $result = $result['result'];
            $result = array_column($result, null, 'addr');
            if (isset($result[$address])) {
                $tokenAssets = $result[$address]['balance'];
                return ['code' => 0, 'amount' => $tokenAssets];
            }
            return ['code' => -1, 'msg' => '资产信息不存在'];
        }
        return $result;
    }

    /**
     * 批量获取token资产
     *
     * @param array $list
     * @return array
     */
    public static function getMulAccountTokenAssets($list)
    {
        foreach ($list as $k => &$v) {
            $temp = self::getAccountTokenAssets($v['address'], $v['coin_name_en']);
            if ($temp['code'] == 0) {
                $v['amount'] = $temp['amount'];
            } else {
                $v['amount'] = 0;
            }
        }
        return array_column($list, 'amount', 'id');
    }

    /**
     * 提币接口
     *
     * @param        $from
     * @param        $to
     * @param        $amount
     * @param string $note
     * @param bool   $isToken
     * @param string $tokenSymbol
     * @return array|string
     */
    public static function extractToken($from, $to, $amount, $note = "", $isToken = true, $tokenSymbol = "")
    {
        $service = new Chain33Service();
        $result  = $service->extractToken($from, $to, $amount, $note, $isToken, $tokenSymbol);
        if ($result['code'] == 0) {
            return $result['result']['hash'];
        }
        return ['code' => -1, 'msg' => $result['msg']];
    }

    /**
     * 转账接口
     *
     * @param        $from
     * @param        $to
     * @param        $amount
     * @param string $note
     * @param bool   $isToken
     * @param string $tokenSymbol
     * @return array|string
     */
    public static function transToken($from, $to, $amount, $note = "", $isToken = true, $tokenSymbol = "", $node_params = [])
    {
        $service = new Chain33Service($node_params);
        $result  = $service->transToken($from, $to, $amount, $note, $isToken, $tokenSymbol);
        if ($result['code'] == 0) {
            return $result['result']['hash'];
        }
        return ['code' => -1, 'msg' => $result['msg']];
    }

    public static function tradeToken($amount, $execName, $fee, $isToken = True, $to, $tokenSymbol = '', $node_params = [])
    {
        $service = new Chain33Service($node_params);
        $result  = $service->structureTrade($amount, $execName, $fee, $isToken, $to, $tokenSymbol);
        if ($result['code'] != 0 ) {
            return ['code' => -1, 'msg' => $result['error']];
        }
        $signBefore = $result['result'];
        $result  = $service->signTrade($addr = '1AEoZD1fKUGjGtxkXS7j1zieVWSkrA1fLn', $expire = "2h", $signBefore);
        if ($result['code'] != 0 ) {
            return ['code' => -1, 'msg' => $result['error']];
        }
        $signAfter = $result['result'];
        $result  = $service->sendTrade($signAfter);
        if ($result['code'] != 0 ) {
            return ['code' => -1, 'msg' => $result['error']];
        }
        return $result['result'];
    }

    /**
     * 获取区块hash
     *
     * @param  integer $height
     * @return array
     */
    public static function getBlockHashByHeight($height)
    {
        $service = new Chain33Service();
        return $service->getBlockHashByHeight($height);
    }

    public static function getProperFee()
    {
        $service = new Chain33Service();
        return $service->getProperFee();
    }

    /**
     * 获取coin地址下DAPP交易信息
     * @param  string $address
     * @return array
     */
    public static function getDappTradeResult($address)
    {
        $service = new Chain33Service();
        $execer = 'wasm';
        $funcName = 'WasmGetContractTable';
        $contractName = 'user.p.tschain.user.wasm.dice';
        $items[] = [
            'tableName' => 'addrinfo',
            'key' =>  'addrinfo-' . $address
        ];
        return $service->chain33Query($execer, $funcName, $contractName, $items);
    }

    /*
     * 获取游戏状态
     * @return array
     */
    public static function getGameStatus($node_params = [])
    {
        $service = new Chain33Service($node_params);
        $execer = 'wasm';
        $funcName = 'WasmGetContractTable';
        $contractName = $node_params['contractName'];
        $items[] = [
            'tableName' => 'gamestatus',
            'key' => 'dice_statics'
        ];
        return $service->chain33Query($execer, $funcName, $contractName, $items);
    }

    /*
     * 获取投注状态
     * @param integer $start
     * @param integer $end
     * @return array
     */
    public static function getBetStatus($start, $end, $roundArr = [], $node_params = [])
    {
        $service = new Chain33Service($node_params);
        $execer = 'wasm';
        $funcName = 'WasmGetContractTable';
        $contractName = $node_params['contractName'];
        if (empty($roundArr)) {
            for($i = $start + 1; $i <= $end; $i++){
                $items[] = [
                    'tableName' => 'roundinfo',
                    'key' => 'round:' . $i
                ];
            }
        }
        if(!empty($roundArr)){
            foreach($roundArr as $key => $round){
                $items[] = [
                    'tableName' => 'roundinfo',
                    'key' => 'round:' . $round
                ];
            }
        }

        return $service->chain33Query($execer, $funcName, $contractName, $items);
    }

    /*
     * 获取最新的区块头
     * @param null
     * @return array
     */
    public static function getLastHeader($node_params = [])
    {
        $service = new Chain33Service($node_params);
        return $service->getLastHeader();
    }

    /**
     * 获取地址下的所有交易记录
     *
     * @param         $addr
     * @param integer $flag   flag: 0:addr 的所有交易;1:当 addr 为发送方时的交易;2:当 addr 为接收
     *                        方时的交易。
     * @param         $count
     * @param         $direction
     * @param integer $height height: 交易所在的 block 高度,-1:表示从最新的开始向后取;大于等于 0 的
     *                        值,从具体的高度+具体 index 开始取(不包括height)。
     * @param integer $index  交易所在 block 中的索引,取值 0--100000
     * @return array
     */
    public static function getTxByAddr($addr, $flag, $count, $direction, $height, $index)
    {
        $service = new Chain33Service();
        return $service->getTxByAddr($addr, $flag, $count, $direction, $height, $index);
    }

    /**
     * 获取用户首次交易区块信息
     *
     * @param string $address
     * @param int    $flag
     * @return boolean|array
     */
    public static function getAddressFirstTransInfo($address, $flag)
    {
        $trans = Chain33Business::getTxByAddr($address, $flag, 1, 1, 0, 0);
        if ($trans['code'] == 0) {
            return $trans['result']['txInfos'][0];
        }
        return false;
    }

    /**
     * 获取用户最新交易区块信息
     *
     * @param string $address
     * @param int    $flag
     * @return boolean|array
     */
    public static function getAddressLastTransInfo($address, $flag)
    {
        $trans = Chain33Business::getTxByAddr($address, $flag, 1, 0, -1, 0);
        if ($trans['code'] == 0) {
            return $trans['result']['txInfos'][0];
        }
        return false;
    }

    /**
     * 获取高度区间内的所有 txhash
     *
     * @param string $addr
     * @param int    $flag
     * @param int    $direction
     * @param int    $start_height
     * @param int    $end_height
     * @return boolean|array
     */
    public static function getTxByAddrInRange($addr, $flag, $direction, $start_height, $end_height)
    {
        $result     = [];
        $trans_info = Chain33Business::getTxByAddr($addr, $flag, 0, 0, -1, 0);
        if ($trans_info['code'] == 0) {
            $result = $trans_info['result']['txInfos'];
        }
//        $first_info = Chain33Business::getAddressFirstTransInfo($addr, $flag);
//        if ($first_info) {
//            $height = $first_info['height'];
//            if ($height <= $start_height) {
//                //获取开始高度之后的所有信息
//                if ($height == $start_height) {
//                    $result = array_merge($result, $first_info);
//                }
//                $trans_info = Chain33Business::getTxByAddr($addr, $flag, 0, 1, $start_height, 0);
//                var_dump($trans_info);
//                if ($trans_info['code'] == 0) {
//                    $trans_info = $trans_info['result']['txInfos'];
//                    $result     = array_merge(array_column($trans_info, null, 'height'));
//                }
//            } else {
//                $last_info = Chain33Business::getAddressLastTransInfo($addr, $flag);
//                if ($last_info) {
//                    $height = $last_info['height'];
//                    if ($height == $end_height) {
//                        $result = array_merge($result, $first_info);
//                    }
//                    if ($height <= $end_height) {
//                        $height = -1;
//                    } else {
//                        $height = $end_height;
//                    }
//                    $trans_info = Chain33Business::getTxByAddr($addr, $flag, 0, 0, $height, 0);
//                    if ($trans_info['code'] == 0) {
//                        $trans_info = $trans_info['result']['txInfos'];
//                        $result     = array_merge(array_column($trans_info, null, 'height'));
//                    }
//                }
//            }
//        }
        foreach ($result as $key => $item) {
            if ($item['height'] > $end_height || $item['height'] < $start_height) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * @param array $hashes
     * @return array
     */
    public static function getTxByHashs($hashes, $node_params = [])
    {
        $service = new Chain33Service($node_params);
        return $service->getTxByHashes($hashes);
    }

    /**
     * @param $password
     * 解锁钱包
     */
    public static function unLockWallet($password, $node_params = [])
    {
        $service = new Chain33Service($node_params);
        return $service->unLockWallet($password);
    }

}
