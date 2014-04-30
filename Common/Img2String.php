<?php

/**
 * @desc 图片转为字符传
 * @author Bear
 * @version 1.0.0
 * @created 2012-09-04 10:47
 */
class Common_Img2String
{
	private $_error;
	
    /**
     * 获取错误信息
     * @return string
     */
    public function getError() {
        return $this->_error;
    }
    
    /**
     * 图片文件转为二进制字符串
     * @param string $path 图片文件绝对路径
     * @return boolean | string 成功返回字符串，失败返回false，有错误提示
     */
    public function toString($path) {
        if (!is_readable($path)) {
        	$this->setError('图片文件不可读！');
        	return false;
        }
        return file_get_contents($path);
    }
    
    /**
     * 设置错误信息
     * @param string $error
     */
    private function setError($error) {
    	$this->_error = $error;
    }

}
