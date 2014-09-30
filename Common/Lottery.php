<?php

/**
 * 抽奖算法
 * @author Bear
 * @copyright http://maimengmei.com
 * @version 1.0.0
 * @created 2014-06-27 14:04
 */
class Common_Lottery
{

    /**
     * 根据概率获取中奖号码, 一定会获取到对应的等级
     * @param array $probability 等级对应的概率，如：array('s'=>10, 'a'=>20, 'b'=>30, 'c'=>40)
     * @return string | integer 返回对应的等级
     */
    public static function randOne($probability) {
        $result = '';
        $sum = array_sum($probability); // 概率数组的总概率精度
        foreach ($probability as $key=>$value) { // 概率数组循环
            $randNum = mt_rand(1, $sum);
            if ($randNum <= $value) {
                $result = $key;
                break;
            } else {
                $sum -= $value;
            }
        }
        unset($probability);
        return $result;
    }
    
    public function f() {}
    
    

}
