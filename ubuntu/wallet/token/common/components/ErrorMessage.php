<?php

namespace common\components;

use yii\base\Component;

class ErrorMessage extends Component
{
    /**
     * 预定义错误信息
     */
    public static $errors = [
        'ErrTokenNameLen' => "token名字太长",
        'ErrTokenSymbolLen' => "	token符号太长",
        'ErrTokenTotalOverflow' => '	token 总数过大， 或是负的',
        'ErrTokenSymbolUpper' => '	token symbol 需要全是大写字母',
        'ErrTokenIntroLen' => '	Token介绍太长',
        'ErrTokenExist' => '	Token Symbol 已经存在',
        'ErrTokenNotPrecreated' => '	Token 还没有预创建',
        'ErrTokenCreatedApprover' => 'Token Approver 没有权限',
        'ErrTokenRevoker' => '	Token Revoker 错误 （需要创建者自己撤销）',
        'ErrTokenCanotRevoked' => 'Token 不能撤销',
        'ErrTokenOwner' => 'Token Owner 错误',
        'ErrTokenHavePrecreated' => 'Token已经被预创建',
        'ErrTokenBlacklist' => 'Token 在黑名单里',
        'ErrTokenNotExist' => 'Token 不存在',
        'ErrTokenSymbolExistAlready' => 'Token Symbol 已经存在'
    ];

    /**
     * 获取错误信息
     */
    public static function getMessage($msg)
    {
        $message = isset(self::$errors[$msg]) ? self::$errors[$msg] : '未知错误';
        return $message;
    }
}