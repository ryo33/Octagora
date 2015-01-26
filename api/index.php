<?php

$auth = new Auth($con);
$tmpl = new Template();

$json = ['status'=>200];
switch($req->get_uri()){
case 'authorize':
    require DIR . 'api/authorize/index.php';
    quit();
case 'token':
    require DIR . 'api/token/index.php';
    quit();
case '1':
//    $auth->access($req->get_param(ACCESS_TOKEN, false), $user_id, $client_id);
    require DIR . 'api/1/index.php';
    break;
case false:
    $is_api = false;
    $tmpl->title = 'Octagora API';
    require DIR . 'api/top.php';
    break;
default:
    error(400, 'version');
}
if($is_api){
    $res->content[] = json_encode($json);
}else{
    $res->content[] = $tmpl->display();
}
