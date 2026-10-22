<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 下午4:33
 */

namespace common\service\brower;


use common\helpers\Curl;

class DcrBrower extends Brower
{
    private $base_uri = 'https://mainnet.decred.org/api/tx/';

    public function getStatus(string $txhash)
    {
        $uri     = $this->base_uri . $txhash;
        $content = (new Curl())->get($uri, true);
        $result  = json_decode($content, true);
        if ($result) {
            if (isset($result['confirmations']) && $result['confirmations'] > 0) {
                return $this->StatusReturn(self::TRANS_OK, '', ['height' => $result['blockheight'], 'blocktime' => $result['blocktime']]);
            } else {
                return $this->StatusReturn(self::TRANS_PADDING);
            }
        }
        return $this->StatusReturn(self::ERROR, $content);
    }

}