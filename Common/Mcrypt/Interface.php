<?php
/**
 * 加密、解密字符串
 * @author bear
 * @version 1.0.0 2011-7-15
 * @copyright http://maimengmei.com
 */
interface Common_Mcrypt_Interface
{
	/**
	 * Encrypts data.
	 * @param string $data data to be encrypted.
	 * @param string $key the decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
	 * @return string the encrypted data
	 * @throws Gp_Model_Auth_Mcrypt_Exception if PHP Mcrypt extension is not loaded
	 */
	public function encrypt($data, $key = null);
	
	/**
	 * Decrypts data
	 * @param string $data data to be decrypted.
	 * @param string $key the decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
	 * @return string the decrypted data
	 * @throws Gp_Model_Auth_Mcrypt_Exception if PHP Mcrypt extension is not loaded
	 */
	public function decrypt($data, $key = null);
	
}
