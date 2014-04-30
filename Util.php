<?php

/**
 * 部分公用代码类 (有些没用测试过，测试过的都写了“已测试”)
 * @author Bear
 */
class Util
{
	/**
	 * 获取客户端IP地址 (已测试通过)
	 * @return string
	 */
	public static function getClientIp(){
	   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	       $ip = getenv("HTTP_CLIENT_IP");
	   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	       $ip = getenv("HTTP_X_FORWARDED_FOR");
	   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	       $ip = getenv("REMOTE_ADDR");
	   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	       $ip = $_SERVER['REMOTE_ADDR'];
	   else
	       $ip = "unknown";
	   return($ip);
	}
	
	/**
	 * 通过ip获取对应的省份和城市（如果有$ip则使用新浪地理位置接口，返回全部信息；否则使用腾讯地理位置接口，仅返回province和city信息）
	 * (已测试通过)
	 * @param string $ip 貌似一定要有ip，没有ip无法查
	 * @return array 如：array('ip','country','province','city','district')
	 */
	public static function getAddressByIp($ip=null) {
		if ($ip === null) {
			return self::getAddressByQQ();
		} else {
			return self::getAddressBySina($ip);
		}
	}
	
	/**
	 * 通过腾讯接口查询地理位置信息 (已测试通过)
	 */
	private static function getAddressByQQ() {
		$ip=file_get_contents("http://fw.qq.com/ipaddress");
		$ip=str_replace('"",','',$ip);
		$ip=str_replace('"',' ',$ip);
		$ip2=explode("(",$ip);
		$a=substr($ip2[1],0,-2);
		$b=explode(",",$a);
		return array('ip'=>$b[0],
					'country'=>'中国',
					'province'=>mb_substr($b[1], 0, (mb_strlen($b[1],'utf8')-2), 'utf8'),
					'city'=>mb_substr($b[2], 0, (mb_strlen($b[2])-2), 'utf8'));
	}
	
	/**
	 * 通过新浪接口查询地理位置信息 (已测试通过)
	 * @param string $ip
	 */
	private static function getAddressBySina($ip) {
		// 严重ipV4是否格式是否正确
		$ip2long = ip2long($ip);
		$ipsrc = $ip;
        if($ip2long === false)	return false;

		$ip = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=$ip");
		$ip = explode("=", $ip);
		$ip = json_decode(substr($ip[1], 0, -1));
		
		return array('ip'=>$ipsrc, 
					'country'=>$ip->country, 
					'province'=>$ip->province, 
					'city'=>$ip->city, 
					'district'=>$ip->district);
	}
	


	/**
	 * 生成唯一字符串 (已测试通过)
	 * @return string
	 */
	public static function generateUUID() {
		return md5(uniqid(mt_rand(),true));
	}
	
