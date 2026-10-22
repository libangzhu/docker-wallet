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
class ChainService
{
    public static function urlBuild()
    {
        $config = Yii::$app->params['chain'];
        $scheme = $config['scheme'] ?? 'http';
        $host   = $config['host'] ?? '127.0.0.1';
        $port   = (string)$config['port'] ?? '';
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
            'id'      => 0,
            'method'  => $method,
            'params'  => [],
        ];
        if (!empty($params)) {
            $data['params'][] = $params;
        }
        return json_encode($data);
    }

    public function send($params = [], $method = 'Chain33.Query')
    {
        $ch      = new Curl();
        $jsonrpc = self::jsonRpcBuild($params, $method);
        $ch->setHeader('Content-Type', 'application/json');
        $ch->setRawPostData($jsonrpc);
        $result = $ch->post(self::urlBuild(), false);
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
     * 获取地址下的所有token资产
     *
     * @param  string|array $address
     * @param  string       $symbol
     * @return array
     */
    public function getAccountTokenAssets($address, $symbol)
    {
        if (!is_array($address)) {
            $address = [$address];
        }
        $params = [
            "addresses"   => $address,
            "execer"      => "user.p.guodun.token",
            "tokenSymbol" => $symbol
        ];
        return $this->send($params, 'Chain33.GetTokenBalance');
    }

    /**
     * 提币
     *
     * @param        $from
     * @param        $to
     * @param        $amount
     * @param string $note
     * @param bool   $isToken
     * @param string $tokenSymbol
     * @return array
     */
    public function extractToken($from, $to, $amount, $note = '', $isToken = true, $tokenSymbol = '')
    {
        $params = [
            "from"        => $to,
            "to"          => $from,
            "amount"      => -$amount,
            "note"        => $note,
            "isToken"     => $isToken ? true : false,
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
     * @param bool   $isToken
     * @param string $tokenSymbol
     * @return array
     */
    public function transToken($from, $to, $amount, $note = '', $isToken = true, $tokenSymbol = '')
    {
        $params = [
            "from"        => $from,
            "to"          => $to,
            "amount"      => $amount,
            "note"        => $note,
            "isToken"     => $isToken ? true : false,
            "tokenSymbol" => strtoupper($tokenSymbol)
        ];
        return $this->send($params, 'Chain33.SendToAddress');
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

    public function getTxByAddr($addr, $flag, $count, $direction, $height, $index)
    {
        $params = [
            'addr'      => $addr,
            'flag'      => $flag,
            'count'     => $count,
            'direction' => $direction,
            'height'    => $height,
            'index'     => $index
        ];
        return $this->send($params, 'Chain33.GetTxByAddr');
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
}
