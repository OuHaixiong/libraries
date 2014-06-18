<?php
/**
 * 2012-11-19 测试通过
 * 饼图统计类,可以以两种方式输出，一种是header输出，一种是生成图片，没有作相关扩展，请根据需求改draw方法代码
 * 具体用法:
 * $datLst    =    array(5, 3, 1, 3);    //数据
 * $labLst    =    array('A', 'B', 'C', 'D'); //标签
 * $clrLst    =    array(0x99ff00,0xffff66,0x0099ff,0x9900ff);//颜色
 * $pie = new Pie($datLst, $labLst, $clrLst);
 * $pie->draw();//输出饼图
 * 
 * $author lxylxy888666 --太阳-- QQ:445491197
 * 
 */
define ( "ANGLE_STEP", 5 ); // 定义画椭圆弧时的角度步长
class Common_Pic { //  Pie 
	
	/**
	 * 统计图生成路径
	 *
	 * @var array
	 */
	public $path;
	
	/**
	 * 数据
	 *
	 * @var array
	 */
	public $datLst;
	
	/**
	 * 标签
	 *
	 * @var array
	 */
	public $labLst;
	
	/**
	 * 颜色
	 *
	 * @var unknown_type
	 */
	public $clrLst;
	
	/**
	 * 统计图名字是否变化
	 *
	 * @var unknown_type
	 */
	public $imgNameRand;
	
	/**
	 * 输出方式：1.header输出 2.生成图片，请指明路径
	 */
	public $drawType;
	
	/**
	 * * 饼图生成参数
	 *
	 * @param array $datLst        	
	 * @param array $labLst        	
	 * @param array $clrLst        	
	 * @param string $path        	
	 * @param bool $imgNameRand        	
	 */
	public function __construct($datLst, $labLst, $clrLst, $path = null, $imgNameRand = null) {
		$this->datLst = $datLst;
		$this->labLst = $labLst;
		$this->clrLst = $clrLst;
		$this->imgNameRand = $imgNameRand;
		$this->path = $path;
	}
	
	/**
	 * 求$clr对应的暗色
	 *
	 * @param integer $img        	
	 * @param integer $clr        	
	 * @return array
	 */
	function draw_getdarkcolor($img, $clr) {
		$rgb = imagecolorsforindex ( $img, $clr );
		return array (
				$rgb ["red"] / 2,
				$rgb ["green"] / 2,
				$rgb ["blue"] / 2 
		);
	}
	
	/**
	 * 求角度$d对应的椭圆上的点坐标
	 *
	 * @param integer $a        	
	 * @param integer $b        	
	 * @param integer $d        	
	 * @return array
	 */
	function draw_getexy($a, $b, $d) {
		$d = deg2rad ( $d );
		return array (
				round ( $a * Cos ( $d ) ),
				round ( $b * Sin ( $d ) ) 
		);
	}
	
	/**
	 * 椭圆弧函数
	 *
	 * @param resource $img        	
	 * @param integer $ox        	
	 * @param integer $oy        	
	 * @param integer $a        	
	 * @param integer $b        	
	 * @param integer $sd        	
	 * @param integer $ed        	
	 * @param integer $clr        	
	 */
	function draw_arc($img, $ox, $oy, $a, $b, $sd, $ed, $clr) {
		$n = ceil ( ($ed - $sd) / ANGLE_STEP );
		$d = $sd;
		list ( $x0, $y0 ) = $this->draw_getexy ( $a, $b, $d );
		for($i = 0; $i < $n; $i ++) {
			$d = ($d + ANGLE_STEP) > $ed ? $ed : ($d + ANGLE_STEP);
			list ( $x, $y ) = $this->draw_getexy ( $a, $b, $d );
			imageline ( $img, $x0 + $ox, $y0 + $oy, $x + $ox, $y + $oy, $clr );
			$x0 = $x;
			$y0 = $y;
		}
	}
	
	/**
	 * 画扇面
	 *
	 * @param integer $img        	
	 * @param integer $ox        	
	 * @param integer $oy        	
	 * @param integer $a        	
	 * @param integer $b        	
	 * @param integer $sd        	
	 * @param integer $ed        	
	 * @param integer $clr        	
	 */
	function draw_sector($img, $ox, $oy, $a, $b, $sd, $ed, $clr) {
		$n = ceil ( ($ed - $sd) / ANGLE_STEP );
		$d = $sd;
		list ( $x0, $y0 ) = $this->draw_getexy ( $a, $b, $d );
		imageline ( $img, $x0 + $ox, $y0 + $oy, $ox, $oy, $clr );
		for($i = 0; $i < $n; $i ++) {
			$d = ($d + ANGLE_STEP) > $ed ? $ed : ($d + ANGLE_STEP);
			list ( $x, $y ) = $this->draw_getexy ( $a, $b, $d );
			imageline ( $img, $x0 + $ox, $y0 + $oy, $x + $ox, $y + $oy, $clr );
			$x0 = $x;
			$y0 = $y;
		}
		imageline ( $img, $x0 + $ox, $y0 + $oy, $ox, $oy, $clr );
		list ( $x, $y ) = $this->draw_getexy ( $a / 2, $b / 2, ($d + $sd) / 2 );
		imagefill ( $img, $x + $ox, $y + $oy, $clr );
	}
	
