<?php

class InitAppHelper
{
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

    public static function isInnerUserIp($user_ip, $company_ip_list)
    {
        if ((in_array($user_ip, $company_ip_list) ||
            !filter_var($user_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) || '127.0.0.1' === $user_ip)) {
            return true;
        }
        return false;
    }

    public static function isOuterUserIp($user_ip, $company_ip_list)
    {
        return !self::isInnerUserIp($user_ip, $company_ip_list);
    }

    public static function isFromCompanyAccessOuterHost()
    {
        $user_ip = self::getUserIp();
        $instanse = new InitAppHelper();
        $company_ip_list = $instanse->getConfig('company_ip_list', 'ip');
        $is_online_host = self::isOnlineHost($_SERVER['SERVER_NAME']);
        if (in_array($user_ip, $company_ip_list) && $is_online_host) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否是真实的外部用户访问   真实外部用户
     */
    public static function isRealOuterAccess()
    {
        $user_ip = self::getUserIp();
        $instanse = new InitAppHelper();
        $company_ip_list = $instanse->getConfig('company_ip_list', 'ip');
        return self::isOuterUserIp($user_ip, $company_ip_list);
    }
}