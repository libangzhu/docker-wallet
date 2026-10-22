<?php
namespace common\base;

use Yii;
use yii\base\UserException;

/**
 * Exception Model
 * 异常 模型
 * ---------------
 * @version 1.0.2
 * @author Verdient。
 */
class Exception extends UserException
{
	/**
	 * @default const ERROR_NAME
	 * 默认错误名称
	 * -------------------------
	 * @author Verdient。
	 */
	const ERROR_NAME = 'error';

	/**
	 * @default const ERROR_CODE
	 * 默认错误代码
	 * -------------------------
	 * @author Verdient。
	 */
	const ERROR_CODE = 500;

	/**
	 * __construct([Integer / String / Array $code = null, String / Integer $message = null, Throwable $previous = null])
	 * 构造函数
	 * ------------------------------------------------------------------------------------------------------------------
	 * @param Integer / String / Array $code 错误代码
	 * @param String / Integer $message 错误描述
	 * @param Throwable $previous 先前的Exception
	 * ------------------------------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public function __construct($code = null, $message = null, $previous = null){
		if(is_array($code)){
			list($code, $message) = [$code[0], $code[1]];
		}
		if(is_numeric($message) && is_string($code)){
			list($code, $message) = [$message, $code];
		}
		$code = is_numeric($code) ? $code : self::ERROR_CODE;
		$message = $message ?: Yii::t('errorCode', $code);
		parent::__construct($message, $code, $previous);
	}

	/**
	 * getName()
	 * 获取异常信息
	 * ------------
	 * @inheritdoc
	 * -----------
	 * @return String
	 * @author Verdient。
	 */
	public function getName(){
		return self::ERROR_NAME;
	}
}
