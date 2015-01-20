<?php

mb_internal_encoding('UTF-8');
mb_http_input('auto');
mb_http_output('UTF-8');

define('DIR', dirname(__FILE__) . '/');
define('REQ', DIR . '/require/');

require REQ . 'EasySql.php';
require REQ . 'function.php';
require DIR . 'setting.php';
require REQ . 'Request.php';
require REQ . 'Response.php';

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

ob_start();
switch($req->get_uri(true)){
case 'api':
    $req->next_uri();
    $res->display_type = Request::JSON;
    switch($req->get_uri()){
    case 'authorize':
        require DIR . 'authorize/index.php';
        quit();
    case 'token':
        require DIR . 'token/index.php';
        quit();
    default:
        $auth = new Auth($con);
        $result = $auth->access();
        $user_id = $result['user_id'];
        $app_id = $result['app_id'];
    }
default:
    $res->display_type = Response::HTML;
    $user_id = $req->check_login();
    $app_id = false;
    break;
}

switch($req->get_uri()){
case false:
    require DIR . 'top.php';
    break;
case 'messages':
    require DIR . 'messages.php';
    break;
case 'users':
    require DIR . 'users.php';
    break;
default:
    error(400, 'uri');
}

quit();

function quit(){
    global $req, $res;
    if($req->check_param() === true){
        error(400, 'unused parameter');
    }
    if($req->check_uri()){
        error(400, 'uri');
    }
    $res->display();
    ob_end_flush();
    exit();
}

function error($status, $message, $log=false){
    global $con, $res, $req;
    $error = $req->get_param(ERROR, false);
    if($error === '200'){
        header('HTTP', true, 200);
    }else{
        header('HTTP', true, $status);
    }
    if($res->display_type === Response::HTML){
    }else{
        $res->content['status'] = "$status";
        $res->content['message'] = "$message";
    }
    if($log !== false){
        $con->insert('error_log', array('status', 'message', 'text'), array($status, $message, $log));
    }
    quit();
}
