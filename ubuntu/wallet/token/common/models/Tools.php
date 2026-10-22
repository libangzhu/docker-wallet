<?php

namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class Tools
{
    /**
     * 获取随机验证码
     * @return string
     */
    public static function getRandomNumber($count = 6, $type = 'mixed')
    {
        $chars = '1234567890abcdefghijklmnopqrstuvwxyz';

        switch($type) {
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
        for($i = 0; $i<$count; $i++) {
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
    public static function getFileUrl($fileName)
    {
        return $fileName? Url::to('@resDomain' . $fileName): '';
    }


    /**
     * 判断当前日期是否是可用日期
     * @param type $startData
     * @param type $endData
     */
    public static function isAvailableDate($start, $end)
    {
        $current = date('Y-m-d');
        $start = $start?date('Y-m-d', strtotime($start)):$current;
        $end = $end?date('Y-m-d', strtotime($end)):$current;

        if($start <= $current && $end >= $current) {
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
        Yii::$app->request->setQueryParams(\yii\helpers\ArrayHelper::merge(Yii::$app->request->get(), $values));
    }


    /**
     * 获取post数据, 可附加额外数据
     * @param array $values 附加数据,必须是数组形式
     * @param string $formName 指定数据附加到特定键
     */
    public static function getPost(array $values, $formName = null)
    {
        if(Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if($formName !== null) {
                $data[$formName] = ArrayHelper::merge($data[$formName], $values);
            } else {
                $data = ArrayHelper::merge($data, $values);
            }
            return $data;
        } else {
            return;
        }
    }


    /**
     * 获取到下个给定时间点还有多长时间,单位 秒
     * @param mixed $time 可以是一个时间字符串,也可以是时间字符串数组
     * 格式为 h:m:s
     * @return $duration 返回值为到下个时间点还有多长时间,以秒为单位
     */
    public static function getDuration($time)
    {
        if(!is_array($time)) {
            $time = (array)$time;
        }

        $seconds = [];
        foreach($time as $value) {
            $timeArray = explode(':', $value);
            if(3 != count($timeArray)) {
                return false;
            }
            list($hour, $minute, $second) = $timeArray;
            if((int)$hour < 0 || (int)$hour > 23 || (int)$minute < 0 || (int)$minute > 59 || (int)$second < 0 || (int)$second > 59) {
                return false;
            }
            $seconds[] = $hour * 3600 + $minute * 60 + $second;
        }

        sort($seconds);
        $currentTimeSecond = idate('H') * 3600 + idate('i') * 60 + idate('s');
        foreach($seconds as $second) {
            if(($second - $currentTimeSecond) > 0) {
                return $second - $currentTimeSecond;
            }
        }

        return $seconds[0] + (24 * 3600 - $currentTimeSecond);
    }


    /**
     * 保留小数位数,向下取数
     * @param float $number     //数字
     * @param int $precision    //精度
     * 例如, roundDown(1.20058, 4)   return 1.2005
     */
    public static function roundDown($number, $precision)
    {
        $pow = pow(10, (int)$precision);
        return floor($number*$pow)/$pow;
    }


    /**
     * 获取一条错误信息
     * @param type $errors
     * @return type
     */
    public static function getFirstError($errors,$defaultError = null)
    {
        if(count($errors) > 0){
            foreach($errors as $error) {
                return $error[0];
            }
        }
        $defaultError = '请求数据有误';
        return $defaultError;
    }


    /**
     * 格式化数字
     * @param int $num
     * @return string
     */
    public static function formatNumber($num)
    {
        return ($num/100000000 > 1) ? sprintf('%.2f', $num/100000000).'亿' : (($num/10000 > 1) ? sprintf('%.2f', $num/10000).'万' : sprintf('%.2f', $num));
    }


    /**
     * 获取配置参数
     * @param string $moduleId 模块ID
     * @param mixed $keys
     * @param mixed $default 默认值
     */
    public static function getModuleParams($moduleId, $keys, $default = null)
    {
        if(is_string($keys) && isset(Yii::$app->params[$moduleId][$keys])) {
            return Yii::$app->params[$moduleId][$keys];
        } elseif(is_array($keys) && isset(Yii::$app->params[$moduleId])) {
            $params = Yii::$app->params[$moduleId];
            foreach($keys as $key) {
                if(isset($params[$key])) {
                    $params = $params[$key];
                } else {
                    return $default;
                }
            }
            return $params;
        }

        return $default;
    }


    /**
     * 获取上传文件目录
     * @param string $moduleId 模块ID
     * @param array $moduleSubDir 模块子目录
     */
    public static function getUploadDir($moduleId, $moduleSubdir = null)
    {
        $uploadDir = '/' . $moduleId;
        if(is_array($moduleSubdir)) {
            foreach($moduleSubdir as $dirName) {
                $uploadDir .= '/' . $dirName;
            }
        }
        return $uploadDir;
    }


    /**
     * 复制文件夹及子目录文件
     * @param string $source
     * @param string $destination
     */
    public static function xCopy($source, $destination)
    {
        if(!is_dir($source)) {
            return;
        }

        FileHelper::createDirectory($destination, 0755, true);
        $handle = opendir($source);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $source . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                static::xCopy($path, $destination . DIRECTORY_SEPARATOR . $file);
            } else {
                copy($path, $destination . DIRECTORY_SEPARATOR . $file);
            }
        }
        closedir($handle);
    }


    /**
     * 压缩文件或文件夹
     * @param string $source 文件夹或文件路径
     */
    public static function folderToZip($source, &$zipArchive, $exclusiveLength)
    {
        if (is_dir($source) || is_file($source)) {
            $handle = opendir($source);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = $source . DIRECTORY_SEPARATOR . $file;
                $localPath = substr($path, $exclusiveLength);
                if (is_file($path)) {
                    $zipArchive->addFile($path, $localPath);
                } elseif (is_dir($path)) {
                    $zipArchive->addEmptyDir($localPath);
                    static::folderToZip($path, $zipArchive, $exclusiveLength);
                }
            }
            closedir($handle);
        }
    }


    /**
     * 压缩文件夹
     * @param string $source
     */
    public static function zipDir($source)
    {
        $pathinfo = pathinfo($source);
        $basename = $pathinfo['basename'];
        $dirname = $pathinfo['dirname'];

        $zipArchive = new \ZipArchive();
        $zipArchive->open($dirname . DIRECTORY_SEPARATOR . $basename . '.zip', \ZipArchive::CREATE);
        $exclusiveLength = strlen($dirname) + 1;
        static::folderToZip($source, $zipArchive, $exclusiveLength);
        $zipArchive->close();
    }


    public static function unZip($source)
    {
        $pathinfo = pathinfo($source);
        $dirname = $pathinfo['dirname'];
        $zipArchive = new \ZipArchive();
        $resource = $zipArchive->open($source);
        if ($resource === true) {
            $zipArchive->extractTo($dirname);
            $zipArchive->close();
        }
    }

    /**
     * 生成全球唯一标识uuid
     * @param string $source
     */
    public function uuid(){
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid =  substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }


}