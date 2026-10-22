<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-24
 * Time: 下午2:13
 */

namespace common\business;

use common\service\zhaobi\ZhaobiService;


class ZhaobiBusiness
{
    /**
     * @param integer $uid
     * @param string  $codetype
     * @param integer $code
     * @param string  $type
     * @return array
     */
    public static function validateCode($uid, $codetype, $code, $type)
    {
        $service = new ZhaobiService();
        $params  = [
            'uid'      => $uid,
            'codetype' => $codetype,
            'code'     => $code,
            'type'     => $type
        ];
        return $service->validatecode($params);
    }
}