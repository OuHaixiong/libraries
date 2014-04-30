<?php

/**
 * 获取客户端信息；如操作系统、浏览器、IP地址	
 * @author bear
 * @version 1.0.0
 * @copyright xiqiyanyan.com
 * @created 2013-1-9 17:58
 */
class Common_ClientInfo
{
    /**
     * 访问者的浏览器信息
     * @var string
     */
    private $_agent;
    
    public function __construct() {
    	$this->_agent = $_SERVER['HTTP_USER_AGENT'];
    	// Mozilla/5.0 [en] (X11; Ubuntu; Linux x86_64; rv:18.0) Gecko/20100101 Firefox/18.0
    }
    
    public function getAgent() {
    	return $this->_agent;
    }
    
    /**
     * 获取浏览器信息 (如遇到问题可参考：http://php.net/manual/zh/function.get-browser.php)
     * @return array 如： array("firefox"=>"18.0") 
     */
    public function getBrowse() {
        // Declare known browsers to look for
        $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror', 'gecko', 'google chrome', 'chrome');
        
        // Clean up agent and build regex that matches phrases for known browsers
        // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
        // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
        $agent = strtolower($this->_agent);
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
        
        // Find all phrases (or return empty array if none found)
        if (!preg_match_all($pattern, $agent, $matches)) return array();
        
        // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
        // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
        // in the UA).  That's usually the most correct.
        $i = count($matches['browser'])-1;
        return array($matches['browser'][$i] => $matches['version'][$i]);
    }
    
    /**
     * 获取IP地址 
     * @return string
     */
    public function getIP() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * 获取操作系统
     * @return string
     */
    public function getOS() {
        if (stripos($this->_agent, 'win')!==false && stripos($this->_agent, 'nt 5.1')!==false) {
            $os = 'Windows XP';
        } else if (stripos($this->_agent, 'win')!==false && stripos($this->_agent, 'nt 5')!==false) {
            $os = 'Windows 2000';
        } else if (stripos($this->_agent, 'win')!==false && stripos($this->_agent, 'nt')!==false) {
            $os = 'Windows NT';
        } else if (stripos($this->_agent, 'win')!==false && ereg($this->_agent, '32')!==false) {
            $os = 'Windows 32';
        } else if (stripos($this->_agent, 'linux')!==false) {
            $os = 'Linux';
        } else if (stripos($this->_agent, 'unix')!==false) {
            $os = 'Unix';
        } else if (stripos($this->_agent, 'sun')!==false && stripos($this->_agent, 'os')!==false) {
            $os = 'SunOS';
        } else if (stripos($this->_agent, 'ibm')!==false && stripos($this->_agent, 'os')!==false) {
            $os = 'IBM OS/2';
        } else if (stripos($this->_agent, 'Mac')!==false && stripos($this->_agent, 'PC')!==false) {
            $os = 'Macintosh';
        } else if (stripos($this->_agent, 'PowerPC')!==false) {
            $os = 'PowerPC';
        } else if (stripos($this->_agent, 'AIX')!==false) {
            $os = 'AIX';
        } else if (stripos($this->_agent, 'HPUX')!==false) {
            $os = 'HPUX';
        } else if (stripos($this->_agent, 'NetBSD')!==false) {
            $os = 'NetBSD';
        } else if (stripos($this->_agent, 'BSD')!==false) {
            $os = 'BSD';
        } else if (stripos($this->_agent, 'OSF1')!==false) {
            $os = 'OSF1';
        } else if (stripos($this->_agent, 'IRIX')!==false) {
            $os = 'IRIX';
        } else if (stripos($this->_agent, 'FreeBSD')!==false) {
            $os = 'FreeBSD';
        } else if (stripos($this->_agent, 'teleport')!==false) {
            $os = 'teleport';
        } else if (stripos($this->_agent, 'flashget')!==false) {
            $os = 'flashget';
        } else if (stripos($this->_agent, 'webzip')!==false) {
            $os = 'webzip';
        } else if (stripos($this->_agent, 'offline')!==false) {
            $os = 'offline';
        } else {
            $os = 'Unknown';
        }
        return $os;
    }

}
