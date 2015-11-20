<?php

/**
 * redis缓存类，通过继承来扩展此类
 * @author Bear
 * @copyright http://maimengmei.com
 * @version 1.0.0
 * @created 2015-6-9 18:01
 */
class Common_Redis extends Redis
{
	const MASTER_KEY = 'master_redis';

    /**
     * 实例化redis类
     * @param array $config 链接redis服务器配置，默认读取App/Configs/Config.php中的master_redis。（数组配置，如：array('host' => '127.0.0.1', 'port' => 6379, 'timeout' => 0)）
     * @param string $db 缓存数据库名；是否需要选择缓存数据库，默认不选择
     * @param string $flag 是否需要建立长链接，默认false：短链接, true:长链接
     * @throws Exception
     */
    public function __construct($config = array(), $db = null, $flag = false) {
    	if (empty($config)) {
    	    $config = BConfig::getConfig(self::MASTER_KEY);
    	}
    	if (empty($config)) {
    	    throw new Exception('redis服务器配置为空');
    	}
    	if ($flag) {
    		$this->pconnect($config['host'], $config['port']);
    	} else {
    	    $this->connect($config['host'], $config['port'], $config['timeout']);
    	}
    	if (isset($config['password'])) {
    	    $this->auth($config['password']);
    	}
    	if (!empty($db)) {
    	    $this->select($db);
    	}
    }
    
    /**
     * 链接redis服务
     * @see Redis::connect()
     * @param string $host 主机ip
     * @param integer $port 端口号
     * @param integer $timeout 链接时长 (可选, 默认为 0 ，不限链接时间) ； 在redis.conf中也有时间，默认为300
     * 
     */
//     public function connect($host, $port, $timeout = 0) {
//         return parent::connect($host, $port, $timeout);
//     }
    
}
