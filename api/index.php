<?php

require REQ . 'Auth.php';
$auth = new Auth($con);

$json = ['status'=>200];
switch($req->get_uri()){
case 'authorize':
    require DIR . 'api/authorize/index.php';
    quit();
case 'token':
    require DIR . 'api/token/index.php';
    quit();
case '1':
//    $auth->access($user_id, $client_id);
    require DIR . 'api/1/index.php';
    break;
default:
    error(400, 'version');
}
$res->content[] = json_encode($json);
