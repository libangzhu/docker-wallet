<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 下午3:00
 */

namespace common\service\brower;

use common\helpers\Curl;

class BtyBrower extends Brower
{
    private $base_uri = 'https://mainnet.bityuan.com/api';

    public function getStatus(string $txhash)
    {
        $ch = new Curl();
        $ch->setHeader('Content-Type', 'application/json');
        $post = [
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'Chain33.QueryTransaction',
            'params'  => [['hash' => $txhash]]
        ];
        $post = json_encode($post);
        $ch->setOption(CURLOPT_POSTFIELDS, $post);
        $result = $ch->post($this->base_uri, false);// from, height
        if ($result) {
            if ($result['result']) {
                return $this->StatusReturn(self::TRANS_OK, '', ['height' => $result['result']['height']]);
            } elseif ($result['error']) {
                return $this->StatusReturn(self::ERROR, $result['error']);
            }
        }
        return $this->StatusReturn(self::ERROR);
    }
}