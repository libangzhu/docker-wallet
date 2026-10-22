<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-26
 * Time: 下午7:08
 */

namespace common\service\exchange;

interface ExchangeInterface
{
    /**
     * 保存支持的交易对到redis数据库，使用crontab定时更新
     * @return mixed|void
     */
    public function setSupportedSymbol();

    /**
     * 更新交易对行情保存到redis，使用crontab定时更新
     * @return mixed|void
     */
    public function setQuotation();

}