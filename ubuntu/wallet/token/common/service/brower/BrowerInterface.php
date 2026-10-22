<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 上午9:51
 */

namespace common\service\brower;

/**
 * Interface BrowerInterface
 * 区块链浏览器接口
 *
 * @package common\service\brower
 */
interface BrowerInterface
{
    /**
     * @param string $txhash
     * @return array 交易状态
     */
    public function getStatus(string $txhash);
}