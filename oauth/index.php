<?php

$now = new DateTime('now', new DateTimeZone('GMT'));

mb_internal_encoding('UTF-8');
mb_http_input('auto');
mb_http_output('UTF-8');

define('DIR', dirname(__FILE__) . '/../');
define('REQ', DIR . 'require/');
define('URL', (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . '/');

require DIR . 'start.php';
require DIR . 'setting.php';
require REQ . 'function.php';
require REQ . 'ClassLoader.php';

$loader = new ClassLoader();
$loader->register_directory(DIR . '/require');
$loader->register();

$con = new EasySql($database_dsn, $database_username, $database_password);
$req = new Request();

$auth = new Auth($con);
$application = new Application($con);

switch($req->get_uri(1, 1)){
case 'authorize':
    require DIR . 'oauth/authorize.php';
    break;
case 'token':
    require DIR . 'oauth/token.php';
    break;
default:
    exit();
}

function error($error, $uri=false){
    if($uri === false){
        exit(json_encode(['error_message'=>$error]));
    }else{
        redirect($uri . '?error=' . $error . '&state=' . $state);
    }
}

function oauth_redirect($uri, $message, $params=false){
    if($uri === false){
        exit($message);
    }
    if($params !== false){
        header('Location: ' . $uri . '?' . $params);
    }else{
        header('Location: ' . $uri);
    }
    exit();
}
