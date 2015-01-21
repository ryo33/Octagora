<?php
$microtime = (int) microtime(true) * 1000;
$now = new DateTime('now', new DateTimeZone('GMT'));

mb_internal_encoding('UTF-8');
mb_http_input('auto');
mb_http_output('UTF-8');

define('DIR', dirname(__FILE__) . '/');
define('REQ', DIR . '/require/');

require REQ . 'EasySql.php';
require REQ . 'function.php';
require DIR . 'setting.php';
require REQ . 'Request.php';
require REQ . 'Model.php';
require REQ . 'Response.php';

define('LAST', 'last');
define('START', 'start');
define('LENGTH', 'length');
define('REQUEST', 'r');
define('TAGS', 'ts');
define('TAGS_OPTION', 'tso');
define('TEXT', 't');
define('NEEDS', 'needs');
define('ORDER', 'order');
define('CLIENT_ID', 'client_id');
define('CLIENT_SECRET', 'client_secret');
define('ERROR', 'error');

$con = new EasySql($database_dsn, $database_username, $database_password);
$req = new Request();
$res = new Response();

try{
    ob_start();
    switch($req->get_uri()){
    case 'api':
        $is_api = true;
        require DIR . 'api/index.php';
        break;
    default:
        $req->get_uri(0, -1);
        $is_api = false;
        require DIR . 'web/index.php';
        break;
    }
    quit();
}catch(Exception $e){
    error_log($e->getMessage());
    error(500, 'error', $e->getMessage());
}

function quit($error=false){
    global $req, $res;
    if($error === false){
        if($req->check_param() === true){
            error(400, 'unused parameter');
        }
        if($req->check_uri()){
            error(400, 'uri');
        }
    }
    $res->display();
    ob_end_flush();
    exit();
}

function error($status, $message, $log=false){
    global $con, $res, $req, $is_api;
    if($is_api){
        $error = $req->get_param(ERROR, false);
        if($error === '200'){
            header('HTTP', true, 200);
        }else{
            header('HTTP', true, $status);
        }
        $json['status'] = "$status";
        $json['message'] = "$message";
        $res->content = [json_encode($json)];
    }
    if($log !== false || !$is_api){
        $con->insert('error_log', array('status', 'message', 'text', 'created'), array($status, $message, $log, now()->format('Y:m:d H:i:s')));
    }
    quit(true);
}
