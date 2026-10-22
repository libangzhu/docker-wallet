<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-19
 * Time: 下午2:12
 */

namespace common\service\coin;

interface CoinInterface
{
    //币种实例化接口
    public function __construct($id, $sid);

    //币种初始化
    public function init();

    //币种销毁
    public function __destruct();

    public function exchange();

    public function exchange_count();

    public function quotation();
}