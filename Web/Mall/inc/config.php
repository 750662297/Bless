<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);  // 屏蔽通知类消息
session_start();    // 开启session
ini_set('date.timezone', 'Asia/Shanghai');

define('WEB_SIGN_KEY', 'QQ263541180');
define('SQLSERVER_CONFIG', ['HOST' => '127.0.0.1', 'USER' => 'sa', 'PASS' => 'BlessUnleashed', 'BASE' => 'create_gamedb_pc']);
