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
     * @param array $httpHeader http请求头信息
     * @param array $cookie 请求cookie信息
     * @param string $refererUrl 请求来源网址
     * @param string $userAgent 用户浏览器信息，如：Mozilla/5.0 (Windows NT 6.1; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0
     * @param boolean $proxy 是否启用代理
     * @param string $contentType     application/x-www-form-urlencoded     multipart/form-data    application/json
     * @param integer $timeout 链接超时秒数
     * @return boolean | mixed 请求方式出错时返回false；
     */
    public static function sendRequest($url, $data = null, $method = 'GET', $httpHeader = array(), $cookie = array(), $refererUrl = '', $userAgent = '', $proxy = false, $timeout = 30) {
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
        }
        $ch = curl_init($url); // curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); // 当根据Location:重定向时，自动设置header中的Referer:信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if (!empty($httpHeader)) { // 请求头信息
            $headerData = array();
            foreach ($httpHeader as $k=>$v) {
                $headerData[] = $k . ':' . $v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        }
        if ($userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        }
        if (!empty($cookie)) { // 请求cookie值
            $cookieData = array();
            foreach ($cookie as $k=>$v) {
                $cookieData[] = $k . '=' . $v;
            }
            $cookieData = implode(';', $cookieData);
            curl_setopt($ch, CURLOPT_COOKIE, $cookieData);
        }
        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, 1); // curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            if (!empty($data)) {
                if (is_string($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // http_build_query对应application/x-www-form-urlencoded
//                     curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // 数组对应multipart/form-data，也是默认的
//                     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }
        }
        if ($refererUrl) {
            curl_setopt($ch, CURLOPT_REFERER, $refererUrl); // 来源网址
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        $response = curl_exec($ch);
//         $info = curl_getinfo($ch);
//         $httpInfo = array(
//                 'sendData' => $data,
//                 'url' => $url,
//                 'response' => $response,
//                 'info' => $info
//         );Common_Tool::prePrint($httpInfo);
        curl_close($ch);
        return $response;
    }
    
}
