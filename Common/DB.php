<?php
/**
 * 数据库操作类，封装了mysqli的一些函数（单例）; 数据库操作层（数据持久层）
 * 实际上，配置信息类、管理类、控制类、门面类、代理类通常被设计为单例类。像Java的Struts、Spring框架，.Net的Spring.Net框架，以及Php的Zend框架都大量使用了单例模式。
 * // TODO 多主键的考虑
 * @author bear
 * @version 1.0.0
 * @copyright xiqiyanyan.com
 * @created 2012-2-15 10:59 
 * @example  $db = Common_DB::getInstance(); $sql = 'select * from News order by AddTime desc'; $number = $db->fetchNum($sql);
 */
class Common_DB
{
	const __HOST__ = 'localhost';
	const __PORT__ = '3306';
	const __USER__ = 'root';
	const __PASSWORD__ = '123456';
	const __DATABASE__ = 'Study';

	/**
	 * 数据库连接资源 the database connection
	 * @var resource
	 * @access private
	 */
	private $_link;
	
	/**
	 * 最后一次操作数据库的sql语句
	 * @var string
	 */
	private $_sql;
	
	/**
	 * 保持单例类的静态变量. the static instance of single db
	 * 还有饿汉单例，饿汉单例就是在类加载(初始化)时就创建实例，如： private static $_instance = new self;
	 * @var object
	 * @access static
	 */
	private static $_instance; 
	
	/**
	 * 私有的构造函数.construct the single object
	 */
	private function __construct() {
		$this->_link = @mysqli_connect(self::__HOST__, self::__USER__, self::__PASSWORD__, self::__DATABASE__, self::__PORT__); // 外部常量可以直接写常量名，本类常量用self::常量名
		if (!($this->_link)) {
//			echo 'Something wrong occurs on the database connection!';
			throw new Exception('数据库连接错误：' . mysqli_connect_error());
		}
		$this->_link->query('set names utf8'); // mysqli_query($this->_link, 'set names utf8');
	}
	
	/**
	 * 防止单例类被克隆.empty clone
	 * @access private
	 * @return null
	 */
	private function __clone() {
	}
	
	/**
	 * 外界访问单例类实例的接口.获取数据库操作db对象. for other object to get the instance of db
	 * @access public
	 * @return Common_DB | self::$_instance
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * 设置sql语句
	 * @param string $sql
	 */
	public function setSql($sql) {
		$this->_sql = $sql;
	}
	
	/**
	 * 获取最后一次操作数据库的sql语句
	 * @return string
	 */
	public function getSql() {
		return $this->_sql;
	}
	
	/**
	 * query sql string 执行一条sql语句
	 * @access public
	 * @param string $sql
	 * @param string $message 出错信息提示 Query failed message
	 * @return resource
	 */
	public function query($sql = null, $message = null) {
		if ($sql !== null) {
			$this->_sql = $sql;
		}
		$result = @mysqli_query($this->_link, $this->_sql) or die($message . mysqli_error($this->_link));
		return $result;
	}
	
	/**
	 * mysqli_num_rows 返回结果集中的总行数
	 * @access public
	 * @param resource $result
	 * @return integer
	 */
	public function num($result) {
		return @mysqli_num_rows($result);
	}

	/**
	 * mysqli_fetch_array
	 * @access public
	 * @param resource $result
	 * @return array
	 */
	public function fetchArray($result) {
		return @mysqli_fetch_array($result);
	}

	/**
	 * mysqli_insert_id
	 * @access public
	 * @param resource $result
	 * @return integer
	 */
	public function lastId() {
		return @mysqli_insert_id($this->_link);
	}
	
	/**
	 * 获取mysql前一次操作（select，delete）所影响的行数
	 * mysqli_affected_rows
	 * @return integer 
	 */
	public function affectedRows() {
		return mysqli_affected_rows($this->_link);
	}

	/**
	 * close the datebase connection
	 * @access public
	 * @return null
	 */
	public function close() {
		@mysqli_close($this->_link);
//		unset(self::$_instance); // 特别注意了：unset 是能够释放静态变量的（将销毁此变量及其所有的引用），但在这里貌似这样用是错误的，因为对象本身不能释放本身
		self::$_instance = null;
	}

	/**
	 * fetch once result from the specific sql query
	 * 获取一行数据
	 * @access public
	 * @param string $sql
	 * @param string $message
	 * @return array | null 一维数组或null
	 */
	public function fetchArrayOnce($sql = null, $message = null) {
		if ($sql !== null) {
			$this->_sql = $sql;
		}
		$result = $this->query($this->_sql, $message);
		$row = $this->fetchArray($result);//return mysqli_fetch_assoc($result);// yes return mysqli_fetch_object($result); // yes
		return $row;
	}

	/**
	 * fetch all result from the specific sql query
	 * @access public
	 * @param string $sql
	 * @param string $message
	 * @return array
	 */
	public function fetchArrayMore($sql = null, $message=null) {
		if ($sql !== null) {
			$this->_sql = $sql;
		}
		$result = $this->query($this->_sql, $message);
		$moreRow = array();
		while (($row = $this->fetchArray($result)) == true) {
			$moreRow[] = $row;
		}
		return $moreRow;
	}

	/**
	 * fetch the number of results from the specific sql query  返回sql语句的总行数
	 * @access public
	 * @param string $sql
	 * @param string $message
	 * @return integer
	 */
	public function fetchNum($sql = null, $message = null) {
		if ($sql !== null) {
			$this->_sql = $sql;
		}
		$result = $this->query($this->_sql, $message);
		return $this->num($result);
	}
	
	/**
	 * mysqli_prepare 获取sql语句信息
	 * @access public
	 * @param string $sql
	 * @return object mysqli_stmt
	 */
	public function prepare($sql = null) {
		if ($sql !== null) {
			$this->_sql = $sql;
		}
		return @mysqli_prepare($this->_link, $this->_sql);
	}

	/**
	 * mysqli_stmt_execute
	 * @access public
	 * @param object $stmt
	 * @param string $message
	 * @return boolean
	 */
	public function stmtExecute($stmt, $message = null) {
		@mysqli_stmt_execute($stmt) or die($message . mysqli_error($this->_link));	
	}
	
	/**
	 * 获取数据库连接资源
	 * @return resource
	 */
	public function getLink() {
		return $this->_link;
	}
	
	/**
	 * 选择数据库（换个数据库）
	 * @param string $databaseName 新的数据库名,如果为null就是说恢复默认选择的数据库
	 */
	public function setDatabase($databaseName = null) {
		if ($databaseName === null) {
			$databaseName = self::__DATABASE__;
		}
		mysqli_select_db($this->_link, $databaseName);
	}
	
}
