<?php

/**
 * 通过curl发送Http请求
 * @author Bear
 * @version 1.0
 * @copyright http://maimengmei.com
 * @created 2014-08-14 20:22
 */
class Common_HttpClient
{

    /**
     * 通过curl发送HTTP请求
     *
     * @param string $url 请求地址
     * @param mixed $data 发送数据. 可以是数组(键值对)，也可以是字符串(通过url编码过的)
     * @param string $method 请求方式: GET/POST
     * @param array $httpHeader http请求头信息，如下：
	    array('Content-Type' => 'application/json') Content-Type可取值如application/x-www-form-urlencoded、multipart/form-data
     * @param array $cookie 请求cookie信息
     * @param string $refererUrl 请求来源网址
     * @param string $userAgent 用户浏览器信息（$_SERVER['HTTP_USER_AGENT']），如：Mozilla/5.0 (Windows NT 6.1; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0
     * @param boolean $proxy 是否启用代理           
     * @param integer $timeout 链接超时秒数
     * @return boolean | mixed 请求方式出错时返回false；
     */
    public static function sendRequest($url, $data = null, $method = 'GET', $httpHeader = array(), $cookie = array(), $refererUrl = '', $userAgent = '', $proxy = false, $timeout = 30) {
        $url = trim($url);
        $method = strtoupper($method);
        if (!in_array($method, array('GET', 'POST'))) {
            return false;
        }
        if ('GET' === $method) { // 如果是get请求，并且需要发送数据，就把数据拼接在url后面
            if (!empty($data)) {
                if (is_string($data)) {
                    $url .= (strpos($url, '?') === false ? '?' : '') . $data;
                } else {
                    $url .= (strpos($url, '?') === false ? '?' : '') . http_build_query($data);
                }
            }
        }//var_dump($url);exit;
        $ch = curl_init($url); // curl_setopt($ch, CURLOPT_URL, $url); // 设置请求（抓取）url
        curl_setopt($ch, CURLOPT_HEADER, 0); // 不返回header部分（设置头文件的信息作为数据流输出）
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); // 当根据Location:重定向时，自动设置header中的Referer:信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
        //		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        $isFormData = false; // Content-Type: multipart/form-data
        $isJson = false; // Content-Type: application/json
        if (!empty($httpHeader)) { // 请求头信息
            $headerData = array();
            foreach ($httpHeader as $k=>$v) {
                if ((strtolower($k) == 'content-type') && ($v == 'application/json')) {
                    $isJson = true;
                }
                if ((strtolower($k) == 'content-type') && ($v == 'multipart/form-data')) {
                    $isFormData = true;
                }
                $headerData[] = $k . ':' . $v; // array('Content-Type:application/json')
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData); // 声明请求头信息
            //            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        }
        if ($userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent); // 模拟用户使用的浏览器代理
        }
        // 判断是否https请求
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ('https' === $scheme) { // 是https请求
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 规避对认证证书来源的检查(不验证证书)
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在。这里是跳过host验证
        }
        if ((is_array($cookie)) && (!empty($cookie))) { // 请求cookie值
            $cookieData = array();
            foreach ($cookie as $k=>$v) {
                $cookieData[] = $k . '=' . $v;
            }
            $cookieData = implode(';', $cookieData);
            curl_setopt($ch, CURLOPT_COOKIE, $cookieData);
        }
        if ('POST' === $method) { // post请求
            curl_setopt($ch, CURLOPT_POST, 1); // curl_setopt($ch, CURLOPT_POST, true);
            //             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');  // 貌似这个写法和上面是一样的（待验证）
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            if (!empty($data)) {
                if (is_string($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    if ($isJson) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    } else if ($isFormData) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // 数组对应multipart/form-data，也是默认的
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // http_build_query对应application/x-www-form-urlencoded
                    }
                }
            }
        } else {
            //             curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            //             curl_setopt($ch, CURLOPT_POST, false);
        }
        if ($refererUrl) {
            curl_setopt($ch, CURLOPT_REFERER, $refererUrl); // 来源网址
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        $response = curl_exec($ch); // 执行命令（请求）
        //         $info = curl_getinfo($ch);
        // var_dump($response);var_dump(curl_error($ch));var_dump($info);exit;
        curl_close($ch); // 关闭请求
        return $response;
    }
    
}
