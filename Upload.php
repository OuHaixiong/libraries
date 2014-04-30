<?php

/**
 * @desc 文件、图片上传类
 * @author bear
 * @copyright xiqiyanyan.com
 * @version 1.0.0 2011-12-15 10:32
 * @created 2011-9-5
 */
class Upload
{
	private $FormerFileName; // 完整的原文件名
	private $FormerFileSize; // 原文件大小 ，b 为单位
	private $FileExt;        // 文件后缀名；包括点在内，如 ： .jpg
	private $SourcePath;     // 文件来源路径
	private $PurposePath;    // 文件目标路径(文件上传保存路径)
	private $Extension = array('.jpg', '.jpeg', '.png', '.gif', '.bmp', '.pdf', '.rar', '.xls', '.xlsx', '.doc', '.docx'); // 允许(allowed)上传的文件格式
	private $FileSize = 2;   // 允许上传的文件大小，默认不得超过 2 Mb, max size of file
	private $Error;          // 上传错误信息 
	private $Flag;           // TODO 是否固定文件名
	private $FileName;       // TODO 自定义文件名
//	private $overwrite;  //TODO overwrite  是否覆盖同名文件 $overwrite = 0;//是否允许覆盖相同文件,1:允许,0:不允许  '对不起,文件 ".$f_name." 已经存在,上传失败!'
//	private $Flagsourname     // TODO 是否保留其原来的文件名
//	private $MaxFiles;       // TODO 一次性最多上传多少文件
//	private $FilesNameArray = array(); // TODO 返回多个文件名，貌似没有什么用
//	private $ImageHeight;     // TODO 图片文件高度，超过就生成缩略图
//	private $ImageWidth;     // TODO 图片文件宽度，超过就生成缩略图
//	private $filesname;      // TODO 文件表单name

	public function __construct() {
        
	}

	/**
	 * 传入参数
	 * @param string $sourcePath     源文件路径
	 * @param string $purposePath    目标文件路径
	 * @param string $formerFileName 完整的原文件名
	 * @param int $formerFileSize    原文件大小,单位byte
	 * @param int $fileSize          允许上传文件大小，M 为单位，默认不得超过 2 Mb
	 * @param array $extension       允许上传的文件格式；如：array('.jpg', '.jpeg', '.png', '.gif', '.bmp'...）
	 */
	public function setParam($sourcePath, $purposePath, $formerFileName, $formerFileSize, $fileSize = null, $extension = null) {
		$this->SourcePath = $sourcePath;
		$this->PurposePath = $purposePath;
		$this->FormerFileName = $formerFileName;
		$this->FileExt = strtolower(strrchr($formerFileName, '.'));
		$this->FormerFileSize = $formerFileSize;
		if (!empty($fileSize)) {
			$this->FileSize = $fileSize;
		}
		if (!empty($extension)) {
			$this->Extension = $extension;
		}
//		$this->FileName = $fileName;
	}

	/**
	 * 文件上传
	 * @return false | $filename 上传后的文件名
	 */
	public function uploadFile() {
		if (!$this->validator()) {
			return false;
		}
//		$fileName = $this->randABC() . time() . $this->FileExt; // microtime(true) ;  time() 返回时间戳；  microtime(true) 返回包括毫秒数的时间戳，如：1326700356.0938
        $fileName = $this->randABC() . date('YmdHis') . $this->randAbcAndNumber() . mt_rand() . $this->FileExt;
        if (!file_exists($this->PurposePath)) {
        	if (!@mkdir($this->PurposePath)) { // TODO 完整路径的创建
        		$this->setError('创建目录（或文件夹）失败！请确认路径是否正确，或文件夹路径是否可读写');
        		return false;
        	}
        }
        // 	TODO  如果文件夹不存在，可以无条件创建之，除非没有权限
//      $upload =new Zend_File_Transfer_Adapter_Http();
//		$result = new Zend_Search_Lucene_Storage_Directory_Filesystem($this->PurposePath); //没有该文件夹则创建目录
/*        $upload->setDestination($this->PurposePath);
        $upload->addFilter('Rename',array('source' => $this->SourcePath, 'target' => $this->PurposePath . $fileName, 'overwrite' => true));//重新命名      
        $upload->receive(); 貌似多个文件调用时有问题，最后别用他的
        //      $file_name = $adapter->getFileName(null, false); //获取上传文件名*/ 
        $lastString = substr($this->PurposePath, -1);
        if ($lastString!='/' or $lastString!='\\') {
        	$this->PurposePath .= '/';
        }
		move_uploaded_file($this->SourcePath, $this->PurposePath . $fileName); //  bool move_uploaded_file ( string $filename , string $destination ) 将上传的文件移动到新位置
		return $fileName;	
//     $adapter->addValidator ( 'Extension', false, 'jpg,png,gif' )//设置上传格式
//         ->addValidator( 'Size', false, 1048576*2 )//大小addValidator('FilesSize',false,array('min' => '1B', 'max'=>'30MB'));
//         ->addValidator ( 'Count', false, array('min' => 1, 'max' => 3) )//上传文件数量;
	}
	
	/**
	 * 验证上传的文件格式和大小
	 * @return boolean
	 */
	private function validator() {
		if (!is_uploaded_file($this->SourcePath)) { // bool is_uploaded_file(string $filename) 判断文件是否是通过 HTTP POST 上传的
			$this->setError('非法文件，不是上传上来的文件');
			return false;
		}
//		$extFlag = false;
//		foreach ($this->Extension as $ext) {
//			if ($ext == $this->FileExt) {
//				$extFlag = true;
//				break;
//			}
//		}
		$extFlag = in_array($this->FileExt,$this->Extension); // 这句如上面一段代码
		if (!$extFlag) {
			$this->setError('上传文件格式错误！');
			@unlink($this->SourcePath); // 删除服务器上的临时文件,不删也可以，php会自动删除的
			return false;
		}
		if (($this->FileSize)*1024*1024 < $this->FormerFileSize) {
			$this->setError('上传文件大小不得超过 ' . $this->FileSize . ' Mb');
			@unlink($this->SourcePath); // 删除服务器上的临时文件
			return false;
		}
		return true;
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
		$this->Error = $error;
	}
	
	/**
	 * 获取上传错误信息
	 * @return string
	 */
	public function getError() {
		return $this->Error;
	}
	
	/**
	 * 设置文件上传类型，也可以在 setParam 中设置
	 * @param array $extension
	 */
	public function setExtension($extension) {
		$this->Extension = $extension;
	}  
	
	/**
	 * 获取文件上传类型
	 * @return array
	 */
	public function getExtension() {
		return $this->Extension;
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
		
}
