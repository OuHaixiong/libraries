<?php

/**
 * 读取配置文件信息
 * @author Bear
 * @version 1.0.0
 * @copyright xiqiyanyan.com
 * @created 2012-05-11 22:12
 */
class Config
{
	private static $_constant;
	private static $_config;
	
	/**
	 * 通过键名读取站点常量
	 * @param string $key
	 * @return array | string | mixed
	 */
	public static function getConstant($key) {
		if (self::$_constant === null) {
			self::$_constant = require_once APPLICATION_PATH . '/configs/constant.php';
		}
		return self::$_constant[$key];
	}
	
	/**
	 * 通过键名读取站点配置文件
	 * @param string $key
	 * @return array | string | mixed
	 */
	public static function getConfig($key) {
		if (self::$_config === null) {
			self::$_config = require_once APPLICATION_PATH . '/configs/config.php'; //  __DIR__ . '/config/config.php' __DIR__ 获取本页脚本执行的绝对路径
		}
		return self::$_config[$key];
	}
	
	
	
}
