<?php

/**
 * @desc ajax 公用类，可以渲染模板页面（后续完成）(返回多重格式的数据，xml、text等)
 * 这里和yii有关
 * @author bear
 * @version 1.1.1 2011-10-27 15:27 
 * @copyright xiqiyanyan.com
 * @created 2011-10-27 14:33
 */
class Common_Ajax
{
	/**
	 * 判断是否为ajax请求
	 * @return boolean true:是； false:否
	 */
	public static function isAJAX() {
		return self::isJQueryAjax() || self::isNativeAjax();
	}
	
	/**
	 * 判断是否为ajax请求；特别注意了：此函数判断是是jQuery的ajax，无法判断原生态的ajax
	 * 准确来说，jquery内部实现ajax的时候，已经加入了标识；jquery源码中是这样的：xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");所以，在php中可以通过HTTP_X_REQUESTED_WITH来判断，不需要另外实现 
	 * @return boolean
	 */
	public static function isJQueryAjax() { // Yii::app()->request
	    $request = Yii::app()->getRequest();
        return $request->getIsAjaxRequest() || $request->getIsFlashRequest();
	}
	
	/**
	 * 判断是否ajax请求，用于原生态的js(注意了本函数依赖前段js请求时需带上头信息：xmlHttp.setRequestHeader("request_type","ajax");)
	 * @return boolean
	 */
	public static function isNativeAjax() {
		return isset($_SERVER['HTTP_REQUEST_TYPE']) && ($_SERVER['HTTP_REQUEST_TYPE']=='ajax');
	}
	
	/**
	 * 输出ajax数据，如果有布局和视图就取消layout和render
	 * @param string $message 错误或成功提示信息
	 * @param boolean $status
	 * @param object | array | string $data 
	 * @param string $type
	 * @return string | object  string JSON encoded object
	 */
	public static function outputAJAX($message = null, $status = true, $data = null, $type = 'json') {
		$response = array();
		$response['status'] = $status;
		if ($message !== null) {
			$response['message'] = $message;// 返回的提示信息
		}
		if ($data !== null) {
			$response['data'] = $data;// 返回的数据
		}
		if ($type == 'text') {
			echo $data;
		} else { // CJSON::encode( $json ); 
			echo json_encode($response);
		}	
	}
	

	/**
	 * 直接输出数据
	 * @param Zend_Controller_Action $controller
	 * @param mixed $data
	 * @param string $type
	 * @return string | json_object
	 */
	public static function outputData(Zend_Controller_Action $controller, $data = null, $type = 'json') {
		if (Zend_Controller_Action_HelperBroker::hasHelper('layout')) {
			$controller->getHelper('layout')->disableLayout();
		}
		$controller->getHelper('viewRenderer')->setNoRender();
		if ($type == 'text') {
			echo $data;
		} else {
			echo Zend_Json::encode($data);
		}	
	}
	
	/**
	 * 输出ajax数据，如果是ajax请求，则输出json数据(默认)，并取消layout和render
	 * @param object | array | string $data
	 * @param boolean $status
	 * @param object | array | string $data
	 * @param string $type
	 * @return string | object  string JSON encoded object
	 */
	public static function output($message = null, $status = true, $data = null, $type = 'json') {
		if (self::isAJAX()) {
			self::outputAJAX($message, $status, $data, $type);exit();
		}
	}
	
	/**
	 * TODO 自动寻找模板文件，下面可能已经没用，
	 * auto load tmpl file, if request url has tmpl
	 * @return array
	 */
  	protected static function tmplHtml(Zend_Controller_Action $controller, $data) {
    	$tempName = $controller->getRequest()->getParam('tmpl','');
    	if ($tempName !== '' && is_array($data))
			$data['tmpl'] = $controller->view->render($controller->getRequest()->controller . '/' . $tempName . '.tmpl');
		
		return $data;
	}
	
}
