<?php

/**
 * @desc 文件、图片上传类 (单个文件上传类，多个请用循环)
 * 暂时不考虑多文件上传(max files)
 * @author bear
 * @copyright xiqiyanyan.com
 * @version 1.1.0 2012-07-27 10:32
 * @created 2011-9-5
 */
class Common_Upload
{
    private $_sourcePath;        // 文件来源路径
    private $_purposePath;       // 文件目标路径(文件上传保存路径);目标文件夹（目录）
    private $_formerFileName;    // 完整的原文件名
    private $_fileExt;           // 文件后缀名；包括点在内，如 ： .jpg
    private $_formerFileSize;    // 原文件大小， b 为单位
    private $_allowFileSize;     // 允许上传的文件大小，默认不得超过 2 Mb, max size of file
    private $_allowExtension = array('.jpg', '.jpeg', '.png', '.gif', '.bmp', '.pdf', '.rar', '.xls', '.xlsx', '.doc', '.docx'); // 允许(allowed)上传的文件格式
    private $_targetFileName;    // 自定义文件名
    private $_isOverwrite;       // overwrite 是否覆盖同名文件; true:允许, false:不允许   '对不起,文件 ".$f_name." 已经存在,上传失败!'
	private $_error;             // 上传错误信息

	/**
	 * 传入参数
     * @param string $sourcePath        源文件路径(包括文件名)
	 * @param string $purposePath       目标文件夹（目录）路径(不包括文件名，后可以带 /，也可以不带)
	 * @param string $formerFileName    完整的原文件名，包括后缀名
	 * @param integer $formerFileSize   原文件大小,单位byte
	 * @param integer $allowFileSize    允许上传文件大小，M 为单位，默认不得超过 2 Mb
	 * @param array $allowExtension     允许上传的文件格式；如：array('.jpg', '.jpeg', '.png', '.gif', '.bmp'...）
	 * @param string $targetFileName    自定义文件名，不包括后缀名
	 * @param boolean $isOverwrite      是否覆盖同名文件；默认false：不覆盖同名文件；true：覆盖同名文件
	 */
    public function __construct($sourcePath, $purposePath, $formerFileName, $formerFileSize, $allowFileSize = 2, $allowExtension = null, $targetFileName = null, $isOverwrite = false) {
		$this->_sourcePath = $sourcePath;
		$this->_purposePath = $purposePath;
		$this->_formerFileName = $formerFileName;
		$this->_fileExt = strtolower(strrchr($formerFileName, '.'));
		$this->_formerFileSize = $formerFileSize;
		$this->_allowFileSize = (float) $allowFileSize;
		if (!empty($allowExtension)) {
			$this->_allowExtension = $allowExtension;
		}
        $this->_targetFileName = $targetFileName;
        $this->_isOverwrite = $isOverwrite;
	}

	/**
	 * 文件上传
	 * @return false | string 上传成功后返回完整文件名（包括后缀名）； 失败后返回false
	 */
    public function uploadFile() {
		if (!$this->validator()) {
			return false;
		}
		if (! $this->_getPurposePath()) {
			return false;
		}
		$this->_getTargetFileName();
//      $upload =new Zend_File_Transfer_Adapter_Http();
//		$result = new Zend_Search_Lucene_Storage_Directory_Filesystem($this->_purposePath); //没有该文件夹则创建目录
/*        $upload->setDestination($this->_purposePath);
        $upload->addFilter('Rename',array('source' => $this->_sourcePath, 'target' => $this->_purposePath . $fileName, 'overwrite' => true));//重新命名      
        $upload->receive(); 貌似多个文件调用时有问题，最后别用他的
        //      $file_name = $adapter->get_fileName(null, false); //获取上传文件名*/ 
		$fileName = $this->_targetFileName;
        $this->_targetFileName = null;
        $dir = $this->_purposePath;
        $this->_purposePath .= $fileName;
        if (file_exists($this->_purposePath)) {
        	if (!$this->_isOverwrite) {
        		$this->setError('同名文件 ' . $this->_purposePath . ' 已存在！');
        		return false;
        	}
        	if (!is_writeable($this->_purposePath)) {
        		$this->setError('无法保存文件！请检测文件 ' . $this->_purposePath . ' 是否有写的权限。');
        		return false;
        	}
        }
        if (!is_writable($dir)) {
        	$this->setError('无法保存文件！（文件夹）目录 ' . $dir . ' 没有写的权限！');
        	return false;
        }
		move_uploaded_file($this->_sourcePath, $this->_purposePath); //  bool move_uploaded_file ( string $filename , string $destination ) 将上传的文件移动到新位置
		return $fileName;	
//     $adapter->addValidator ( '_allowExtension', false, 'jpg,png,gif' )//设置上传格式
//         ->addValidator( 'Size', false, 1048576*2 )//大小addValidator('FilesSize',false,array('min' => '1B', 'max'=>'30MB'));
//         ->addValidator ( 'Count', false, array('min' => 1, 'max' => 3) )//上传文件数量;
	}

	/**
	 * 随机生成字母串
	 * @param int $length 默认 6 个字母
	 * @return string
	 */
	public function randABC($length = 6) {
		$file_arr="abcdefghijklmnopqrstuvwxyz";
		$filename="";	  
		$l=strlen($file_arr);
		for($i=0;$i<$length;$i++)
		{
			$num=mt_rand(0,$l-1);		
			$filename.=$file_arr[$num];   
		}
		return $filename;
	}
	
	/**
	 * 随机生成（字母+数字）串
	 * @param int $length
	 * @return string
	 */
	public function randAbcAndNumber($length = 6) {
		$file_arr="abcdefghijklmnopqrstuvwxyz0123456789";
		$filename="";	  
		$l=strlen($file_arr);
		for($i=0;$i<$length;$i++)
		{
			$num=mt_rand(0,$l-1);		
			$filename.=$file_arr[$num];   
		}
		return $filename;
	}

