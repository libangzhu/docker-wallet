<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 上午10:17
 */

namespace common\service\brower;

use common\helpers\Curl;

class BtcBrower extends Brower
{
    private $base_uri = 'https://chain.api.btc.com/v3/tx/';

    public function getStatus(string $txhash)
    {
        $uri     = $this->base_uri . $txhash;
        $content = (new Curl())->get($uri, false);
        if ($content) {
            if ($content['err_no']) {
                return $this->StatusReturn(self::ERROR, $content['err_msg']);
            }
            if ($content['data']['confirmations'] > 0) {
                return $this->StatusReturn(self::TRANS_OK, '', ['height' => $content['data']['block_height'], 'blocktime' => $content['data']['block_time']]);
            } else {
                return $this->StatusReturn(self::TRANS_PADDING);
            }
        }
        return $this->StatusReturn(self::ERROR);
    }
}