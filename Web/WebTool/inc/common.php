<?php
require_once dirname(__FILE__) . '/config.php';
function isPost() {
    return ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST']))) ? 1 : 0;
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