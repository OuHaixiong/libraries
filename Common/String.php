<?php

/**
 * @desc 公共字符串处理类
 * @author bear
 * @version 1.1.1 2012-03-05 14:23
 * @copyright http://maimengmei.com
 * @created 2012-2-10 10:55
 * mb_strimwidth 这个php的函数也有点点像 mb_strcut
 */
class Common_String
{
	private static $_encoding = 'UTF-8'; // 字符编码
	
	/**
	 * 设置字符编码，也可以直接用 类名::属性名 获取和赋值
	 * @param string $encoding
	 */
	public static function setEncoding($encoding) {
		self::$_encoding = $encoding;
	}
	
	/**
	 * 获得utf-8 字符串的长度, 中英文都算一个字 (与 strlen() 不一样)
	 * @param integer $str
	 * @return number
	 */
	public static function strlenUtf8($str) {
		$i = 0;
		$count = 0;
		$len = strlen($str);
		while ($i < $len) {
			$chr = ord($str[$i]);
			$count++;
			$i++;
			if ($i >= $len) break;
			if ($chr & 0x80) {
				$chr <<= 1;
				while ($chr & 0x80) {
					$i++;
					$chr <<= 1;
				}
			}
			 
		}
		return $count;
	}

	/**
	 * 截取utf-8字符串，中英文都算一个字
	 * @param string $str 需要截取的字符串
	 * @param integer $start 开始位置
	 * @param integer $length 截取长度
	 * @return string
	 */
	public static function subStrUtf8($str, $start, $length) {
		$len = strlen($str);
		$r = array();
		$n = 0;
		$m = 0;
		for ($i=0; $i<$len; $i++) {
			$x = substr($str, $i, 1);
			$a = base_convert(ord($x), 10, 2);
			$a = substr('00000000' . $a, -8);
			if ($n < $start) {
				if (substr($a, 0, 1) == 0) {
				} elseif (substr($a, 0, 3) == 110) {
					$i += 1;
				} elseif (substr($a, 0, 4) == 1110) {
					$i += 2;
				}
				$n++;
			} else {
				if (substr($a, 0, 1) == 0) {
					$r[] = substr($str, $i, 1);
				} elseif (substr($a, 0, 3) == 110) {
					$r[] = substr($str, $i, 2);
					$i += 1;
				} elseif (substr($a, 0, 4) == 1110) {
					$r[] = substr($str, $i, 3);
					$i += 2;
				} else {
					$r[] = '';
				}
				if (++ $m >= $length) {
					break;
				}
			}
		}
		return join('', $r);
	}
	
	/**
	 * 获取摘要简介信息，如果内容中有图片，将提取最前一张图片放在返回的字符串的最前面
	 * @param string $content 可包含html的源内容
	 * @param integer $length 需要截取的长度，一个英文字符和一个汉字都算一个字
	 * @param string $endString 最后添加的字符串
	 * @return string
	 */
	public static function getBrief($content, $length = 100, $endString = '...') {
		$pattern = '/<img[^>]*src\=(\'|\")(([^>]*)(jpg|gif|png|bmp|jpeg))\\1/i'; // 获取所有图片标签的全部信
		preg_match_all($pattern, $content, $matches);
		$outputStr = '';
		if (sizeof($matches[2])) { // sizeof() 是 count() 的别名 ，计算数组中的单元数目或对象中的属性个数
			$outputStr = '<img width="30" src="' . $matches[2][0] . '" onclick="resize_pic(this, 30, 176)" />';
		}
		$content = strip_tags($content); // string strip_tags(string $str [, string $allowable_tags])  从字符串中去除 HTML 和 PHP 标记，HTML 注释和 PHP 标签也会被去除。
		// 该函数尝试返回给定的字符串 str 去除空字符、HTML 和 PHP 标记后的结果。它使用与函数 fgetss() 一样的标记去除状态机。 
		// 后面的参数是允许保留的标签  如： 允许 <p> 和 <a> ， strip_tags($text, '<p><a>');
		if (self::strlenUtf8($content) > $length) {
			$outputStr .= self::subStrUtf8($content, 0, $length) . $endString;
		} else {
			$outputStr .= $content;
		}
		return $outputStr;
	}
	
