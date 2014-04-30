<?php
/**
 * 加密、验证密码
 * @author bear
 * @version 1.0.0 2011-7-15
 * @copyright xiqiyanyan.com
 */
interface Common_PwdCrypt_Interface
{
	/**
	 * 加密密码，返回加密后的base64编码字符串
	 * 同一个密码每次返回的字符串基本上都会不同 
	 * @param string $pwd 需要加密的密码
	 * @param string $key 在验证密码时必须提供相同的key
	 * @return string base64 code
	 */
	public function encryptPassword($pwd, $key = null);
	
	/**
	 * 验证密码
	 * @param string $pwd 需要验证的密码
	 * @param string $encryptPwd 已经加密的密码
	 * @param string $key
	 * @return boolean
	 */
	public function validatePassword($pwd, $encryptPwd, $key = null);
	
}
