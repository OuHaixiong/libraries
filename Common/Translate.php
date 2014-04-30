<?php

/**
 * @desc 翻译类 
 * @author Bear
 * @version 1.0.0 
 * @copyright xiqiyanyan.com
 * @created 2012-12-05 11:58
 */
class Common_Translate
{
    /**
     * 英语
     * @var string
     */
    const EN = 'en';
    
    /**
     * 中文
     * @var string
     */
    const ZH_CN = 'zh-CN';
    
    /**
     * Google翻译
     * @param string $text 需要翻译的字符串
     * @param string $src 源语种, 如：zh-CN
     * @param string $dst 目标语种， 如： en
     * @param string $url google 翻译接口url，固定地址，如： http://translate.google.cn
     * @return string 翻译后的字符串， 如果没有翻译出来就返回空字符串
     */
    public static function google($text, $src, $dst, $url = 'http://translate.google.cn') {
		$html = '';
		if ($url != '' && $text != '') {
			$ch = curl_init ( $url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 1 );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt ( $ch, CURLOPT_TIMEOUT, 15 );
			
			$fields = array (
					'hl=zh-CN',
					'langpair=' . $src . '|' . $dst,
					'ie=UTF-8',
					'text=' . urlencode ( $text ) 
			);
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, implode ( '&', $fields ) );
			
			$html = curl_exec ( $ch );
			if (curl_errno ( $ch ))
				$html = '';
			curl_close ( $ch );
		}
		
		preg_match ( '/<span title=\"' . mb_convert_encoding ( $text, 'GB2312', 'UTF-8' ) . '\"[^>]*>([\d\D]*)<\/span>/iU', $html, $arr_text );
		if (isset($arr_text [0])) {
			return iconv('gbk', 'utf-8//IGNORE', strip_tags ( $arr_text [0] )); // 转换编码
		} else {
			return '';
		}
	}
    


    
    
}