	/**
	 * 截取中文字，一个中、英文字都算一个（这个和上面的 subStrUtf8() 是一样的）； 如果总长度多于要截取的字符串就拼接后面的字符串
	 * @param string $str 需要截取的字符串
	 * @param integer $start 开始的位置， from where start
	 * @param integer $len 截取的长度
	 * @param string $suffix 后面需要拼接的字符串
	 * @return string
	 */
	public static function utf8SubStr($str, $start, $len = 180, $suffix = '') {
		$str = trim($str);
        $str = strip_tags($str);
        $strlen = strlen($str);
        $str = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
        if ($strlen != strlen($str)) {
            $str .= $suffix; // 如果是从0位置开始截取到最后，就不会连接后面的字符串
        }
        return $str;
    }

	/**
	 * 截取UTF-8编码的多字节字符串 ; 中英文都算一个字（类似 mb_substr）
	 * @param string $str
	 * @param integer $start
	 * @param integer $len
	 * @return string
	 */
	public static function interceptUtf8String($str, $start, $len) {
		return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $start . '}((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
	}
	
	/**
	 * 按字来截取字符串，不包括开始位置字符；$encoding 是按什么编码来截取（utf-8等），需和文档编码一致（这里是php的 mb_substr）
	 * @param string $string  需要截取的字符串
	 * @param int $start      截取字符串的开始位置
	 * @param int $length     截取的长度
	 * @return string         截取好的字符串
	 */
	public static function interceptChar($string, $start, $length) {
		return mb_substr($string, $start, $length, self::$_encoding);
	}
	
	/**
	 * 按字节截取字符串，可能包括开始位置字符，看编码来的，$encoding也须和文档编码一致，不然出乱码（这里是php的 mb_strcut）
	 * @param string $string 需要截取的字符串
	 * @param int $start     截取字符串的开始位置
	 * @param int $length    截取的长度
	 * @return string        截取好的字符串
	 */
	public static function interceptBit($string, $start, $length) {
		return mb_strcut($string, $start, $length, self::$_encoding);
	}
	
	/**
	 * 按占位来截取字符串，一个中文占两个位，一个英文占一个位
	 */
	public static function interceptPlaceholder() {
		// TODO
		
	}
	
	/**
	 * 按网页显示占位截取字符（标准是一个汉字一个字符）。 在网页两个数字或字母占用一个字，汉字一个字占用一个字 ；本程序不支持负数； (还得测试)
	 * 如果，仅是截取中文字，一个英文字或数字和中文字都算一个字符，那建议使用 mb_substr
	 * @param string $str 要截取的字符串
	 * @param integer $start 开始位置，一个中文和一个英文或数字算一个字
	 * @param integer $length 截取的总长度，一个汉字或两个字母算一个字；如果不传即截取开始字符以后的所有字符
	 * @param string $encoding 编码，默认UTF-8 ，可传入 gb2312
	 * @return string 截取好的字符串；开始位置不包括在截取长度之内;有时会少一个字
	 */
	public static function interceptStringForDisplayOccupied($str, $start, $length=null, $encoding='utf-8') {
		$encodingOccupied = 1/3; // 编码在本脚本的字节数，在gb2312下为1/2，在utf-8为1/3。即utf-8编码的汉字占三个字节
		$stringLength = strlen($str);
		$str = mb_substr($str, $start, $stringLength, $encoding);
		$stringLength = strlen($str);
		if ($length === null) {
			return mb_substr($str, 0, $stringLength, $encoding);
		}
		$number = 0; // 累积量
		for ($i=0; $i<$stringLength; $i++) {
			if (ord($str[$i])>=128 and ord($str[$i])<=255) { // 貌似这里不知道怎么写才好
				// 中文字符在字符串中的ASCII码是从129到254的，即GB编码的中文字符高位范围是 0x81-0xFE
				$number = $number + $encodingOccupied;
			} else {
				$number = $number + 1/2; // 两个字母或两个数字算一个字（占用一个字符位置）
			}
			if ($number > $length) {
				break;
			}
		}
//		return substr($str, 0, $i); // $i 为真正需要截取的长度
		return mb_strcut($str, 0, $i, $encoding); // $i 为真正需要截取的长度
	}
	
