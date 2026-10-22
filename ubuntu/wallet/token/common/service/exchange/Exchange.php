<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-26
 * Time: 下午7:22
 */

namespace common\service\exchange;

use common\helpers\Curl;
use Yii;

/**
 * Class Exchange
 * 交易所抽象类
 * @package common\service\exchange
 */
abstract class Exchange
{
    protected $supported_symbol = '';
    protected $quotation_prefix = '';
    /**
     * @var $redis \yii\redis\Connection
     */
    protected $redis;

    /**
     * @var $ch \common\helpers\Curl
     */
    protected $ch;

    public function __construct()
    {
        $this->redis = Yii::$app->redis;
        $this->ch    = new Curl();
        if (Yii::$app->params['http_proxy']['use']) {
            $this->ch->setOptions([
                CURLOPT_PROXY     => Yii::$app->params['http_proxy']['host'],
                CURLOPT_PROXYPORT => Yii::$app->params['http_proxy']['port']
            ]);
        }
    }

    /**
     * 转化交易对为请求变量
     *
     * @param string $tag
     * @param string $aim
     * @return mixed
     */
    abstract public function formatSymbol($tag = 'BTC', $aim = 'USDT');

    /**
     * 获取支持查询行情的交易对
     *
     * @param string $tag
     * @param string $aim
     * @return bool
     */
    public function symbolExists($tag = 'BTC', $aim = "USDT")
    {
        $supported = $this->redis->smembers($this->supported_symbol);
        if (is_array($supported) && in_array($this->formatSymbol($tag, $aim), $supported)) {
            return true;
        }
        return false;
    }

    /**
     * 获取交易对聚合行情信息
     *
     * @param string $tag
     * @param string $aim
     * @return mixed
     */
    public function getTicker($tag = 'BTC', $aim = "USDT")
    {
        $symbol = $this->formatSymbol($tag, $aim);
        $keys   = $this->redis->hkeys($this->quotation_prefix . $symbol);
        $values = $this->redis->hvals($this->quotation_prefix . $symbol);
        return array_combine($keys, $values);
    }
}