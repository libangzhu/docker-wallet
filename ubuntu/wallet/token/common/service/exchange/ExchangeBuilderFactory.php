<?php

namespace common\service\exchange;
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/8/15
 * Time: 10:10
 */

class ExchangeBuilderFactory
{
    private static $cached = [];

    public static function create($type)
    {
        if (empty(static::$cached[$type])) {
            $type = str_replace(' ', '', ucwords($type));
            $class = __NAMESPACE__ . "\\factory\\{$type}Builder";
            static::$cached[$type] = new $class;
        }
        return static::$cached[$type];
    }
}
