<?php

/**
 * （FirePHP）在火狐控制台打印php信息，此类需要php不结束脚本运行才能打印（即不能用exit等退出脚本执行）
 * @author bear
 * @version 1.0.0
 * @copyright xiqiyanyan.com
 * @created 2012-12-15
 */
class Common_FirePHP
{
	protected static $instance;
	
	protected function __construct() {
	}
	
	/**
	 *
	 * @return Zend_Log
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new Zend_Log ( new Zend_Log_Writer_Firebug () );
		}
		return self::$instance;
	}
	
	/**
	 * 打印log
	 * @param mixed $message        	
	 * @param string $priority        	
	 * @return void
	 */
	public static function log($message, $priority = Zend_Log::INFO) {
		self::getInstance ()->log ( $message, $priority );
	}
}
