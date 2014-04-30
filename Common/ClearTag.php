<?php

/**
 * 清除HTML、JavaScript、空白等和替换单双引号为实体
 * @author bear
 * @version 1.0.0 2011-5-26
 */
class Common_ClearTag
{
	/**
	 * 去掉 HTML 标记，javascript 代码和空白字符。还会将一些通用的 HTML 实体转换成相应的文本。
	 * (貌似不能去掉空白字符和换行符) （有时间研究一下这里的正则表达式）
	 * @desc strip the html
	 * @param string $document 应包含一个 HTML 文档
	 * @return string 替换好的纯文本; 无html标签的字符串
	 */
	public static function clearAll($document) {
		$search = array ( "'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
	                       "'<[\/\!]*?[^<>]*?>'si",           // 去掉 HTML 标记
                           "'([\r\n])[\s]+'",                 // 去掉空白字符 ； 后面这种写法是错误的：   "'([rn])[s]+'",
                           "'&(quot|#34);'i",                 // 替换 HTML 实体
                           "'&(amp|#38);'i",
                           "'&(lt|#60);'i",
		                   "'&(gt|#62);'i",
		                   "'&(nbsp|#160);'i",
		                   "'&(iexcl|#161);'i",
		                   "'&(cent|#162);'i",
		                   "'&(pound|#163);'i",
		                   "'&(copy|#169);'i",
		                   "'&#(\d+);'e"                   // 作为 PHP 代码运行 ； 貌似后面的写法有问题： "'&#(d+);'e"
		);
		$replace = array ("", "", "\\1", "\"", "&", "<", ">", " ", chr(161), chr(162), chr(163), chr(169), "chr(\\1)");
//		$replace = array("", "", "\1", "\"", "&", "<", ">", " ", chr(161), chr(162), chr(163), chr(169), "chr(\1)"); // 这个写法也是错误的
        return preg_replace($search, $replace, $document);    
	}
	
	/**
	 * 清除javaScript
	 * @param string $document
	 * @return string
	 */
	public static function clearJavascript($document) {
		$pattern = '/\<script[^>]*?\>.*?\<\/script\>/is';
		return preg_replace($pattern, '', $document);
        /*preg_replace("/<script[^>].*?>.*?<\/script>/si", "替换内容", $str)*/
	}
	
	public static function clearCss() {
		// TODO
	}
	
	/**
	 * 除去所有的 html 标签
	 * @param string $document 有 html 标签的字符串
	 * @return mixed string 除去了 html 标签的字符串
	 */
	public static function clearHtml($document) {
		$pattern = '/(<[^>]*>)/';
		return preg_replace($pattern, '', $document);
	}
	
	/**
	 * 替换所有的 html 标签 ，并保留你想要的那个标签
	 * @param string $document 有 html 标签的字符串
	 * @param string $tag 保留哪个标签；格式如：'<p><div>'
	 * @return string 替换好的字符串
	 */
	public static function clearHtmlRetainTag($document, $tag) {
		return strip_tags($document, $tag);
	}
	
	/**
	 * 除去 img 和 object 标签
	 * @param string $content
	 * @return string
	 */
	public static function clearImageAndObject($content) {
		$imgPattern = '/<img.*?>/is';//去除image
		$content = preg_replace($imgPattern, '', $content);
		$objectPattern = '/<object.*?>(.*?<\/object>)?/is';//去除object：视频，音频，flash等
		$content = preg_replace($objectPattern, '', $content);
		return $content;
	}
	
	/**
	 * 去掉指定的html标签(没有来得及测试)
	 * @param array $tags
	 * @param string $str
	 * @return (mixed) string
	 */
	public function clearTags(array $tags, $str) {
		foreach ($tags as $tag) {
			$p[] = "/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
		}
		return preg_replace($p, '', $str);
	}
	
	
	
	
}
