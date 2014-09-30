<?php

/**
 * Http请求
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
     * @param array $data 发送数据
     * @param string $method 请求方式: GET/POST
     * @param integer $timeout 链接超时秒数
     * @param string $refererUrl 请求来源地址
     * @param boolean $proxy 是否启用代理
     * @param string $contentType     application/x-www-form-urlencoded     multipart/form-data    application/json
     * @return boolean | mixed
     */
    public function sendRequest($url, $data = null, $method = 'GET', $timeout = 30, $refererUrl = '', $proxy = false)
    {
        $method = strtoupper($method);
        if (!in_array($method, array('GET', 'POST'))) {
        	return false;
        }
        if ('GET' === $method) {
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
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
            curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
        }
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
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
