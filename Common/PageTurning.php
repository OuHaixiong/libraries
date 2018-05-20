<?php
/**
 * @desc 翻页类，主要用于ajax
 * @author bear
 * @version 1.1.1 2012-4-1 12:05
 * @copyright http://maimengmei.com
 * @created 2011-11-03 12:14
 * TODO 差数和连接地址也可以往模板中传[如果模板中支持的话]
 */
class Common_PageTurning
{
//firstItemNumber 	integer 	当前页上第一条记录是整个记录集的第几条
//firstPageInRange 	integer 	第一个显示出的页码（各页码样式不同）
//lastItemNumber   	integer 	当前页上最后一条记录是整个记录集的第几条
//lastPageInRange 	integer 	最后一个显示出的页码（各页码样式不同) 
	private $First;            // 第一页的页码
	private $Current;          // 当前页码
	private $CurrentItenCount; // 本页上的记录有几条 TODO
	private $ItemCountPerPage; // 每页上最多可以显示几条记录
	private $Last;             // 最后一页的页码
	private $Next = '';        // 下一页的页码
	private $Previous = '';    // 上一页的页码 (没有办法，如果在魔棒方法中不抛异常就可以不用赋值)
	private $TotalItemCount;   // 一共有几条记录
	private $PagesInRange;     // 显示在网页上的页码数组（各页码样式不同）array(4,5,6,7,8)
	private $PageRange;        // 要显示的页码数
	private $PageCount;        // 一共多少页
	private $Params;           // Array 差数 // TODO 差数的编码
	private $ScrollingStyle;   // 中间页码样式，即滚动样式 . 有 All, Sliding, Elastic, Jumping, Sliding
	     //All 显示所有页数 $pagination->count(),下拉列表时使用. TODO
        // Elastic： Google式，页码范围会根据用户当前页而扩展或缩小（注意了这里是有弹性的）；显示页数 PageRange 随当前页数 CurrentPageNumber 的增加而增加.
        // Jumping： 页码最后页之后会显示第一页；当当前页数 CurrentPageNumber % PageRange == 0 时,重新设置页数范围.
        // Sliding： Yahoo式，当前页会放在页码中间，这是默认样式。当前页数 CurrentPageNumber 始终居中于页数范围 PageRange.
	private $Url; // 默认获取当前的url TODO
	
	
	/**
	 * 构造函数，设置总页数、当前页、一页多少条数据、显示页码限制、页码样式、翻页差数
	 * @param integer $totalItemCount 总共多少条记录
	 * @param integer $current 当前页是第几页（页码）
	 * @param integer $itemCountPerPage 每页多少条数据
	 * @param integer $pageRange 中间页码个数
	 * @param string  $url 翻页的url，链接地址
	 * @param array $params 翻页所需的参数（一维数组）[除page参数之外]
	 * @param string $scrollingStyle 中间页码样式 TODO 自定义样式
	 * @throws Study_Model_Communal_Exception
	 */
	public function __construct($totalItemCount, $current = 1, $itemCountPerPage = 10, $pageRange = 5, $url=null, $params = null, $scrollingStyle='Sliding') {
		$this->TotalItemCount = $totalItemCount;
		$this->ItemCountPerPage = $itemCountPerPage;
		$this->PageRange = $pageRange;
		$this->ScrollingStyle = $scrollingStyle;
		$this->Params = $params;
		$this->Url = $url;
		$this->PageCount = ceil($totalItemCount/$itemCountPerPage);
		if ($current < 1) {
			$this->Current = 1;
		} elseif ($current > $this->PageCount) {
			$this->Current = $this->PageCount;
		} else {
			$this->Current = $current;
		}
		if ($this->PageCount <= $pageRange) {
			for ($i=0; $i<$this->PageCount; $i++) {
				$this->PagesInRange[] = $i+1;
			}
		} else {
			$this->buildScrollingStyle();
		}
		if ($current > 1) {
			$this->Previous = $this->Current-1;
		}
		if ($current < $this->PageCount) {
			$this->Next = $this->Current+1;
		}
		$this->First = 1;
		$this->Last = $this->PageCount;
	}
	
	/**
	 * 生成url地址即连接地址和差数
	 * @param array $arrayParams
	 * @return string
	 */
	public function url(array $arrayParams = array()) { // 这里先没有使用zend 的 / 分割差数
		$url = '';
		if ($this->Url != null) {
			$url = $this->Url;
		}
		$url .= '?';
		if ($this->Params != null) {
			foreach ($this->Params as $key=>$param) {
				$url .= $key . '=' . $param . '&';
			}
		}
		if (count($arrayParams) != 0) {
			foreach ($arrayParams as $key=>$param) {
				$url .= $key . '=' . $param . '&';
			}
		}
		if (substr($url, -1) == '?') {
			$url = substr($url, 0, -1);
		}
		if (substr($url, -1) == '&') {
			$url = substr($url, 0, -1);
		}
		return $url;
	}
	
	/**
	 * 根据样式，生成页码数组
	 * @throws Study_Model_Communal_Exception
	 */
	private function buildScrollingStyle() {
		$scrollingStyle = strtolower($this->ScrollingStyle);
		if (method_exists($this, $scrollingStyle)) {
			$this->$scrollingStyle();
		} else {
			throw new Exception('差数scrollingStyle错误，无此样式');
		}
	}
	
	private function all() {
		
	}
	
	/**
	 * 生成页码数组，
	 */
	private function sliding() {
		$totalPage = $this->PageCount;                 // 总页数
		$page = $this->Current;                        // 当前页
		$pageRange = $this->PageRange;                 // 页码限制数
		$minMiddlePage = ceil($pageRange/2);           // 最小中间页码数
		$maxMiddlePage = $totalPage-$minMiddlePage+1;  // 最大中间页码数
		if ($page <= $minMiddlePage) {
			$startPage = 0;
		} elseif ($page >= $maxMiddlePage) {
			$startPage = $totalPage-$pageRange;
		} else {
			$startPage = $page-$minMiddlePage;
		}
		for ($i=0; $i<$pageRange; $i++) {
			$startPage = $startPage+1;
			$this->PagesInRange[] = $startPage;
		}
	}
	
	/**
	 * 魔法方法获取属性 TODO 貌似可以用私有的方法
	 * @param string $propertyName
	 * @throws Study_Model_Communal_Exception
	 */
	public function __get($propertyName) {
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName; 
		} else {
			throw new Exception('Undefined property: ' . $propertyName);
		}
	}
	
	/**
	 * 魔法方法设置属性
	 * @param string $propertyName
	 * @param mixed $value 
	 * @throws Study_Model_Communal_Exception
	 */
	public function __set($propertyName, $value) {
		if (property_exists($this, $propertyName)) {
			$this->$propertyName = $value;
		} else {
			throw new Exception('Undefined property:' . $propertyName);
		}
	}
	
}