	/**
	 * 按占位来截取字符串，一个中文占一个位，两个英文占一个位 (注意：这里只能用utf-8和开始只能是0)
	 * @param string $string
	 * @param integer $length 所占的位个数
	 * @param integer $start TODO 开始位置
	 * @return string
	 */
	public static function cutStr($string, $length, $start = 0) {
		$strLength = mb_strlen($string, self::$_encoding);
		$l = 0;
		for ($i=0; $i<$strLength; $i++) {
			$char = mb_substr($string, $i, 1, self::$_encoding);
			if (strlen($char) > 2) {
				$l += 1;
			} else {
				$l += 0.5;
			}
			if ($l > $length) {
				break;
			}
		}
		return mb_substr($string, $start, $i, self::$_encoding);
	}
	
	/**
	 * 按字节来截取字符串（不建议使用，还是用php原生态的mb_strcut） ;  这个是取最大的数算的，比 ‘12345我的’ 取6个字节返回 ‘12345我’
	 * @param string $string
	 * @param integer $length
	 * @return string
	 */
	public static function wordsCut($string, $length) {
		if (strlen($string) > $length) {
			$wordscut = '';
			for ($i=0; $i<$length; $i++) {
				if (ord($string[$i]) > 127) {
					$wordscut .= $string[$i].$string[$i+1].$string[$i+2];
					$i= $i+2;
				} else {
					$wordscut .= $string[$i];
				}
			}
			return $wordscut;
		} 
		return $string;
	}
	
	/**
	 * 中英文都算一个字 ； 和 mb_substr 类似
	 * @param string $str 源字符串
	 * @param integer $len 截取字符串的个数
	 * @param string $charset 编码：utf-8, gb2312
	 * @return string
	 */
	function left ($str, $len, $charset = "utf-8") {
	    //如果截取长度小于等于0，则返回空
	    if (! is_numeric($len) or $len <= 0) {
	        return "";
	    }
	    //如果截取长度大于总字符串长度，则直接返回当前字符串
	    $sLen = strlen($str);
	    if ($len >= $sLen) {
	        return $str;
	    }
	    //判断使用什么编码，默认为utf-8
	    if (strtolower($charset) == "utf-8") {
	        $len_step = 3; //如果是utf-8编码，则中文字符长度为3  
	    } else {
	        $len_step = 2; //如果是gb2312或big5编码，则中文字符长度为2
	    }
	    //执行截取操作
	    $len_i = 0; //初始化计数当前已截取的字符串个数，此值为字符串的个数值（非字节数）
	    $substr_len = 0; //初始化应该要截取的总字节数
	    for ($i = 0; $i < $sLen; $i ++) {
	        if ($len_i >= $len)
	            break; //总截取$len个字符串后，停止循环
	        //判断，如果是中文字符串，则当前总字节数加上相应编码的中文字符长度
	        if (ord(substr($str, $i, 1)) > 0xa0) {
	            $i += $len_step - 1;
	            $substr_len += $len_step;
	        } else { //否则，为英文字符，加1个字节
	            $substr_len ++;
	        }
	        $len_i ++;
	    }
	    $result_str = substr($str, 0, $substr_len);
	    return $result_str;
	}
	
