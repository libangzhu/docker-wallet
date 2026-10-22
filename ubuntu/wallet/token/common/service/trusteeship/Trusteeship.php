<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2019/1/10
 * Time: 10:53
 */
namespace common\service\trusteeship;

use Yii;
use common\helpers\Curl;

/**
 * Lottery平行链 区块链接口
 */
class Trusteeship
{
    /**
     * @return string
     * 平行链上操作彩票
     */
    public static function urlTrusteeshipBuild()
    {
        $config = Yii::$app->params['trusteeship'];
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

    /**
     * @return array|mixed
     * 获取支持的币种
     */
    public function getSupportCoin()
    {
        $ch = new Curl();
        $config = Yii::$app->params['trusteeship'];
        $url = self::urlTrusteeshipBuild().$config['coin_getsupport'];
        $result = $ch->get($url, false);
        if (!$result) {
            return ['code' => -1, 'msg' => $ch->errorText];
        }
        if (200 == $result['code']) {
            $result['code'] = 0;
            $result['msg'] = $result['message'];
            unset($result['message']);
            return $result;
        } else {
            return ['code' => -1, 'msg' => $result['message']];
        }
    }
}