	/**
	 * 设置上传错误信息
	 * @param string $error
	 */
	public function setError($error) {
		$this->_error = $error;
	}
	
	/**
	 * 获取上传错误信息
	 * @return string
	 */
	public function getError() {
		return $this->_error;
	}
	
	/**
	 * 设置文件上传类型
	 * @param array $extension
	 */
	public function setAllowExtension(array $extension) {
		$this->_allowExtension = $extension;
	}  
	
	/**
	 * 获取文件上传类型
	 * @return array
	 */
	public function getAllowExtension() {
		return $this->_allowExtension;
	}
	
	/**
	 * 删除上传过的文件
	 * @param array | string $filePath
	 */
	public function deleteUploadFile($filePath) {
		if (is_array($filePath)) {
			foreach ($filePath as $path) {
				@unlink($path);
			}
		} else {
			@unlink($filePath);
		}		
	}
	
	/**
	 * 设置目标文件名
	 * @param string $fileName
	 */
	public function setTargetFileName($fileName) {
		$this->_targetFileName = $fileName;
	}
	
	/**
	 * 获取目标文件名
	 * @param string
	 */
	private function _getTargetFileName() {
		if ($this->_targetFileName) {
			return $this->_targetFileName = $this->_targetFileName . $this->_fileExt;	
		} else {
			return $this->_targetFileName = $this->randABC() . date('YmdHis') . $this->randAbcAndNumber() . mt_rand() . $this->_fileExt;	
		}
		// $fileName = $this->randABC() . time() . $this->_fileExt; 
		// microtime(true) ;  time() 返回时间戳；  microtime(true) 返回包括毫秒数的时间戳，如：1326700356.0938
	}

	/**
	 * 获取目标文件夹路径，在保存的文件路径后加 / 
	 * 如果文件夹不存在，可以无条件创建之，除非没有权限
	 * @return string | false 没有写的权限或文件路径错误
	 */
	private function _getPurposePath() {
        $this->_purposePath = $this->formatPath($this->_purposePath);
        if (!file_exists($this->_purposePath)) { // 不存在此目录，创建之
        	$boolean = $this->createdDirectory($this->_purposePath);
			if (!$boolean) {
				return false;
			}
		}
		$this->_purposePath = $this->_purposePath . '/';
		return $this->_purposePath;
	}
	
	/**
	 * 创建目录（文件夹）路径下的所有目录 , created all folder
	 * 这里的方法和 Common_Tool::createdDirectory($directoryPath) 是一样的
	 * @param string $directoryPath 要创建的文件目录（文件夹）路径，只能是绝对路径，或相对路径，
	 * 不能是zend的重写路径（如 ‘/upload/img/’）或http://这样的路径，
	 * @return boolean
	 */
	private function createdDirectory($directoryPath) { // created all folder
		if (!$directoryPath) {
			$this->setError('目标文件路径为空！');
			return false;
		}
		$directoryPath = str_replace('\\', '/', $directoryPath);
		if ($directoryPath[strlen($directoryPath)-1] == '/') {
			$directoryPath = substr($directoryPath, 0,-1);
		}
		$pattern = '/([a-zA-Z]+:\/)?/';
		preg_match($pattern, $directoryPath, $prefixFolder);
		$createdFolder = $prefixFolder[0];
		$directoryPath = str_replace($createdFolder, '', $directoryPath);
		$arrayFolder = explode('/', $directoryPath);
	
		foreach ($arrayFolder as $folder) {
			$folder = trim($folder);
			$parentFolder = $createdFolder;
			$createdFolder .= $folder . '/';
			if (!file_exists($createdFolder)) {
				if (!is_writeable($parentFolder)) {
					$this->setError("无法新建目标文件夹（目录）；请确认目标文件夹路径是否正确或目标文件夹 $parentFolder 是否有写的权限！");
					return false;
				}
				mkdir($createdFolder);
			}
		}
		return true;
	}
	
	/**
	 * 格式化路径，把所有的 \ 转换成 /，并把最后的 / 除掉
	 * @param string $path
	 * @return string
	 */
	private function formatPath($path) {
		$path = str_replace('\\', '/', $path);
		if ($path[strlen($path)-1] == '/') {
			$path = substr($path, 0, -1);
		}
		return $path;
	}

	/**
	 * 验证上传的文件格式和大小
	 * @return boolean
	 */
	private function validator() {
		if (!is_uploaded_file($this->_sourcePath)) { // bool is_uploaded_file(string $filename) 判断文件是否是通过 HTTP POST 上传的
			$this->setError('非法文件，不是上传上来的文件!');
			return false;
		}// TODO 上传文件发生错误$_FILES['NAME']['error']  或 大于php设定的值时，上传的大小为0
        /* $ext_flag = false;
        foreach ($this->_allowExtension as $ext) {
        	if ($ext == $this->_fileExt) {
        		$ext_flag = true;
        		break;
        	}
        } */
		$ext_flag = in_array($this->_fileExt, $this->_allowExtension); // 这句如上面一段代码
		if (!$ext_flag) {
			$this->setError('上传文件格式错误！只允许上传 ' . join('、', $this->_allowExtension) . ' 格式的文件');
			@unlink($this->_sourcePath); // 删除服务器上的临时文件,不删也可以，php会自动删除的
			return false;
		}
		if (($this->_allowFileSize)*1024*1024 < $this->_formerFileSize) {
			$this->setError('上传文件大小不得超过 ' . $this->_allowFileSize . ' Mb');
			@unlink($this->_sourcePath); // 删除服务器上的临时文件
			return false;
		}
		return true;
	}

}
