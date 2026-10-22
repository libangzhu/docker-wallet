<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 上午10:19
 */

namespace common\service\brower;

use common\helpers\Curl;

class EthBrower extends Brower
{
    private $base_uri = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash=';

    public function getStatus(string $txhash)
    {
        $uri     = $this->base_uri . $txhash;
        $ch      = new Curl();
        $content = $ch->get($uri, false);
        if ($content && is_array($content)) {
            if (1 == $content['status']) {
                $status = (string)$content['result']['status'];
                if ("0" === $status) {
                    return $this->StatusReturn(self::TRANS_FAIL);
                } elseif ("1" === $status) {
                    return $this->StatusReturn(self::TRANS_OK);
                } else {
                    return $this->StatusReturn(self::TRANS_PADDING);
                }
            }
        }
        return $this->StatusReturn(self::ERROR);
    }
}