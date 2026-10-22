<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-18 10:28:23
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\service\chain33;

use Yii;
use common\helpers\Curl;

/**
 * chain33 区块链接口
 */
class Chain33Service
{
    private $node_params;

    public function __construct($parameter = [])
    {
        if (empty($parameter)) {
            $this->node_params = Yii::$app->params['chain33'];
        } else {
            $this->node_params = $parameter;
        }
    }

    public function urlBuild()
    {
        $config = $this->node_params;
        $scheme = $config['scheme'] ?? 'http';
        $host = $config['host'] ?? '127.0.0.1';
        $port = (string)$config['port'] ?? '';
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

    public function send($params = [], $method = 'Chain33.Query', $timeout = [])
    {
        $ch = new Curl($timeout);
        $jsonrpc = self::jsonRpcBuild($params, $method);
        $ch->setHeader('Content-Type', 'application/json');
        $ch->setRawPostData($jsonrpc);
        $result = $ch->post($this->urlBuild(), false);
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
     * 获取coin地址下DAPP交易信息
     *
     * @param  string|array $address
     * @return array
     */
    public function getDappTradeResult($address)
    {
        $params = [
            'execer' => 'wasm',
            'funcName' => 'WasmGetContractTable',
            'payload' => [
                'contractName' => 'user.p.tschain.user.wasm.dice',
                'items' => [
                    [
                        'tableName' => 'addrinfo',
                        'key' => 'addrinfo-' . $address
                    ]
                ]
            ]
        ];
        return $this->send($params);
    }

    /**
     * Chain33服务查询
     * @param  string $execer
     * @param  string $funcName
     * @param  string $contractName
     * @param  array $items
     * @return array
     */
    public function chain33Query($execer = 'wasm', $funcName = 'WasmGetContractTable', $contractName = 'user.p.para.user.wasm.dice', $items = [])
    {
        $params = [
            'execer' => $execer,
            'funcName' => $funcName,
            'payload' => [
                'contractName' => $contractName,
                'items' =>
                    $items
            ]
        ];
        return $this->send($params);
    }


    /**
     * 获取地址下的所有token资产
     *
     * @param  string|array $address
     * @param  string $symbol
     * @return array
     */
    public function getAccountTokenAssets($address, $symbol)
    {
        if (!is_array($address)) {
            $address = [$address];
        }
        $params = [
            "addresses" => $address,
            "execer" => "user.p.guodun.token",
            "tokenSymbol" => $symbol
        ];
        return $this->send($params, 'token.GetTokenBalance');
    }

    /**
     * 提币
     *
     * @param        $from
     * @param        $to
     * @param        $amount
     * @param string $note
     * @param bool $isToken
     * @param string $tokenSymbol
     * @return array
     */
    public function extractToken($from, $to, $amount, $note = '', $isToken = true, $tokenSymbol = '')
    {
        $params = [
            "from" => $to,
            "to" => $from,
            "amount" => -$amount,
            "note" => $note,
            "isToken" => $isToken ? true : false,
            "tokenSymbol" => strtoupper($tokenSymbol)
        ];
        return $this->send($params, 'Chain33.SendToAddress');
    }

    /**
     *  token 转账
     *
     * @param        $from
     * @param        $to
     * @param        $amount
     * @param string $note
     * @param bool $isToken
     * @param string $tokenSymbol
     * @return array
     */
    public function transToken($from, $to, $amount, $note = '', $isToken = true, $tokenSymbol = '')
    {
        $params = [
            "from" => $from,
            "to" => $to,
            "amount" => $amount,
            "note" => $note,
            "isToken" => $isToken ? true : false,
            "tokenSymbol" => strtoupper($tokenSymbol)
        ];
        return $this->send($params, 'Chain33.SendToAddress');
    }

    public function createTokenRawTransaction($to, $amount, $isToken, $tokenSymbol, $fee, $note, $execer)
    {
        $params = [
            "to" => $to,
            "amount" => $amount,
            "isToken" => $isToken,
            "tokenSymbol" => $tokenSymbol,
            "fee" => $fee,
            "note" => $note,
            "execer" => $execer,

        ];
        return $this->send($params, 'Chain33.CreateRawTransaction');
    }

    public function createRawTransaction($to, $amount, $fee, $note, $execer)
    {
        $params = [
            "to" => $to,
            "amount" => $amount,
            "fee" => $fee,
            "note" => $note,
            "execer" => $execer,
        ];
        return $this->send($params, 'Chain33.CreateRawTransaction');
    }

    public function signRawTx($privkey, $txHex, $expire)
    {
        $params = [
            'privkey' => $privkey,
            'txHex' => $txHex,
            'expire' => $expire
        ];
        return $this->send($params, 'Chain33.SignRawTx');
    }

    public function sendTransaction($data)
    {
        $params = [
            'data' => $data
        ];
        $timeout = [
            'connect_timeout' => 50000,
            'timeout_ms' => 50000
        ];
        return $this->send($params, 'Chain33.SendTransaction', $timeout);
    }

    public function QueryTransaction($data)
    {
        $params = [
            'hash' => $data
        ];
        return $this->send($params, 'Chain33.QueryTransaction');
    }

    public function query()
    {
        $params = [
            "execer" => 'manage',
            "funcName" => "GetConfigItem",
            "payload" => ['data' => 'token-finisher']
        ];
        return $this->send($params, 'Chain33.Query');
    }

    public function getBalance($address, $execer = "")
    {
        $params = [
            'addresses' => [$address],
            'execer' => $execer,
        ];
        return $this->send($params, 'Chain33.GetBalance');
    }

    public function getTokenBalance($address, $execer, $tokenSymbol)
    {
        $params = [
            'addresses' => $address,
            'execer' => $execer,
            'tokenSymbol' => $tokenSymbol
        ];
        return $this->send($params, 'token.GetTokenBalance');
    }

    public function createNoBalanceTransaction($txHex, $payAddr, $privkey)
    {
        $params = [
            'txHex' => $txHex,
            'payAddr' => $payAddr,
            'privkey' => $privkey
        ];
        return $this->send($params, 'Chain33.CreateNoBalanceTransaction');
    }

    public function unLock($passwd, $walletorticket, $timeout)
    {
        $params = [
            'passwd' => $passwd,
            'walletorticket' => $walletorticket,
            'timeout' => $timeout
        ];
        return $this->send($params, 'Chain33.UnLock');
    }

    public function structureTrade($amount, $execName, $fee, $isToken = True, $to, $tokenSymbol = '')
    {
        $params = [
            "amount" => $amount,
            "execName" => $execName,
            "fee" => $fee,
            "isToken" => $isToken ? true : false,
            "to" => $to,
            "tokenSymbol" => strtoupper($tokenSymbol)
        ];
        return $this->send($params, 'Chain33.CreateRawTransaction');
    }

    public function signTrade($addr, $expire = '2h', $txHex)
    {
        $params = [
            "addr" => $addr,
            "expire" => $expire,
            "txHex" => $txHex
        ];
        return $this->send($params, 'Chain33.SignRawTx');
    }

    public function createRawTokenPreCreateTx($params)
    {
        return $this->send($params, 'token.CreateRawTokenPreCreateTx');
    }

    public function createRawTokenFinishTx($symbol, $owner)
    {
        $params = [
            'symbol' => $symbol,
            'owner' => $owner
        ];
        return $this->send($params, 'token.CreateRawTokenFinishTx');
    }

    public function createRawTokenRevokeTx($symbol, $owner)
    {
        $params = [
            'symbol' => $symbol,
            'owner' => $owner
        ];
        return $this->send($params, 'token.CreateRawTokenRevokeTx');
    }

    public function getBlock2MainInfo($start, $end)
    {
        $params = [
            "Start" => $start,
            "End" => $end,
        ];
        return $this->send($params, 'paracross.GetBlock2MainInfo');
    }

    public function sendTrade($data)
    {
        $params = [
            "data" => $data,
        ];
        return $this->send($params, 'Chain33.SendTransaction');
    }

    public function getHeight($consensus_platform = 'game')
    {
        $params = [
            "data" => 'user.p.' . $consensus_platform . '.',
        ];
        return $this->send($params, 'paracross.GetHeight');
    }

    /**
     * 获取最新的区块
     */
    public function getLastHeader()
    {
        return $this->send([], 'Chain33.GetLastHeader');
    }

    /**
     * 获取区块hash
     *
     * @param integer $height
     * @return array
     */
    public function getBlockHashByHeight($height)
    {
        return $this->send(['height' => $height], 'Chain33.GetBlockHash');
    }

    public function getProperFee($params = [])
    {
        return $this->send($params, 'Chain33.GetProperFee');
    }

    public function getTxByAddr($addr, $flag, $count, $direction, $height, $index)
    {
        $params = [
            'addr' => $addr,
            'flag' => $flag,
            'count' => $count,
            'direction' => $direction,
            'height' => $height,
            'index' => $index
        ];
        return $this->send($params, 'Chain33.GetTxByAddr');
    }

    /**
     * 查询所有预创建的token GetTokens
     * @param string $execer
     * @param string $funcName
     * @return array
     */
    public function getTokens($execer, $funcName)
    {
        $params = [
            'execer' => $execer,
            'funcName' => $funcName,
            'payload' => [
                'status' => 1,
                'queryAll' => true,
                //'symbolOnly' => true
            ]
        ];
        return $this->send($params, 'Chain33.Query');
    }

    public function chainQuery($execer, $funcName, $param)
    {
        $params = [
            'execer' => $execer,
            'funcName' => $funcName,
            'payload' => $param
        ];
        return $this->send($params, 'Chain33.Query');
    }

    /**
     * @param array $hashes
     * @return array
     */
    public function getTxByHashes(array $hashes)
    {
        $params = [
            'hashes' => $hashes,
        ];
        return $this->send($params, 'Chain33.GetTxByHashes');
    }

    /**
     * @param $password
     * @return array|mixed
     * 解锁钱包
     */
    public function unLockWallet($password)
    {
        $params = [
            'passwd' => $password,
            'walletorticket' => false,
            'timeout' => 30
        ];
        return $this->send($params, 'Chain33.UnLock');
    }

    /**
     * @param ParaName
     * @param ParaSerAddr
     * @return array|mixed
     * 解锁钱包
     */
    public function addPara($paraName, $paraSerAddr)
    {
        $params = [
            'ParaName' => $paraName,
            'ParaSerAddr' => $paraSerAddr,
        ];
        $timeout = [
            'connect_timeout' => 50000,
            'timeout_ms' => 50000
        ];
        return $this->send($params, 'CoinsControl.AddParaCoinsInfo', $timeout);
    }
}
