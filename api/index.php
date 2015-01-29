<?php

$auth = new Auth($con);

$json = ['status'=>200];
switch($req->get_uri()){
case '1':
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
