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
        $uri = $req->get_uri();
        if($uri === false){
            require DIR . 'web/login.php';
        }else if($user->is_exists($uri)){
            require DIR . 'web/user.php';
        }else{
            error(400, 'user_id');
        }
    }else{
        if($req->get_uri !== false){
            error(400, 'url');
        }
    }
    break;
default:
    break;
}

$res->content[] = $tmpl->display();
