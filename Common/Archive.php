<?php

/**
 * 解压缩相关类，支持zip、tar、rar格式
 * rar文件解压需要安装rar扩展：http://pecl.php.net/get/rar
 * @author Bear[258333309@163.com] 
 * @version 1.0.0
 * @date 2019年3月12日
 */
class Common_Archive
{
    /**
     * 错误消息
     * @var string 
     */
    private $_errorMsg;
    /**
     * 错误代码
     * ERROR_NO_WRITE：文件或目录不可写或不存在
     * @var string
     */
    private $_errorCode;
    
    /**
     * 获取错误信息
     * @return string
     */
    public function getErrorMsg() {
        return $this->_errorMsg;
    }
    
    /**
     * 获取错误码
     * @return string
     */
    public function getErrorCode() {
        return $this->_errorCode;
    }
    
    /**
     * 压缩zip，支持文件和文件夹
     * @param string $filePath 需要压缩的文件或文件夹
     * @param string $zipPath 压缩后的文件路径
     * @return boolean true：压缩成功，false：压缩失败，可通过getErrorXXX方法获取错误信息或错误码
     */
    public function packZip($filePath, $zipPath) {
        $filePath = realpath($filePath);
        $zip = new \ZipArchive(); // 建立一个新的ZipArchive的对象
        $result = $zip->open($zipPath, \ZipArchive::CREATE);
        if ($result !== true) {
            $this->_errorCode = 'ERROR_NO_WRITE';
            $this->_errorMsg = '文件或目录不可写或不存在';
            return false;
        }
        $boolean = $this->_packZip($zip, $filePath);
        $zip->close();
        return $boolean;
    }
    
    /**
     * 递归添加文件入zip文件包中
     * @param \ZipArchive $zipArchive 
     * @param string $filePath 文件路径
     * @param string $rootPath 包中根路径，如果是多层目录后面需带“/”
     * @return boolean true：压缩成功，false：压缩失败，可通过getErrorXXX方法获取错误信息或错误码
     */
    private function _packZip(&$zipArchive, $filePath, $rootPath = '') {
        // 判断是目录还是文件
        if (is_dir($filePath)) {
            $directoryHandle = opendir($filePath);
            $directoryName = pathinfo($filePath, PATHINFO_BASENAME);
            while ($file = readdir($directoryHandle)) {
                if ($file != '.' && $file != '..') {
                    $fullPath = $filePath . '/' . $file;
                    $this->_packZip($zipArchive, $fullPath, $rootPath . $directoryName . '/');
                }
            }
        } else { // 文件
            $filename = basename($filePath);
            $boolean = $zipArchive->addFile($filePath, $rootPath . $filename);
            if (!$boolean) {
                $this->_errorCode = 'ERROR_NO_WRITE';
                $this->_errorMsg = '文件或目录不可写或不存在';
                return false;
            }
        }
        return true;
    }
    
    /**
     * 解压zip文件
     * @param string $filePath 需解压的文件路径
     * @param string $extractPath 解压到哪个目录
     * @return boolean
     */
    public function unpackZip($filePath, $extractPath) {
        $zip = new \ZipArchive();
        $result = $zip->open($filePath);
        if ($result === true) {
            $zip->extractTo($extractPath); // 解压到xxx文件夹
            $zip->close();
            return true;
        } else {
            $this->_errorCode = $result; // 这里的错误码是整形
            $this->_errorMsg = 'zip文件无法打开，请查看文件是否存在或文件是否为zip格式';
            return false;
        }
    }
    
    /**
     * 解压rar文件
     * rar的扩展需要装上才能用
     * @param string $filePath 需解压的文件路径
     * @param string $extractPath 解压到哪个目录
     * @return boolean
     */
    public function unpackRar($filePath, $extractPath) {
        $rarArchive = \RarArchive::open($filePath);
        if ($rarArchive === false) {
            $this->_errorCode = 'ERROR_NOT_OPEN';
            $this->_errorMsg = 'rar文件无法打开';
            return false;
        }
        foreach ($rarArchive as $rarEntry) {
//             $fileName = $rarEntry->getName();
            if ($rarEntry->isDirectory() === false) {
//                 $extractName = $extractPath . $fileName; // 不需要建立文件夹，解压时会自动建立
//                 $dir = pathinfo($extractName, PATHINFO_DIRNAME);
//                 mkdir($dir, 0777, true);
                $rarEntry->extract($extractPath);
            }
        }
        $rarArchive->close();
        return true;
    }
    
    /**
     * 解压.tar.gz文件
     * @param string $filePath 需要解压的文件路径
     * @param string $extractPath 解压到哪个目录
     * @param string|array $files 指定需要解压的文件或目录，null：全部解压
	 * The name of a file or directory to extract, or an array of files/directories to extract
	 * @param boolean $overwrite 如果存在文件是否覆盖，true：覆盖
     * @return boolean
     */
    public function unpackGz($filePath, $extractPath, $files = null, $overwrite = true) {
        $phar = new \PharData($filePath);
        $boolean = @mkdir($extractPath, 0777, true);
        if (!$boolean) {
            $this->_errorCode = 'ERROR_NO_WRITE';
            $this->_errorMsg = '文件或目录不可写或不存在';
            return false;
        }
        return $phar->extractTo($extractPath, $files, $overwrite);
    }

    /**
     * 打包目录为.tar.gz文件
     * @param string $buildDirectory 需打包的目录
     * @param string $filePath 打包后保存的文件路径
     * @return boolean
     */
    public function packGz($buildDirectory, $filePath) {
        $phar = new \PharData($filePath);
        $phar->buildFromDirectory($buildDirectory); // 这里已经把文件打包进去了，默认空文件夹不打包进去
        return true;
        // $phar->compress(\Phar::ZIP); // 不能转zip
        // $phar->compress(\Phar::BZ2); // 只能转bz2和gz
    }
    
    /**
     * 获取zip压缩文件内的所有文件列表（貌似没什么实际的用处）
     * @param string $zipFilePath zip文件的绝对路径
     * @return array 所有文件名列表（一维数组）
     */
    public function getFileListByZip($zipFilePath) {
        if (is_file($zipFilePath)) {
            $zipResource = zip_open($zipFilePath); // 打开压缩包
            $fileList = array();
            // 依次读取包中的文件
            while ($zip = zip_read($zipResource)) { // $zip is resource of type (Zip Entry) 
                if (zip_entry_open($zipResource, $zip)) { // 读取包中的文件
                    $fileName = zip_entry_name($zip); // 获取文件名
                    $fileName = substr($fileName, strrpos($fileName, '/')+1);
                    if ((!is_dir($fileName)) && $fileName) {
                        $fileList[] = $fileName;
                        // 文件已读出来，可以进行保存操作等
                        /* $file_size = zip_entry_filesize($zip);
                        $file = zip_entry_read($zip, $file_size); // 读取文件二进制数据
                        file_put_contents($save_path, $file);
                        zip_entry_close($zip); */
                    }
                }
            }
            zip_close($zipResource);
            return $fileList;
        } else {
            return array();
        }
    }
    
    
}
