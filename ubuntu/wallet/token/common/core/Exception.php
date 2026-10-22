<?php

namespace common\core;
/**
 * Created by PhpStorm.
 * Author: libingke
 * Date: 2017/11/1
 * Time: 10:03
 */
class Exception extends \Exception
{
	public static function fxeeException($result)
	{
		if (is_array($result) && isset($result['code'])) {

			if ($result['code'] != 200) {
				throw new Exception($result['message'], $result['code']);
			}

		} else {

			throw new Exception('fxee接口服务器错误');
		}
	}
}