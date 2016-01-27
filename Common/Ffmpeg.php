<?php

/**
 * 扩展ffmpeg-php，对视频、音频进行处理
 * mencode 貌似也是处理视频的
 * @author bear
 * @copyright http://maimengmei.com
 * @version 1.0.0
 * @created 2013-03-04 09:04
 * 
 * ffmpeg-php （php的扩展ffmpeg）提供了访问ffmpeg的接口（api）；其中包括几大类：ffmpeg_movie、ffmpeg_frame
 * 
 * ffmpeg_movie的方法：
 * array(32) {
  [0] =>  "__construct"
  [1] =>  "getduration"
  [2] =>  "getframecount"
  [3] =>  "getframerate"
  [4] =>  "getfilename"
  [5] =>  "getcomment" // 貌似有时获取不到
  [6] =>  "gettitle" // 貌似有时获取不到
  [7] =>  "getauthor" // 貌似有时获取不到
  [8] =>  "getartist" // 貌似有时获取不到
  [9] =>  "getcopyright" // 貌似有时获取不到
  [10] => "getalbum"
  [11] => "getgenre" // 貌似有时获取不到
  [12] => "getyear" // 貌似有时获取不到
  [13] => "gettracknumber" // 貌似有时获取不到
  [14] => "getframewidth"
  [15] => "getframeheight"
  [16] => "getframenumber"
  [17] => "getpixelformat"
  [18] => "getbitrate"
  [19] => "hasaudio"
  [20] => "hasvideo"
  [21] => "getnextkeyframe"
  [22] => "getframe"
  [23] => "getvideocodec"
  [24] => "getaudiocodec"
  [25] => "getvideostreamid"
  [26] => "getaudiostreamid"
  [27] => "getaudiochannels"
  [28] => "getaudiosamplerate"
  [29] => "getaudiobitrate"
  [30] => "getvideobitrate"
  [31] => "getpixelaspectratio"
}
 *
 * ffmpeg_frame的方法：
 * array(6) {
  [0] => string(9) "togdimage"
  [1] => string(8) "getwidth" 获取宽
  [2] => string(9) "getheight" 获取高
  [3] => string(10) "iskeyframe"
  [4] => string(24) "getpresentationtimestamp"
  [5] => string(6) "getpts"
}
 */
class Common_Ffmpeg
{
    private $_error;
    private $_allowType = array('video/mp4', 'audio/mpeg', 'video/x-flv', 'application/octet-stream');
    // application/octet-stream avi返回的类型

