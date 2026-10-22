<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-26
 * Time: 下午7:19
 */

namespace common\service\exchange;

use Prophecy\Exception\Doubler\ClassNotFoundException;

/**
 * Class ExchangeFactory
 * 交易所工厂
 * @package common\service\exchange
 */
class ExchangeFactory
{
    public static $instances = [];

    /**
     * 创建交易所，默认为火币
     * @param string $name
     * @return object
     * @throws ClassNotFoundException
     */
    public static function createExchange($name = 'HuoBi')
    {
        $className = __NAMESPACE__ . '\\' . $name;
        if (class_exists($className)) {
            if (array_key_exists($name, self::$instances)) {
                if (self::$instances[$name] instanceof $className) {
                    return self::$instances[$name];
                }
            }
            self::$instances[$name] = new $className();
            return self::$instances[$name];
        }
        throw new ClassNotFoundException('class ' . $className . ' not found!', $className);
    }
}