	/**
	 * 截取指定长度的字符串(UTF-8专用 汉字和大写字母长度算1，其它字符长度算0.5)
	 * 这个没有看 ，貌似中文算一个，英文三个算一个
	 * @param string $string: 原字符串
	 * @param int $length: 截取长度
	 * @param string $etc: 省略字符（...）
	 * @return string: 截取后的字符串
	 */
	function cut_str($sourcestr, $cutlength = 80, $etc = '...') {
	    $returnstr = '';
	    $i = 0;
	    $n = 0.0;
	    $str_length = strlen($sourcestr); //字符串的字节数
	    while (($n < $cutlength) and ($i < $str_length)) {
	        $temp_str = substr($sourcestr, $i, 1);
	        $ascnum = ord($temp_str); //得到字符串中第$i位字符的ASCII码
	        if ($ascnum >= 252) {//如果ASCII位高与252
	            $returnstr = $returnstr . substr($sourcestr, $i, 6); //根据UTF-8编码规范，将6个连续的字符计为单个字符
	            $i = $i + 6; //实际Byte计为6
	            $n ++; //字串长度计1
	        } elseif ($ascnum >= 248) {//如果ASCII位高与248
	            $returnstr = $returnstr . substr($sourcestr, $i, 5); //根据UTF-8编码规范，将5个连续的字符计为单个字符
	            $i = $i + 5; //实际Byte计为5
	            $n ++; //字串长度计1
	        } elseif ($ascnum >= 240) {//如果ASCII位高与240
	            $returnstr = $returnstr . substr($sourcestr, $i, 4); //根据UTF-8编码规范，将4个连续的字符计为单个字符
	            $i = $i + 4; //实际Byte计为4
	            $n ++; //字串长度计1
	        } elseif ($ascnum >= 224) {//如果ASCII位高与224
	            $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
	            $i = $i + 3; //实际Byte计为3
	            $n ++; //字串长度计1
	        } elseif ($ascnum >= 192) {//如果ASCII位高与192
	            $returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
	            $i = $i + 2; //实际Byte计为2
	            $n ++; //字串长度计1
	        } elseif ($ascnum >= 65 and $ascnum <= 90 and $ascnum != 73) {//如果是大写字母 I除外
	            $returnstr = $returnstr . substr($sourcestr, $i, 1);
	            $i = $i + 1; //实际的Byte数仍计1个
	            $n ++; //但考虑整体美观，大写字母计成一个高位字符
	        } elseif (! (array_search($ascnum, array(37, 38, 64, 109, 119)) === FALSE)) {//%,&,@,m,w 字符按１个字符宽
	            $returnstr = $returnstr . substr($sourcestr, $i, 1);
	            $i = $i + 1; //实际的Byte数仍计1个
	            $n ++; //但考虑整体美观，这些字条计成一个高位字符
	        } else {//其他情况下，包括小写字母和半角标点符号
	            $returnstr = $returnstr . substr($sourcestr, $i, 1);
	            $i = $i + 1; //实际的Byte数计1个
	            $n = $n + 0.5; //其余的小写字母和半角标点等与半个高位字符宽...
	        }
	    }
	    if ($i < $str_length) {
	        $returnstr = $returnstr . $etc; //超过长度时在尾处加上省略号
	    }
	    return $returnstr;
	}
	
	/**
	 * 计算字符串的长度;汉字按照两个字符计算 
	 * @param string $str
	 * @return number | integer
	 */
	public static function stringLen($str) {
		$length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
		if ($length) {
			return strlen($str) - $length + intval($length / 3) * 2;
		} else {
			return strlen($str);
		}
	} 
	
	/**
	 * 按字来截取字符串，一个中文和一个英文都是一个字，和 mb_substr 是一样的
	 * @param string $str
	 * @param integer $start
	 * @param 第三个参数是截取长度
	 * @return string
	 */
	public static function mbsubstr($str,$start) {
		/*
		 * UTF-8 version of substr(), for people who can't use mb_substr() like
		 * me. Length is not the count of Bytes, but the count of UTF-8
		 * Characters Author: Windix Feng Bug report to: windix(AT)263.net,
		 * http://www.douzi.org/blog - History - 1.0 2004-02-01 Initial Version
		 * 2.0 2004-02-01 Use PREG instead of STRCMP and cycles, SPEED UP!
		 */
		preg_match_all ( '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/', $str, $ar );
		if (func_num_args () >= 3) {
			$end = func_get_arg ( 2 );
			return join ( '', array_slice ( $ar [0], $start, $end ) );
		} else {
			return join ( '', array_slice ( $ar [0], $start ) );
		}
	}
	
	/**
	 * 判断是不是gbk编码字符串（只测试过一次，貌似没有问题）
	 * @param string $str
	 * @return boolean
	 */
	public static function isGB($str) {
        $strLen = strlen($str);
        $length = 1;
        $legalflag = false;
        for ($i = 0; $i < $strLen; $i ++) {
            $tmpstr = ord(substr($str, $i, 1));
            $tmpstr2 = ord(substr($str, $i + 1, 1));
            if (($tmpstr <= 161 || $tmpstr >= 247) && ($tmpstr2 <= 161 || $tmpstr2 >= 247)) {
                $legalflag = false;
            } else {
                $legalflag = true;
            }
        }
        return $legalflag;
    }

    /**
     * 翻转/反转字符串
     * @param string $str 输入的字符串（可以中英文混合）
     * @return string
     */
    static public function reversal($str) {
        $len = mb_strlen($str, self::$_encoding);
        $temp = '';
        for($i=$len-1; $i>=0; $i--) {
            $temp .= mb_substr($str, $i, 1, self::$_encoding);
        }
        return $temp;
    }

}
