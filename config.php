<?php
session_start();
// putenv("TZ=Asia/Bangkok");
date_default_timezone_set('Asia/Bangkok');
// error_reporting(E_ALL);
header('Content-Type: text/html; charset=UTF-8');

//     define('_DB_HOST_','localhost');
//     define('_DB_USER_','root');
//     define('_DB_PASS_','root');
//     define('_DB_DATA_','demo_tm');
//     define('_DB_TYPE_','mysql');
    
    define('_DB_HOST_','192.168.1.111');
    define('_DB_USER_','root');
    define('_DB_PASS_','eoffice0841606322');
    define('_DB_DATA_','thaimart');
    define('_DB_TYPE_','mysql');


/*** MS Database Connect ***/
define('_MS_HOST_','tmintranet.dyndns.org, 1434');
define('_MS_USER_','bplusbase');
define('_MS_PWD_','#58255825$');
define('_MS_DBNAME_','TMC_LVII');

$DOC_ROOT = $_SERVER['DOCUMENT_ROOT']."/www/tmdt/";
$BASE_URL = "http://localhost/tmdt/";

define('_DOC_ROOT_',$_SERVER['DOCUMENT_ROOT'].'/tmdt/');
define('_BASE_URL_','http://'.$_SERVER['HTTP_HOST'].'/tmdt/');

//CI Library
$system_path = $_SERVER['DOCUMENT_ROOT'].'/tmdt/lib/';
$system_path = rtrim($system_path, '/').'/';
define('BASEPATH', str_replace("\\", "/", $system_path));
$root_path = realpath('.').'/';
$root_path = rtrim($root_path, '/').'/';
define('ROOTPATH', str_replace("\\", "/", $root_path));

define('_COOKIE_NAME_',md5($_SERVER['HTTP_HOST']._DB_DATA_));

?>