<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-8-7
 * Time: 上午11:30
 */

namespace common\service\exchange;

use linslin\yii2\curl\Curl;
use voku\helper\HtmlDomParser;

class Boc extends Exchange implements ExchangeInterface
{
    protected $supported_symbol = 'supported_symbol_boc';
    protected $quotation_prefix = 'quotation_boc_';
    protected $base_url = 'https://srh.bankofchina.com/search/whpj/search_cn.jsp';

    public function symbolExists($tag = 'CNY', $aim = "JPY")
    {
        $supported = $this->redis->smembers($this->supported_symbol);
        if (is_array($supported) && in_array($this->formatSymbol($tag, $aim), $supported)) {
            return true;
        }
        return false;
    }

    /**
     * 转化交易对为请求变量
     *
     * @param string $tag
     * @param string $aim
     * @return mixed
     */
    public function formatSymbol($tag = 'CNY', $aim = 'JPY')
    {
        return strtoupper($tag . '_' . $aim);
    }

    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setSupportedSymbol()
    {
        $this->redis->sadd($this->supported_symbol, 'CNYJPY');
    }

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     *
     * @return mixed|void
     */
    public function setQuotation()
    {
        $curl = new Curl();

        $response = $curl->setPostParams([
            'erectDate' => '',
            'nothing' => '',
            'pjname' => '日元'
        ])->post('https://srh.bankofchina.com/search/whpj/search_cn.jsp');

        $response = iconv('UTF-8', 'GBK//TRANSLIT', $response);
        $html = HtmlDomParser::str_get_html($response);
        $div = ($html->find('div.BOC_main'));
        if ($div->find('td')) {
            foreach ($div->find('td') as $key => $e) {
                if ($key == 5) {
                    $key = $this->quotation_prefix . 'CNY_JPY';
                    $this->redis->hmset($key, 'low', $e->innertext, 'high', $e->innertext, 'last', $e->innertext);
                    $this->redis->sadd($this->supported_symbol, 'CNYJPY');
                    break;
                }
            }
        }
    }

}