<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-13
 * Time: 上午9:56
 */

namespace common\service\brower;

class BrowerFactory
{
    public static $instances;

    /**
     * @param $name
     * @return BrowerInterface
     */
    public static function getInstance($name)
    {
        $name = strtolower($name);
        if (!isset(self::$instances[$name]) || !(self::$instances[$name] instanceof BrowerInterface)) {
            $className = __NAMESPACE__ . '\\' . ucfirst($name) . 'Brower';
            if (class_exists($className) && (new \ReflectionClass($className))->isSubclassOf(BrowerInterface::class)) {
                self::$instances[$name] = new $className();
            } else {
                self::$instances[$name] = new Brower();
            }
        }
        return self::$instances[$name];
    }
}