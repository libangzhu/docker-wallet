<?php

namespace common\components;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

class Tools
{
    /**
     * 获取随机验证码
     * @return string
     */
    public static function getRandomNumber($count = 6, $type = 'mixed')
    {
        $chars = '1234567890abcdefghijklmnopqrstuvwxyz';

        switch ($type) {
            case 'number':
                $startIndex = 0;
                $endIndex = 9;
                break;
            case 'letter':
                $startIndex = 10;
                $endIndex = 35;
                break;
            default :
                $startIndex = 0;
                $endIndex = 35;
                break;
        }

        $randomNumber = '';
        for ($i = 0; $i < $count; $i++) {
            $randomNumber .= substr($chars, rand($startIndex, $endIndex), 1);
        }
        return $randomNumber;
    }


    /**
     * 获取文件全路径
     * @param type $filename
     * @param type $type
     * @return string
     */
    public static function getFileUrl($filename, $type)
    {
        return $filename;
        switch ($type) {
            case 'case':
                return $filename ? Url::to('@resDomain/' . $type . '/' . $filename) : '';
            case 'video':
                return $filename ? Url::to('@resDomain/' . $type . '/' . $filename) : '';
            case 'banner':
                return $filename ? Url::to('@resDomain/' . $type . '/' . $filename) : '';
            case 'weixin':
                return $filename ? Url::to('@resDomain/' . $type . '/' . $filename) : '';
            case 'public':
                return $filename ? Url::to('@resDomain/' . $type . '/' . $filename) : '';
            default:
                return '';
        }
    }

    public static function getGradeFile($grade)
    {
        return Url::to('@resDomain/grade/a' . $grade . '.png');
    }


    /**
     * 判断当前日期是否是可用日期
     * @param type $startData
     * @param type $endData
     */
    public static function isAvailableDate($start, $end)
    {
        $current = date('Y-m-d');
        $start = $start ? date('Y-m-d', strtotime($start)) : $current;
        $end = $end ? date('Y-m-d', strtotime($end)) : $current;

        if ($start <= $current && $end >= $current) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 添加get查询数据
     * @param type $values
     */
    public static function addQueryParams($values)
    {
        Yii::$app->request->setQueryParams(ArrayHelper::merge(Yii::$app->request->get(), $values));
    }


    /**
     * 获取post数据, 可附加额外数据
     * @param array $values 附加数据,必须是数组形式
     * @param string $key 指定数据附加到特定键
     */
    public static function getPost(array $values, $key = null)
    {
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($key !== null) {
                $data[$key] = ArrayHelper::merge($data[$key], $values);
            } else {
                $data = ArrayHelper::merge($data, $values);
            }
            return $data;
        } else {
            return;
        }
    }

    /*
 * 截取字符串
 */
    public static function truncate_utf8_string($string, $length, $etc = '...')
    {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
            if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
                if ($length < 1.0) {
                    break;
                }
                $result .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            } else {
                $result .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($i < $strlen) {
            $result .= $etc;
        }
        return $result;
    }

    public static function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    public static function assoc_unique($arr, $key)
    {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        $arr = array_values($arr); //sort函数对数组进行排序
        return $arr;
    }

    public static function super_unique($array, $recursion = false)
    {
        // 序列化数组元素,去除重复
        $result = array_map('unserialize', array_unique(array_map('serialize', $array)));
        // 递归调用
        if ($recursion) {
            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = super_unique($value);
                }
            }
        }
        return $result;
    }

    public static function getDatesFromRange($start, $end, $format = 'Y-m-d')
    {
        // Declare an empty array
        $array = array();

        // Variable that store the date interval
        // of period 1 day
        $interval = new \DateInterval('P1D');

        $realEnd = new \DateTime($end);
        $realEnd->add($interval);

        $period = new \DatePeriod(new \DateTime($start), $interval, $realEnd);

        // Use loop to store date into array
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        // Return the array elements
        return $array;
    }

    /**
     * 不同环境下获取真实的IP
     */
    public static function getUserIp()
    {
        // 判断服务器是否允许$_SERVER
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            // 不允许就使用getenv获取
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }
}