	/**
	 * 3d扇面
	 *
	 * @param integer $img        	
	 * @param integer $ox        	
	 * @param integer $oy        	
	 * @param integer $a        	
	 * @param integer $b        	
	 * @param integer $v        	
	 * @param integer $sd        	
	 * @param integer $ed        	
	 * @param integer $clr        	
	 */
	function draw_sector3d($img, $ox, $oy, $a, $b, $v, $sd, $ed, $clr) {
		$this->draw_sector ( $img, $ox, $oy, $a, $b, $sd, $ed, $clr );
		if ($sd < 180) {
			list ( $R, $G, $B ) = $this->draw_getdarkcolor ( $img, $clr );
			$clr = imagecolorallocate ( $img, $R, $G, $B );
			if ($ed > 180)
				$ed = 180;
			list ( $sx, $sy ) = $this->draw_getexy ( $a, $b, $sd );
			$sx += $ox;
			$sy += $oy;
			list ( $ex, $ey ) = $this->draw_getexy ( $a, $b, $ed );
			$ex += $ox;
			$ey += $oy;
			imageline ( $img, $sx, $sy, $sx, $sy + $v, $clr );
			imageline ( $img, $ex, $ey, $ex, $ey + $v, $clr );
			$this->draw_arc ( $img, $ox, $oy + $v, $a, $b, $sd, $ed, $clr );
			list ( $sx, $sy ) = $this->draw_getexy ( $a, $b, ($sd + $ed) / 2 );
			$sy += $oy + $v / 2;
			$sx += $ox;
			imagefill ( $img, $sx, $sy, $clr );
		}
	}
	
	/**
	 * RBG转索引色
	 *
	 * @param resource $img        	
	 * @param string $clr        	
	 * @return resource
	 */
	function draw_getindexcolor($img, $clr) {
		$R = ($clr >> 16) & 0xff;
		$G = ($clr >> 8) & 0xff;
		$B = ($clr) & 0xff;
		return imagecolorallocate ( $img, $R, $G, $B );
	}
	
	/**
	 * 绘图主函数，并输出图片
	 *
	 * @param integer $a        	
	 * @param integer $b        	
	 * @param integer $v        	
	 * @param integer $font        	
	 * @return resource string
	 */
	function draw($a = 250, $b = 120, $v = 20, $font = 10) {
		$datLst = $this->datLst;
		$labLst = $this->labLst;
		$clrLst = $this->clrLst;
		
		$ox = 5 + $a;
		$oy = 5 + $b;
		$fw = imagefontwidth ( $font );
		$fh = imagefontheight ( $font );
		
		$n = count ( $datLst ); // 数据项个数
		
		$w = 10 + $a * 2;
		$h = 10 + $b * 2 + $v + ($fh + 2) * $n;
		
		$img = imagecreate ( $w, $h );
		
		// 转RGB为索引色
		for($i = 0; $i < $n; $i ++)
			$clrLst [$i] = $this->draw_getindexcolor ( $img, $clrLst [$i] );
		
		$clrbk = imagecolorallocate ( $img, 0xff, 0xff, 0xff );
		$clrt = imagecolorallocate ( $img, 0x00, 0x00, 0x00 );
		
		// 填充背景色
		imagefill ( $img, 0, 0, $clrbk );
		
		// 求和
		$tot = 0;
		for($i = 0; $i < $n; $i ++)
			$tot += $datLst [$i];
		
		$sd = 0;
		$ed = 0;
		$ly = 10 + $b * 2 + $v;
		for($i = 0; $i < $n; $i ++) {
			$sd = $ed;
			$ed += $datLst [$i] / $tot * 360;
			
			// 画圆饼
			$this->draw_sector3d ( $img, $ox, $oy, $a, $b, $v, $sd, $ed, $clrLst [$i] ); // $sd,$ed,$clrLst[$i]);
			                                                                         
            // 画标签
            // imagefilledrectangle($img,
            // 5,
            // $ly,
            // 5+$fw,
            // $ly+$fh,
            // $clrLst[$i]);
            // imagerectangle($img,
            // 5,
            // $ly,
            // 5+$fw,
            // $ly+$fh,
            // $clrt);
            // imagestring($img,
            // $font,
            // 5+2*$fw,
            // $ly,
            // $labLst[$i].":".$datLst[$i]."(".(round(10000*($datLst[$i]/$tot))/100)."%)",
            // $clrt);
			
			$str = iconv ( "GB2312", "UTF-8", $labLst [$i] );
			// @ImageTTFText($img, $font, 0, 5+2*$fw, $ly+13, $clrt,
			// "./simsun.ttf",
			// $str.":".$datLst[$i]."(".(round(10000*($datLst[$i]/$tot))/100)."%)");
			$ly += $fh + 2;
		}
		
		// 直接header输出：输出方式一
		@header ( "Content-type: image/png" );
		imagepng ( $img );
		
		// 输出生成的图片:输出方式二
		/*
		 * if($this->imgNameRand == TRUE) { $imgName = time().'.png'; } else {
		 * $imgName = 'vote.png'; } $imgFileName = $this->path."temp/".$imgName;
		 * imagepng($img,$imgFileName); return $imgName;
		 */
	}
}