	/**
	* php缩略图及水印的原理与实现
	* 1. 使用imagecreatetruecolor()函数创建一个指定大小的真彩色图像
	* 2. 使用imagecolorallocate()函数为一幅图像分配颜色
	* 3. 使用imagefill()函数进行区域填充
	* 4. 使用imagecopyresampled()函数采样拷贝部分图像并调整大小
	* 改变图像大小，生成缩略图
	* @param string $srcFileName
	* @param int $dstWidth
	* @param int $dstHeight 为空则等比压缩
	* @param string $dstFileName 不填则默认和$srcFileName相同
	* @return bool 成返回1， 不成功返回false
	*/
	public static function createThumb($srcFileName,$dstWidth,$dstHeight=null,$dstFileName=null){
	
		if ($dstFileName===null) $dstFileName = $srcFileName;
	
		$imageInfo = getimagesize($srcFileName);
		// print_r($imageInfo); /*	Array([0] => 1024 [1] => 768 [2] => 2 [3] => width="1024" height="768" [bits] => 8 [channels] => 3 [mime] => image/jpeg)*/
	
		$srcWidth = $imageInfo[0];
		$srcHeight = $imageInfo[1];
	
		// 如果原图宽度小于目标图宽度，则不处理，直接返回true
		if ($srcWidth <= $dstWidth) return true;
		if ($dstHeight===null) $dstHeight = ($srcHeight*$dstWidth)/$srcWidth;
	
		switch ($imageInfo[2])
		{
			case 1:
				$imageType = "gif";
				$srcFile = imagecreatefromgif($srcFileName);
				break;
			case 2:
				$imageType = "jpeg";
				$srcFile = imagecreatefromjpeg($srcFileName);
				break;
			case 3:
				$imageType = "png";
				$srcFile = imagecreatefrompng($srcFileName);
				break;
			default:
				return false;
			break;
		}
	
		$dstFile = imagecreatetruecolor($dstWidth, $dstHeight);
	
		if ($dstFile) {
			// $bgColor=imagecolorallocate($dstFile,255,255,255);
			// imagefill($ni,0,0,$bgColor);
			imagecopyresampled($dstFile, $srcFile, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		} else {
			$dstFile = imagecreate($dstWidth, $dstHeight);
			$bgColor=imagecolorallocate($dstFile,255,255,255);
			imagefill($dstFile,0,0,$bgColor);
			imagecopyresized($dstFile, $srcFile, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		}
	
		$createfunction = "image".$imageType;
		return $createfunction($dstFile,$dstFileName);
	}
	
	/**
	 * 抓取远程图片
	 * @param string $srcUrl
	 * @param string $dstFileName
	 * @param int $dstWidth
	 * @param int $dstHeight
	 * @return boolean
	 */
	public static function grabImage($srcUrl,$dstFileName,$dstWidth,$dstHeight=null) {
		$imageInfo = getimagesize($srcUrl);
		// print_r($imageInfo); /*	Array([0] => 1024 [1] => 768 [2] => 2 [3] => width="1024" height="768" [bits] => 8 [channels] => 3 [mime] => image/jpeg)*/
		
		$srcWidth = $imageInfo[0];
		$srcHeight = $imageInfo[1];
		
		// 如果原图宽度小于目标图宽度，则不处理，直接返回true
		if ($srcWidth <= $dstWidth) {
			$dstWidth = $srcWidth;
			$dstHeight = $srcHeight;
		} else {
			if ($dstHeight===null) $dstHeight = ($srcHeight*$dstWidth)/$srcWidth;
		}
		
		switch ($imageInfo[2])
		{
			case 1:
				$imageType = "gif";
				$srcFile = imagecreatefromgif($srcUrl);
				break;
			case 2:
				$imageType = "jpeg";
				$srcFile = imagecreatefromjpeg($srcUrl);
				break;
			case 3:
				$imageType = "png";
				$srcFile = imagecreatefrompng($srcUrl);
				break;
			default:
				return false;
			break;
		}
		
		
		$dstFile = imagecreatetruecolor($dstWidth, $dstHeight);
		
		if ($dstFile) {
			imagecopyresampled($dstFile, $srcFile, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		} else {
			$dstFile = imagecreate($dstWidth, $dstHeight);
			$bgColor=imagecolorallocate($dstFile,255,255,255);
			imagefill($dstFile,0,0,$bgColor);
			imagecopyresized($dstFile, $srcFile, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
		}
		
		$createfunction = "image".$imageType;
		return $createfunction($dstFile,$dstFileName);
	}
	
	/**
	 * 获取文件后缀
	 * @param string $fileUrl
	 * @param bool $tolower
	 * @return string
	 */
	public function getFileExt($fileUrl, $tolower = true) {
		$finfo = pathinfo($fileUrl);
		if ($tolower)
			return strtolower($finfo['extension']);
		else
			return $finfo['extension'];
	}
	
	/**
	 * 将十进制换行成52进制
	 * @param string $dec
	 */
	public static function convert10To52($dec) {
		$base = 'YjEqmwpQsrDVGBSoUiKAfnvzOXHhJPCNkRtLaZuWyxIbMeTFdlcg';
		$result = '';
		do {
			$result = $base[$dec % 52] . $result;
			$dec = intval($dec / 52);
		} while ($dec != 0);
		return $result;
	}
	
	/**
	 * 将52进制转换成10进制
	 * @param string $fifty_two
	 */
	public static function convert52To10($fifty_two) {
		$base_map = array (
				'Y' => 0,
				'j' => 1,
				'E' => 2,
				'q' => 3,
				'm' => 4,
				'w' => 5,
				'p' => 6,
				'Q' => 7,
				's' => 8,
				'r' => 9,
				'D' => 10,
				'V' => 11,
				'G' => 12,
				'B' => 13,
				'S' => 14,
				'o' => 15,
				'U' => 16,
				'i' => 17,
				'K' => 18,
				'A' => 19,
				'f' => 20,
				'n' => 21,
				'v' => 22,
				'z' => 23,
				'O' => 24,
				'X' => 25,
				'H' => 26,
				'h' => 27,
				'J' => 28,
				'P' => 29,
				'C' => 30,
				'N' => 31,
				'k' => 32,
				'R' => 33,
				't' => 34,
				'L' => 35,
				'a' => 36,
				'Z' => 37,
				'u' => 38,
				'W' => 39,
				'y' => 40,
				'x' => 41,
				'I' => 42,
				'b' => 43,
				'M' => 44,
				'e' => 45,
				'T' => 46,
				'F' => 47,
				'd' => 48,
				'l' => 49,
				'c' => 50,
				'g' => 51,
		);
		$result = 0;
		$len = strlen($fifty_two);
	
		for ($n = 0; $n < $len; $n++) {
			$result *= 52;
			$result += $base_map[$fifty_two{$n}];
		}
	
		return $result;
	}
	/**
	 * 
	 * 返回截取过的简短的字符串
	 * @param string $string 要截取的字符串
	 * @param int $length 字符串截取长度
	 * @param string $suffix 截取的后缀 
	 */
	public static function cutString($string,$length=8,$suffix='...'){
		if( mb_strlen($string,'utf8')>$length)
		return mb_substr($string,0,$length-1,'utf8').$suffix;
		else
		return $string;
	}
	
	const ENCODING = 'utf-8';
	
	/**
	 * 分割字符串  
	 * @param string $string
	 * @param int $length 第一段最大长度
	 * @return array  e.g array('第一段', '剩余')
	 */
	public static function splitStringWithFace($string, $length = 140) {
		return P_Common_Face::splitString($string, $length, self::ENCODING);
	}
	
	/**
	 * 获取字符串长度
	 * @param string $string 被X的字符串
	 * @return int
	 */
	public static function strLen($string) {
		$strLength = mb_strlen($string, self::ENCODING);
		$length = 0;
		for ($i = 0; $i < $strLength; $i++) {
			$length += self::_charLen($string, $i);
		}
		return (int)floor($length);
	}
	
	/**
	 * 剪切字符串，从第一个开始 (已测试，这里的中文算一个，英文两个算一个，不过这里的截取要多半个字符)
	 * @param string $string 被X的字符串
	 * @param int $length 剪切长度
	 * @param string $suffix 后缀，如果字符长度超过设定长度，补充后缀
	 * @return string
	 */
	public static function cutStr($string, $length, $suffix = '') {
		$strLength = mb_strlen($string, self::ENCODING);
		$l = 0;
		for ($i=0; $i<$strLength; $i++) {
			$l += self::_charLen($string, $i);
			if (floor($l) > $length) break;
		}
		if ($i < $strLength) {
			return mb_substr($string, 0, $i, self::ENCODING) . $suffix;
		} else {
			return $string;
		}
	}
	
	/**
	 * substr字符串  （已测试， 这个和Common_String::cutStr是一样的，中文算一个，英文两个算一个）
	 * @param string $string 被X的字符串
	 * @param int $start 开始位置 暂时只支持大于等于0的整数
	 * @param int $length 长度 暂时只支持大于0的整数
	 * @param boolean $isCeil 是否进位，当有小数位时，是否进位到下一个整数，默认是false
	 * @return string
	 */
	public static function subStr($string, $start, $length, $isCeil = false) {
		$strLength = mb_strlen($string, self::ENCODING);
		$l = 0;
		$fromLength = $start;
		$toLength = $fromLength + $length;
		$resultString = '';
		for ($i=0; $i<$strLength; $i++) {
			$l += self::_charLen($string, $i);
			$ll = $isCeil ? floor($l) : $l;
			if ($ll > $fromLength && $ll <= $toLength) {
				$resultString .= mb_substr($string, $i, 1, self::ENCODING);
			} elseif ($ll > $toLength) {
				break;
			}
		}
		return $resultString;
	}
	
	/**
	 * 截取混合类型的字符串，如果中文长度的三倍大于英文长度，那么中文长度意义不到
	 * @like i am 肖
	 * @param string $string 被X的字符串
	 * @param int $start 开始位置 暂时只支持大于等于0的整数
	 * $param int $engLength 英文字长
	 * @param int $chnLength 中文字长
	 */
	public static function subMixStr($string, $start, $engLength, $chnLength)
	{
		$string = mb_substr($string, $start);
		$strLength = mb_strlen($string, self::ENCODING);
		if ($strLength < $chnLength)
			return $string;
		$rusultString = '';
		$englen = 0;
		$chnlen = 0;
		for ($i = 0; $i < $strLength; $i++) {
			$temp = mb_substr($string, $i, 1, self::ENCODING);
			$rusultString .= $temp;
			if(strlen($temp) > 1){
				++$chnlen;
			} else {
				++$englen;
			}
			$lenSum = $chnlen * 3 + $englen;
			if ($lenSum >= $chnLength * 3 || $lenSum >= $engLength)
				break;
		}
		return $rusultString;
	}
	
	public static function autolink($foo, $short = false)
	{
		$foo = preg_replace('(((f|ht){1}tp(s?):\/\/)[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)', '<a href="\0" target=_blank rel=nofollow>\0</a>', $foo);
		
		if( strpos($foo, "http") === FALSE && strpos($foo, "ftp") === FALSE ){
			$foo = preg_replace('(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="http://\0" target=_blank rel=nofollow >\0</a>', $foo);
		}else{
			$foo = preg_replace('/([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/', '\1<a href="http://\2" target=_blank rel=nofollow >\2</a>', $foo);
		}
		if ($short) {
			preg_match_all('(((f|ht){1}tp(s?):\/\/)[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)', $foo, $matches);
			$urls = $matches[0];
			if (!empty($urls)) {
				$shortUrls = array();				
				foreach ($urls as $url) {
					$shortUrls[] = P_ShortUrl::HOST. P_ShortUrl::getId64BySrcUrl($url);
				}
				$foo = str_replace($urls, $shortUrls, $foo);
			}
		}
		return $foo;
	}
	
	/**
	 * 返回字符串中某个字符的长度 
	 * @param string $string
	 * @param int $start
	 * @return number
	 */
	private static function _charLen($string, $start) {
		$char = mb_substr($string, $start, 1, self::ENCODING);
		if (strlen($char) > 2) {
			return 1;
		} else {
			return 0.5;
		}
	}
	
}