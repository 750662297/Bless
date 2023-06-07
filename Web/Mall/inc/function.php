<?php
/**
 * 是否是POST提交
 * @return int
 */
function isPost() {
    return ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST']))) ? 1 : 0;
}
/**
 * 获取真实IP地址
 *
 * @return void
 */
function get_real_ip(){
    static $realip = NULL;
    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip) {
                $ip = trim($ip);
        
                if ($ip != 'unknown') {
                    $realip = $ip;
        
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    // 使用正则验证IP地址的有效性，防止伪造IP地址进行SQL注入攻击
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}
/**
 * 数组加密，需要依赖discuz.authcode.php文件
 *
 * @param [type] $arr
 * @return void
 */
function encry($arr){
    return authcode(base64_encode(json_encode($arr)),"ENCODE", WEB_SIGN_KEY);
}

/**
 * 数组解密，需要依赖discuz.authcode.php文件
 *
 * @param [type] $str 要解密的字符串
 * @return array 返回解密后的数组
 */
function decry($str){
    return json_decode(base64_decode(authcode($str,"DECODE", WEB_SIGN_KEY)), true);
}
/**
 * 转换成 年 天 时 分 秒
 *
 * @param [type] $time
 * @return void
 */
function Sec2Time($time)
{
    if (is_numeric($time)) {
        $value = array(
            "years" => 0, "days" => 0, "hours" => 0,
            "minutes" => 0, "seconds" => 0,
        );
        $t = '';
        if ($time >= 31556926) {
            $value["years"] = floor($time / 31556926);
            $time = ($time % 31556926);
            $t .= $value["years"] . "年";
        }
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time = ($time % 86400);
            $t .= $value["days"] . "天";
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time = ($time % 3600);
            $t .= $value["hours"] . "时";
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time = ($time % 60);
            $t .= $value["minutes"] . "分";
        }
        $value["seconds"] = floor($time);
        //return (array) $value;
        $t .= $value["seconds"] . "秒";
        return $t;

    } else {
        return (bool) false;
    }
}
function die_json($code = '', $msg = '', $data = null){
    $arr = array(
        "return_code" => $code,
        "return_msg"  => $msg
    );
    if($data){
        $arr['data'] = $data;
    }
    die(json_encode($arr));
}
/**
 * 用户是否登录
 *
 * @return void
 */
function UserIsLogin() 
{
    if($_SESSION['USER_LOGIN_USERNAME'] != '' && $_SESSION['USER_LOGIN_STEP'] == 2 && $_SESSION['USER_LOGIN_CHAR_ID'] && time() - $_SESSION['USER_LOGIN_TIME'] < 86400){
        return true;
    }
    UserLogout();
}
function GetLoginUserName()
{
    return $_SESSION['USER_LOGIN_USERNAME'];
}
function GetLoginCharId()
{
    return $_SESSION['USER_LOGIN_CHAR_ID'];
}
function GetLoginCharName()
{
    return  $_SESSION['USER_LOGIN_CHAR_NAME'];
}
/**
 * 设置用户登录
 *
 * @param [type] $username
 * @return void
 */
function SetUserLogin($username, $user_usn, $step, $char = null, $char_name = null)
{
    $_SESSION['USER_LOGIN_USERNAME'] = $username;
    $_SESSION['USER_LOGIN_USER_USN'] = $user_usn;
    $_SESSION['USER_LOGIN_TIME'] = time();
    $_SESSION['USER_LOGIN_STEP'] = $step;   // 设为刚登录
    $_SESSION['USER_LOGIN_CHAR_ID'] = $char;
    $_SESSION['USER_LOGIN_CHAR_NAME'] = $char_name;
}
/**
 * 用户登出
 *
 * @return void
 */
function UserLogout()
{
    unset($_SESSION['USER_LOGIN_USERNAME']);    // 账号
    unset($_SESSION['USER_LOGIN_USER_USN']);    // 账号USN
    unset($_SESSION['USER_LOGIN_TIME']);    // 登录时间
    unset($_SESSION['USER_LOGIN_STEP']);    // 登录步骤
    unset($_SESSION['USER_LOGIN_CHAR_ID']); // 角色ID
    unset($_SESSION['USER_LOGIN_CHAR_NAME']); // 角色名
}
