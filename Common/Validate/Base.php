<?php

/**
 * @desc 一些常用的验证函数；这里有验证用户名、电子邮件、QQ、中文、字母和数字组合等(全是静态方法)
 * @author Bear
 * @version 1.3.0 2014-6-18 13:42
 * @copyright http://maimengmei.com
 * @created 2012-07-11 21:59
 */
class Common_Validate_Base
{

	/**
	 * 错误信息
	 * @var string
	 */
	private static $_error;
	
	/**
	 * 获取错误信息
	 * @return string
	 */
	public static function getError() {
	    return self::$_error;
	}
	
	/**
	 * 设置错误信息
	 * @param string $error
	 */
	public static function setError($error) {
	    self::$_error = $error;
	}
	 
	/**
	 * 验证用户名，只能是字母、数字、下划线字符的组合，以字母开头，最少 6 个字符，最长 20 个字符
	 * @param string $subject
	 * @return boolean true:验证通过; false:验证不通过
	 */
	public static function isUsername($subject) {
	    $pattern = '/^[a-zA-Z]\w{5,19}$/';
	    return preg_match($pattern, $subject) ? true : false;
	}
	
	/**
	 * 验证密码(6-20个字符，只允许数字、下划线和英文字母)
	 * @param string $value
	 * @return boolean
	 */
	public static function isPassword($value) {
	    $pattern = '/^\w{6,20}$/';
	    return preg_match($pattern, $value);
	}
	
	/**
	 * 验证电子邮件
	 * @param string $value
	 * @return string | false 如果成功返回该email，如果失败返回false
	 */
	public static function isEmail($value) {
	    return filter_var($value, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * 验证两个值是否相同
	 * @param mixed $value1
	 * @param mixed $value2
	 * @param boolean $flag 默认false: 不检测类型; true: 检测类型（绝对相等）
	 * @return boolean
	 */
	public static function isSame($value1, $value2, $flag = false) {
	    return $flag? $value1 === $value2 : $value1 == $value2;
	}
	
	/**
	 * 验证QQ号
	 * @param string $subject
	 * @return boolean
	 */
	public static function isQQ($subject) {
	    $pattern = '/^[1-9]\d{4,10}$/';
	    return preg_match($pattern, $subject) ? true : false;
	}
	
	/**
	 * 验证中国手机号
	 * @param string $string
	 * @return boolean
	 */
	public static function isMobile($string) {
	    $regExp = '/^(?:13|15|18)[0-9]{9}$/';
	    return preg_match($regExp, $string) ? true : false;
	}
	
	/**
	 * 验证中国家庭电话号码
	 * @param string $string
	 * @return boolean
	 */
	public static function isTel($string) {
	    $regExp = '/^\d{3,4}\-?\d{7,8}$/';
	    return preg_match($regExp, $string) ? true : false;
	}
	
	/**
	 * 验证中文
	 * @param string $string
	 * @return boolean
	 */
	public static function isChinese($string, $encoding = 'utf8') {
	    $regExp = $encoding == 'utf8' ? '/^[\x{4e00}-\x{9fa5}]+$/u' :  '/^([\x80-\xFF][\x80-\xFF])+$/';
	    return preg_match($regExp, $string) ? true : false;
	}
	
	/**
	 * 验证字母 （ctype_alpha 是php的函数，在哪里都可以运行，也是推荐的用法）
	 * @param string $string
	 * @return boolean
	 */
	public static function isAlpha($string) {
	    return ctype_alpha($string) ? true : false;
	}
	
	/**
	 * 验证字母和数字组合 （ctype_alnum 是php的函数，在哪里都可以运行，也是推荐的用法）
	 * @param string $string
	 * @return boolean
	 */
	public static function isAlnum($string) {
	    return ctype_alnum($string) ? true : false;
	}
	
	/**
	 * 验证数字字符串（只有整型数字字符串才能通过， $a = 88; 都返回false）
	 * @param string $string
	 * @return boolean
	 */
	public static function isDigit($string) {
	    return ctype_digit($string);
	}
	
	/**
	 * 验证是否是数字或数字字符串
	 * @param string | integer  $string
	 * @return boolean
	 */
	public static function isNumeric($string) {
	    return is_numeric($string);
	}
	
	/**
	 * 验证是否全是小写字母字符串（一定要是字母）
	 * @param string $string
	 * @return boolean
	 */
	public static function isLower($string) {
	    return ctype_lower($string);
	}
	
	/**
	 * 验证是否是控制字符串（\n\r\t）
	 * @param string $string
	 * @return boolean
	 */
	public static function isControl($string) {
	    return ctype_cntrl($string);
	}
	
	/**
	 * 简单验证是否为IP地址 ( 如果少于4段也可能正确；如：25.1 )
	 * @param string $ipAddress
	 * @return boolean
	 */
	public static function isIP($ipAddress) {
	    $result = ip2long($ipAddress);
	    return ($result == -1 || $result == false) ? false : true;
	}
	
	/**
	 * 检测参数是否为正确的身份证号码,只支持18位身份证,不做区域/年份/性别判断,只做检验.
	 * @param string $ID
	 * @return boolean
	 */
	public static function isID($ID) {
	    $Weighting = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1);
	    $Verify = array(1,0,'X',9,8,7,6,5,4,3,2);
	    $Sum = 0;
	    $Regexp = '/^[1-6][1-6][0-9]{15}[0-9x]$/i';
	    $error = 'Your ID is not properly formatted.';
	    if(!preg_match($Regexp,$ID)){
	        self::setError($error);
	        return false;
	    }
	    $Last = substr($ID,17,1);
	    for($I=0;$I<17;$I++){
	        $Sum = $Sum+intval(substr($ID,$I,1))*$Weighting[$I];
	    }
	    $Y = $Sum % 11;
	    if(strtoupper($Last) != $Verify[$Y]){
	        self::setError($error);
	        return false;
	    }
	    return true;
	}
	
}
