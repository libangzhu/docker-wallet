<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-24
 * Time: 下午1:53
 */

namespace common\service\zhaobi;

use Yii;
use common\helpers\Curl;

/**
 * Class ZhaobiService
 * 找币接口
 *
 * @package common\service\fzmca
 */
class ZhaobiService
{
    private $ch = null;

    public function __construct()
    {
        $this->ch = new Curl();
    }

    public function validatecode($params)
    {
        $config = Yii::$app->params['service']['ZhaobiService']['validatecode'];
        $url    = self::urlBuild($config['scheme'], $config['host'], $config['port'], $config['path']);
        return $this->send('POST', $url, $params, false);
    }

    /**
     * @param string  $method
     * @param string  $url
     * @param array   $params
     * @param boolean $isRow false:将返回json数据decode
     * @return array
     */
    private function send($method = 'POST', $url = '', $params = [], $isRow = false)
    {
        $result = '';
        if (strtolower($method) == 'post') {
            $this->ch->setPostParams($params);
            $result = $this->ch->post($url, $isRow);
        } else {
            $this->ch->setGetParams($params);
            $result = $this->ch->get($url, $isRow);
        }
        if ($result) {
            return $result;
        }
        return ['code' => -1, 'message' => '请求失败'];
    }

    /**
     * @param string $scheme
     * @param string $host
     * @param string $port
     * @param string $path
     * @return string
     */
    private static function urlBuild($scheme = 'http', $host = '', $port = '', $path = '')
    {
        $base = strtolower($scheme) == 'http' ? 'http' : 'https';
        $base .= '://' . $host;
        $base = ltrim($base, '/');
        return $port ? $base . ':' . $port . $path : $base . $path;
    }
}
