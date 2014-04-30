<?php

/**
 * 验证字符串长度
 * @author bear
 * @version 1.0.0
 * @copyright xiqiyanyan.com
 * @created 2011-12-21 10:05
 */
class Common_Validate_StringLength implements Zend_Validate_Interface
{
	private $_max;
	private $_min;
	
	public function __construct($max, $min = 0) {
		$this->_max = $max;
		$this->_min = $min;
	}
	
	/** (non-PHPdoc)
	 * 验证字符串的长度，一个中文字算一个字，两个英文字母、数字、或其组合算一个字
	 * @see Zend_Validate_Interface::isValid()
	 * @return boolean true:验证通过; false:验证不通过
	 */
	public function isValid($value) {
		$strLength = mb_strlen($value, 'utf-8');
		$length = 0;
		for ($i=0; $i<$strLength; $i++) {
			$char = mb_substr($value, $i, 1, 'utf-8');
			if (strlen($char)>2) {
				$length++;
			} else {
				$length += 0.5;
			}
		}
		return $length >= $this->_min && $length <= $this->_max;
	}
	
	public function getErrorMessage() {
		return 'String length must be between ' . $this->_min . ' to ' . $this->_max;
	}
	
	public function getMessages() {
		return array('String length must be between ' . $this->_min . ' to ' . $this->_max);
	}

}
