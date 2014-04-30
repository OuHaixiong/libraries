<?php
/**
 * 临时类，供设计部调用
 * Gp_Model_Temp 
 * @author scoolin@gmail.com
 * @version 1.0.0 2011-7-19 下午05:11:51
 * @copyright 2011 zilanxing.com
 */
class Tmpl
{
	/**
	 * 加载静态文件，并渲染
	 * @param string $filename 静态文件地址 
	 * @return string
	 */
	public static function load($filename) {
		$filename = APPLICATION_PATH . '/../public/template/' . $filename;
		if (file_exists($filename))
			return P_Common_Tmpl::render(file_get_contents($filename));
		else
			return '';
	}
	
	/**
	 * 渲染文本
	 * @param string $content
	 * @return string
	 */
	protected static function render($content) {
		$pattern = '/<!--\s\{(.)+\}\s-->/';
		return preg_replace_callback($pattern, 'P_Common_Tmpl::replaceMatch', $content);
	}
	
	/**
	 * 执行代码，并返回文本
	 * 这是preg_replace_callback，需要执行的callback函数
	 * @param array $match
	 * @return string
	 */
	protected static function replaceMatch($match) {
		$match = $match[0];
		$match = str_replace('<!-- {', '', $match);
		$match = str_replace('} -->', '', $match);
		eval('$match=P_Common_Tmpl::' . $match . ';');
		return $match;
	}
}