<?php
/**
 * @desc 扩展 Zend_Controller_Action, 所有的控制器都要继承这个类(以便更好的运用)
 * @author bear
 * @version 1.0.0 2011-12-26 10:24
 * @copyright xiqiyanyan.com
 * @created 2011-11-16 10:14
 */
class Common_ControllerAction extends Zend_Controller_Action
{
	/**
	 * 用于 js 引用（块）文件，用在只需一个块的文件
	 */
	public function blockAction() {
		$action = $this->_request->getParam('tmpl');
//		$action = $this->_request->getParam('block');
		if (Zend_Controller_Action_HelperBroker::hasHelper('layout')) {// 判断是否有设布局文件
			$this->_helper->layout()->disableLayout();
//			$this->getHelper('layout')->disableLayout(); // 不显示布局文件
		}
	}
	
	/**
	 * 获取用户上次进入控制器时的日记
	 * @param integer $userId
	 * @param string $fromPlat
	 * @param integer $logsTitle
	 * @return null |　stdClass Object　｜ mixed
	 */
//	public function lastLoginAction($userId, $fromPlat, $logsTitle = 4) {
//		$select = $this->getSelect();
//		$select->where('UserlogsTitle=?', $logsTitle);
//		$select->where('UserlogsUserId=?', $userId);
//		$select->where('UserlogsFromPlat=?', $fromPlat);
//		$select->order('UserlogsCreatedTime desc');
//		return $select->query()->fetchObject();
//	}

	/**
	 * 进入控制器之后写日记，
	 * @see Zend_Controller_Action::postDispatch()
	 */
//	public function postDispatch() {
//		$data = array();
////		$params = $this->getRequest()->getParams();
//		$params = $this->_request->getParams();
//		$userId = P_Auth::getInstance()->Id;
//		if (isset($userId)) {
//			if (count($params)) { // view action 防止视图层也掉用了action
//				$data['UserlogsUserId'] = $userId;
//				$data['UserlogsTitle'] = 4;print_r($params['controller']);exit;
//				$data['UserlogsInfo'] = $params['controller'] . ' --> ' . $params['action'];
//				$data['UserlogsIp'] = P_Putils_Common::getIp();
//	//			$data['UserlogsFromPlat'] = P_Auth::getInstance()->User->UserFromPlat;
//				$logs = new P_Userlogs();
//				$logs->insert($data);
//			}
//		}
//		parent::postDispatch();
//	}	
	
//	public function __call($action, $arguments) {
//		echo 'Action = ' . $action . '<br />';
//		echo 'Arguments = ' . $arguments;
//		echo '页面不存在';
//		exit;
//	}
	
}
