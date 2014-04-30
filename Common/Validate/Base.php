<?php

/**
 * @desc 一些常用的验证函数
 * @author Bear
 * @version 1.0.0
 * @copyright xiqiyanyan.com
 * @created 2012-07-11 21:59
 */
class Common_Validate_Base
{
	/**
	 * 验证密码(6-20个字符，只允许数字、下划线和英文字母)
	 * @param string $value
	 * @return boolean
	 */
	public static function password($value) {
		$pattern = '/^\w{6,20}$/';
		return preg_match($pattern, $value);
	}
	
}
