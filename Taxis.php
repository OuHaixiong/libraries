<?php

/**
 * @desc 一些排序算法
 * @author Bear
 * @copyright xiqiyanyan.com
 * @version 1.0.0 2012-06-26 14：03
 * @created 2011-9-22 15:53
 */
class Taxis
{
	/**
	 * 冒泡、起泡排序  
	 * 冒泡排序的基本思想是：两两比较待排序记录的关键字，发现两个记录的次序相反时即进行交换，直到没有反序的记录为止。
	 * @param array $date 要排序的数组 (一维数组)
	 * @param integer $num  1或0 1代表从大到小，0代表升序
	 * @return array 排好序的数组
	 */
	public static function bubbleSort(array $array, $num = 0) {
		$count = count($array);
		if ($count <= 0) {
			return array();
		}
        if ($num == 1) {
        	for($i=0;$i<$count-1; ++$i)
			{
				for($j=0;$j<$count-$i-1; ++$j)
				{
					if($array[$j] < $array[$j+1])
					{
						$temp        = $array[$j];
		             	$array[$j]   = $array[$j+1];
						$array[$j+1] = $temp;
					}
				}
		    }
        } else {
            for($i=0;$i<$count-1; ++$i)
			{
				for($j=0;$j<$count-$i-1; ++$j)
				{
					if($array[$j] > $array[$j+1])
					{
						$temp        = $array[$j];
		             	$array[$j]   = $array[$j+1];
						$array[$j+1] = $temp;
					}
				}
		    }
        }
		return $array;
	}
	
	/**
	 * 插入排序（Insertion Sort）; 暂时只支持升序排序
	 * 每次将一个待排序的记录，按其关键字大小插入到前面已经排好序的子文件中的适当位置，直到全部记录插入完成为止
	 * @param array $array 一维数组
	 * @return array
	 */
	public static function insertSort(array $array) {
		$count = count ( $array );
		if ($count <= 0) {
			return array ();
		}
		for($i = 1; $i < $count; $i ++) {
			$tmp = $array [$i];
			$j = $i - 1;
			while ( $array [$j] > $tmp ) {
				$array [$j + 1] = $array [$j];
				$array [$j] = $tmp;
				$j --;
			}
		}
		return $array;
	}
	
	/**
	 * 选择排序（Selection Sort）
	 * 每一趟从待排序的记录中选出关键字最小的记录，顺序放在已排好序的子文件的最后，直到全部记录排序完毕。
	 * @param array $arr
	 * @return array
	 */
	public static function selectSort(array $arr) {
		$count = count ( $arr );
		for($i = 0; $i < $count; $i ++) {
			$k = $i;
			for($j = $i + 1; $j < $count; $j ++) {
				if ($arr [$k] > $arr [$j])
					$k = $j;
				if ($k != $i) {
					$tmp = $arr [$i];
					$arr [$i] = $arr [$k];
					$arr [$k] = $tmp;
				}
			}
		}
		return $arr;
	}
	
	/**
	 * 快速排序
	 * 实质上和冒泡排序一样，都是属于交换排序的一种应用。所以基本思想和上面的冒泡排序是一样的。
	 * @param array $array
	 * @return array
	 */
	public static function quickSort(array $array) {
		if (count ( $array ) <= 1)
			return $array;
		
		$key = $array [0];
		$left_arr = array ();
		$right_arr = array ();
		for($i = 1; $i < count ( $array ); $i ++) {
			if ($array [$i] <= $key)
				$left_arr [] = $array [$i];
			else
				$right_arr [] = $array [$i];
		}
		$left_arr = self::quickSort ( $left_arr );
		$right_arr = self::quickSort ( $right_arr );
		
		return array_merge ( $left_arr, array($key), $right_arr );
	}
	
}