    /**
     * 检测文件类型
     * @param string $fileName
     * @return boolean
     */
    public function check($fileName) {
        //$type = mime_content_type($fileName); // 获取文件类型(貌似5.3后废弃)
        //$finfo = new finfo(FILEINFO_MIME_TYPE);
        //$type = $finfo->file($fileName);
        $type = strtolower(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fileName));
        //var_dump($type);exit;
        if (in_array($type, $this->_allowType)) {
            return true;
        } else {
            $this->setError('不支持的文件类型');
            return false;
        }
    }
    
    /**
     * 获取第几帧的图像资源
     * @param string $file 视频文件
     * @param integer $num 要获取的帧数，必须大于0（Frame number must be greater than zero） 
     * @return false | resource 成功返回gd资源，失败返回false
     */
    public function getFrameImg($file, $num = 1) {
        if ($num < 1) {
            $this->setError('Frame number must be greater than zero');
        	return false;
        }
        $ffmpeg = new ffmpeg_movie($file);
        $boolean = $ffmpeg->hasVideo();
        if (!$boolean) {
            $this->setError('不是有效的视频文件');
        	return false;
        }
        $frame = $ffmpeg->getFrame($num);
        if ($frame instanceof ffmpeg_frame) {
        	/* var_dump($ffmpeg->getFrameNumber());exit; // 这里返回是$num
        	var_dump($frame->getWidth()); 
        	var_dump($frame->getHeight());
            var_dump($frame->getpts());exit; // float(0.002) 
            var_dump($frame->iskeyframe());exit; // int(0) 
            var_dump($frame->getpresentationtimestamp());exit; // float(0.002)  */
            $gd = $frame->togdimage();
            if (is_resource($gd)) {
            	return $gd;
            } else {
                $this->setError('无法转为图片');
            	return false;
            }
        } else {
            $this->setError('获取帧出错');
        	return false;
        }
    }
    
    /**
     * 将gd信息写入图片文件
     * @param resource $gd gd图片资源
     * @param string $imgPath 要生成的图片文件路径，包含完整的路径和文件名(不做文件夹和文件可读写检查)
     * @return boolean
     */
    public function toImageFile($gd, $imgPath) {
        $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION));
        switch ($ext) {
        	case 'png'  : return imagepng($gd, $imgPath); break;
        	case 'gif'  : return imagegif($gd, $imgPath); break;
        	case 'jpg'  :
        	case 'jpeg' : return imagejpeg($gd, $imgPath); break;
        	default    : $this->setError('不支持的文件格式'); return false; break;
        }
    }
    
    /**
     * 命令行方式转换视频
     * @param string $inputFile 输入视频完整绝对路径
     * @param string $outputFile 输出视频完整绝对路径
     * @param array $size 目标视频的宽和高，如果保持源宽高，传入null或array()
     * @param integer $audioBit 音频比特率，默认96，如保持源，传入null即可
     * @param integer $audioRate 音频采样率,音频采样频率一般共分为22.05KHz、44.1KHz、48KHz三个等级
     * @param integer $videoBit 视频比特率，默认700，比特率越高，传送的数据越大，视频质量越好，就如同音频比特率。如果转成flv设800为宜
     * @param float $fps 帧频，缺省25(越大越流畅),确认非标准桢率会导致音画不同步，所以只能设定为15或者29.97
     * @param boolean $isCover 是否覆盖已存在的文件，默认true：覆盖，false：不覆盖
     * @return boolean true:处理成功，false：处理失败
     */
    public function execVideoConvert($inputFile, $outputFile, $size=array(), $audioBit=96, $audioRate=44100, $videoBit=700, $fps=29.97, $isCover=true) {
        set_time_limit(0);
        $command = 'ffmpeg -i ' . $inputFile;
        if ($audioBit) {
        	$command .= ' -ab ' . $audioBit;
        }
        if ($audioRate) {
        	$command .= ' -ar ' . $audioRate;
        }
        if ($videoBit) {
        	$command .= ' -b ' . $videoBit . 'k'; //一定要加k，不然就会按其原来的，
        }
        if ($fps) {
        	$command .= ' -r ' . $fps;
        }
        if (!empty($size)) {
        	$command .= ' -s ' . $size['width'] . 'X' . $size['height'];
        }
        $isExist = is_file($outputFile);
        if (!$isCover) {
        	if ($isExist) {
        	    $this->setError('已存在该文件');
        		return false;
        	}
        }
        if ($isExist) {
        	if (!is_writeable($outputFile)) {
        	    $this->setError('文件已存在，且不可写');
        		return false;
        	}
        }
        $command .= ' -y ';
        $command .= ' ' . $outputFile;
        //var_dump($command);exit;
    	exec($command, $output, $status);
    	if ($status) {
    	    $this->setError('执行转换命令失败');
    		return false;
    	} else {
    		return true;
    	}
    }

    /**
     * 命令行方式截取缩略图
     * @param string $inputFile
     * @param string $outputFile
     * @param integer $second 单位秒，第几秒的缩略图
     * @param array $size 生成图片的尺寸，非等比例；设了宽一定要设高
     * @return boolean
     */
    public function execVideoImg($inputFile, $outputFile, $second = 0, $size = array()) {
        set_time_limit(0);
        $command = 'ffmpeg -i ' . $inputFile . ' -ss ' . $second;
        if (!empty($size)) {
        	$command .= ' -s ' . $size['width'] . 'X' . $size['height'];
        }
        $command .= ' -y -f image2 -t 0.001';
        $command .= ' ' . $outputFile;
        //var_dump($command);exit;
        exec($command, $output, $status);
        if ($status) {
        	$this->setError('执行命令失败');
        	return false;
        } else {
        	return true;
        }
    }
    
    /**
     * 命令行方式转换音频
     * (貌似有些参数没有配对，转出来播放不了)
     * @param string $inputFile 输入视频完整绝对路径
     * @param string $outputFile 输出视频完整绝对路径
     * @param integer $audioBit 音频比特率，默认96，如保持源，传入null即可
     * @param integer $audioRate 音频采样率,音频采样频率一般共分为22.05KHz、44.1KHz、48KHz三个等级
     * @param boolean $isCover 是否覆盖已存在的文件，默认true：覆盖，false：不覆盖
     * @return boolean true:处理成功，false：处理失败
     */
    public function execAudioConvert($inputFile, $outputFile, $audioBit=96, $audioRate=44100, $isCover=true) {
        set_time_limit(0);
        $command = 'ffmpeg -i ' . $inputFile;
        if ($audioBit) {
            $command .= ' -ab ' . $audioBit;
        }
        if ($audioRate) {
            $command .= ' -ar ' . $audioRate;
        }
        $isExist = is_file($outputFile);
        if (!$isCover) {
            if ($isExist) {
                $this->setError('已存在该文件');
                return false;
            }
        }
        if ($isExist) {
            if (!is_writeable($outputFile)) {
                $this->setError('文件已存在，且不可写');
                return false;
            }
        }
        $command .= ' -y ';
        $command .= ' ' . $outputFile;
//         var_dump($command);exit;
        exec($command, $output, $status);
        if ($status) {
            $this->setError('执行转换命令失败');
            return false;
        } else {
            return true;
        }
    }
   
    /**
     * 获取错误信息
     * @return string
     */
    public function getError() {
    	return $this->_error;
    }
    
    /**
     * 设置错误信息
     * @param string $error
     */
    public function setError($error) {
    	$this->_error = $error;
    }
    
    

    
  
    
    
    
    
    
   

  
    /**
     * 获得视频的数字时间(使用纯php获取)；只支持flv格式，其他的无法获取
     * @param string $name
     * @return void|number
     */
    public function getFlvTime ($name) {
        if (! file_exists($name)) {
            return;
        }
        $flv_data_length = filesize($name);
        $fp = @fopen($name, 'rb');
        $flv_header = fread($fp, 5);
        fseek($fp, 5, SEEK_SET);
        $frame_size_data_length = $this->BigEndian2Int(fread($fp, 4));
        $flv_header_frame_length = 9;
        if ($frame_size_data_length > $flv_header_frame_length) {
            fseek($fp, $frame_size_data_length - $flv_header_frame_length, 
                    SEEK_CUR);
        }
        $duration = 0;
        while ((ftell($fp) + 1) < $flv_data_length) {
            $this_tag_header = fread($fp, 16);
            $data_length = $this->BigEndian2Int(substr($this_tag_header, 5, 3));
            $timestamp = $this->BigEndian2Int(substr($this_tag_header, 8, 3));
            $next_offset = ftell($fp) - 1 + $data_length;
            if ($timestamp > $duration) {
                $duration = $timestamp;
            }
            
            fseek($fp, $next_offset, SEEK_SET);
        }
        
        fclose($fp);
        return $duration;
    }
    
    /**
     * 大字节转整型
     * @param unknown_type $byte_word
     * @param unknown_type $signed
     * @return number
     */
    private function BigEndian2Int ($byte_word, $signed = false) {
        $int_value = 0;
        $byte_wordlen = strlen($byte_word);
    
        for ($i = 0; $i < $byte_wordlen; $i ++) {
            $int_value += ord($byte_word{$i}) * pow(256, ($byte_wordlen - 1 - $i));
        }
    
        if ($signed) {
            $sign_mask_bit = 0x80 << (8 * ($byte_wordlen - 1));
            if ($int_value & $sign_mask_bit) {
                $int_value = 0 - ($int_value & ($sign_mask_bit - 1));
            }
        }
    
        return $int_value;
    }
     
    /**
     * 转化为00：03：56的时间格式
     * @param integer $second 总秒数
     * @return string
     */
    public function toHourMinuteSecond($second) {
        $sec = intval($second / 1000);
        $h = intval($sec / 3600);
        $m = intval(($sec % 3600) / 60);
        $s = intval(($sec % 60));
        $tm = $h . ':' . $m . ':' . $s;
        return $tm;
    }
    
}
