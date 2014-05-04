<?php
/**
 * @desc 用于生成加密密码和验证密码
 * @author Bear
 * @version 1.1.0 2012-01-10 15:21
 * @copyright 2011 xiqiyanyan.com
 * @created 2011-7-14 上午10:32:30
 */
class Common_PwdCrypt implements Common_PwdCrypt_Interface
{
	const DEFAULT_KEY = 'BEAR_XIQIYANYAN_258333309';
	
	/**
	 * 加密字符串，返回可解密的base64编码字符串 
	 * @param string $data
	 * @param string $key
	 * @return string base64 code
	 */
	public function encrypt($data, $key = null) {
		$key = self::DEFAULT_KEY . $key;
		return base64_encode(Common_Mcrypt::getInstance()->encrypt($data, $key));
	}
	
	/**
	 * 解密已加密的字符串，解密字符串必须是base64编码
	 * @param string $encryptData base64 code
	 * @param string $key
	 * @return string
	 */
	public function decrypt($encryptData, $key = null) {
		$key = self::DEFAULT_KEY . $key;
		return Common_Mcrypt::getInstance()->decrypt(base64_decode($encryptData), $key);
	}

	/**
	 * 加密密码，对密码进行不可逆加密后，再用可解密方法加密，并返回base64编码字符串
	 * @param string $pwd 需要加密的密码
	 * @param string $key 
	 * @return string base64 code
	 */
	public function encryptPassword($pwd, $key = null) {
		return $this->encrypt($this->irreversibleEncrypt($pwd), $key);
	}

	/**
	 * 验证密码是否有效
	 * @param string $pwd 需要验证的密码
	 * @param string $encryptPwd 数据库保存的加密后的密码
	 * @param string $key
	 * @return boolean
	 */
	public function validatePassword($pwd, $encryptPwd, $key = null) {
		return $this->irreversibleEncrypt($pwd) === $this->decrypt($encryptPwd, $key);
	}

	/**
	 * 不可逆加密 
	 * @param string $data
	 * @return string
	 */
	protected function irreversibleEncrypt($data) {
		return $this->md5Encryption($data);
	}

	/**
	 * md5 加密字符串   
	 * // crypt 这个也是单向加密的
	 * @param string $string 需要加密的字符串
	 * @return string
	 */
	public function md5Encryption($string, $number = 10) {
		$encryptionString = $string;
    	for ($i=0; $i<$number; $i++) {
    		$encryptionString = md5($encryptionString);
    	}
    	$encryptionString = md5(md5(substr($encryptionString, 0, 8)) . md5(substr($encryptionString, 8, 16)) . md5(substr($encryptionString, -20)));
    	$encryptionString = sha1($encryptionString);
    	$encryptionString = md5($encryptionString);
    	return sha1($encryptionString);
	}
	
}
