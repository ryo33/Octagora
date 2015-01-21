<?php

require DIR . 'web/Template.php';
$tmpl = new Template();
$user_id = $req->check_login();

switch($req->get_uri()){
case 'messages':
    if($req->request_method === REQUEST::GET){
    }else{
    }
    break;
case 'users':
    if($req->request_method === REQUEST::GET){
    }else{
    }
    break;
case 'apps':
    if($req->request_method === REQUEST::GET){
    }else{
    }
    break;
}

$res->content[] = $tmpl->display();
