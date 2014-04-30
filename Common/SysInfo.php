<?php

/**
 * @desc 获取系统配置信息类;如操作系统等
 * @author bear
 * @version 1.0.0 2012-08-25 10:51
 * @copyright xiqiyanyan.com
 * @created 2012-08-25 10:51
 */
class Common_SysInfo {
    const MYSQL_USER = 'root';
    const MYSQL_PASSWORD = '123456';
    
	private $_gd;
    private $_serverEnv;
    private $_domainName;
    private $_phpVersion;
    private $_gdInfo;
    private $_freeType;
    private $_mysqlVersion;
    private $_allowUrl;
    private $_fileUpload;
    private $_maxExeTime;
	
	function __construct() {
		$this->_serverEnv = $this->getServerEnv ();
		$this->_domainName = $this->getDomainName ();
		$this->_phpVersion = $this->getPhpVersion ();
		$this->_gdInfo = $this->getGdInfo ();
		$this->_freeType = $this->getFreeType ();
		$this->_mysqlVersion = $this->getMysqlVersion ();
		$this->_allowUrl = $this->getAllowUrl ();
		$this->_fileUpload = $this->getFileUpload ();
		$this->_maxExeTime = $this->getMaxExeTime ();
	}
	
	private function getServerEnv() {
		return PHP_OS . ' | ' . $_SERVER ['SERVER_SOFTWARE'];
	}
	
	private function getDomainName() {
		return $_SERVER ['SERVER_NAME'];
	}
	
	private function getPhpVersion() {
		return PHP_VERSION;
	}
	
	private function getGdInfo() {
		if (function_exists ( 'gd_info' )) {
			$this->_gd = gd_info ();
			$gdInfo = $this->_gd ['GD Version'];
		} else {
			$gdInfo = '<span class="red_font">未知</span>';
		}
		return $gdInfo;
	}
	
	private function getFreeType() {
		if ($this->_gd ["FreeType Support"])
			return '支持';
		else
			return '<span class="red_font">不支持</span>';
	}
	
	private function getMysqlVersion() {
		mysql_connect ( 'localhost', self::MYSQL_USER, self::MYSQL_PASSWORD );
		return mysql_get_server_info ();
	}
	
	private function getAllowUrl() {
		if (@ini_get ( 'allow_url_fopen' ))
			return '支持';
		else
			return '<span class="red_font">不支持</span>';
	}
	
	private function getFileUpload() {
		if (@ini_get ( 'file_uploads' )) {
			$umfs = ini_get ( 'upload_max_filesize' );
			$pms = ini_get ( 'post_max_size' );
			return '允许 | 文件:' . $umfs . ' | 表单：' . $pms;
		} else {
			return '<span class="red_font">禁止</span>';
		}
	}
	
	private function getMaxExeTime() {
		return ini_get ( 'max_execution_time' ) . '秒';
	}
	
	/**
	 * 获取系统配置信息
	 * @return array
	 */
	public function getSysInfos() {
		$infos = array (
				"serverEnv" => $this->_serverEnv,
				"domainName" => $this->_domainName,
				"phpVersion" => $this->_phpVersion,
				"gdInfo" => $this->_gdInfo,
				"FreeType" => $this->_freeType,
				"mysqlVersion" => $this->_mysqlVersion,
				"allowUrl" => $this->_allowUrl,
				"fileUpload" => $this->_fileUpload,
				"maxExeTime" => $this->_maxExeTime 
		);
		return $infos;
	}
	
}
