<?php
require_once dirname(__FILE__) . "/inc.php";
require_once dirname(__FILE__) . "/sqlserver.class.php";
  /**
  * 结束脚本以XML格式
  *
  * @param string $code
  * @param string $msg
  * @param [type] $data
  * @return void
  */
  function die_xml($code = "Fail", $msg = false, $data = null){
    $arr = [
        "return_code" => $code
    ];
 
    if($msg){
        
        $arr['return_msg'] = $msg;
 
    }
 
    if($data){
        
        $arr['data'] = $data;
 
    }
 
    header("Server-Type:rxjh");
 
    header("Author:QQ:778716166");
 
    header("Content-Type:application/xml;charset=UTF-8");
 
    die(arr2xml($arr));
 
 }
 function arr2xml($arr, $root = 'xml')
 {
 
    $xml = '';
 
    foreach($arr as $key => $val){
 
        if(is_array($val)){
 
            $xml .= str_repeat("\t", 1) . arr2xml_ex($val, 1);
 
        }else{
 
            $xml .= str_repeat("\t", 1) . "<" . (is_numeric($key) ? 'num_' : '') . $key . ">" . xml_replace($val) . "</" . (is_numeric($key) ? 'num_' : '') . $key . ">" . PHP_EOL;
 
        }
        
    }
 
    $xml = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL . '<' . $root . '>' . PHP_EOL . $xml . '</' . $root . '>';
 
    return $xml;
 
 }
 
 function arr2xml_ex($arr, $lev=0)
 {
 
    $xml = '';
 
    foreach($arr as $key => $val){
 
        if(is_array($val)){
 
            $xml .= str_repeat("\t", $lev) . "<" . (is_numeric($key) ? 'num_' : '') . $key . ">" . PHP_EOL . arr2xml_ex($val, $lev + 1) . str_repeat("\t", $lev) . "</" . (is_numeric($key) ? 'num_' : '') . $key . ">" . PHP_EOL;
 
        }else{
 
            $xml .= str_repeat("\t", $lev) . "<" . (is_numeric($key) ? 'num_' : '') . $key . ">" . xml_replace($val) . "</" . (is_numeric($key) ? 'num_' : '') . $key . ">" . PHP_EOL;
 
        }
        
    }
 
    return $xml;
 
 }
 
 function xml_replace($val)
 {
 
    $val = str_ireplace("<", "&lt;", $val);
 
    $val = str_ireplace(">", "&gt;", $val);
 
    $val = str_ireplace('"', "&quto;", $val);
 
    return $val;
 
 }

function post($data, $url) {

    $ch = curl_init();      // 初始化CURL
 
    curl_setopt($ch, CURLOPT_URL, $url);    // 设置URL
 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 
    curl_setopt($ch, CURLOPT_POST, 1);  // 如果你想PHP去做一个正规的HTTP POST，设置这个选项为一个非零值。这个POST是普通的 application/x-www-from-urlencoded 类型，多数被HTML表单使用。
 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    // 传递一个作为HTTP "POST"操作的所有数据的字符串。
 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
 
    $rst = curl_exec($ch);
 
    curl_close($ch);
 
    return $rst;
 }

 class AES{
    public static function encrypt($str,$key)
    {
        $data = openssl_encrypt($str, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = strtolower(bin2hex($data));
        return $data;
    }
 }