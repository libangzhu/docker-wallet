<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 上午10:21
 */

namespace common\business;

use common\service\brower\BrowerFactory;

class BrowerBusiness
{
    /**
     * 获取交易信息
     *
     * @param string $name 区块链浏览器
     * @param string $txhash 交易hash
     * @return mixed|string|boolean
     */
    public static function getTransStatus(string $name, string $txhash)
    {
        return $status = BrowerFactory::getInstance($name)->getStatus($txhash);
    }
}