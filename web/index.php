<?php

switch($req->get_uri()){
case 'messages':
    if($req->request_method === REQUEST::GET){
    }else{
    }
    break;
case 'users':
    if($se->is_login){
        $action = $req->get_param(ACTION, false);
        if($action === 'logout'){
            $se->logout();
            redirect(URL);
        }
        require DIR . 'web/user.php';
    }else if($req->request_method === REQUEST::GET){//GET
        $uri = $req->get_uri();
        if($uri === false){
            $action = $req->get_param(ACTION, false);
            require DIR . 'web/login.php';
        }else if($user->is_exists($uri)){
            require DIR . 'web/user.php';
        }else{
            error(400, 'user_id');
        }
    }else{//POST
        if($req->get_uri() !== false){
            error(400, 'url');
        }
        $action = $req->get_param(ACTION, false);
        $token = $req->get_param(TOKEN, '');
        if($action === false){//login
            if(check_token('login', $token)){
                redirect(URL);
            }
            $result = $user->check_login($req->get_param('name', ''), $req->get_param('password', ''));
            if($result === true){
                redirect(URL . '/users?message=incorrect');
            }
            $se->login($result);
            redirect(URL);
        }else if($action === 'new'){//signup
            if(check_token('signup', $token)){
                redirect(URL);
            }
            $result = $user->add_user(['name'=>$req->get_param('name', ''), 'name2'=>$req->get_param('name2', ''), 'password'=>$req->get_param('password', ''), 'password2'=>$req->get_param('password2', '')]);
            if($result[0] !== false){
                redirect(URL . '/users?action=new&message=' . $result[1]);
            }
            $se->login($result[1]);
            redirect(URL);
        }else{
            error(400, ACTION);
        }
    }
    break;
case 'login':
    $action = false;
    require DIR . 'web/login.php';
    break;
case 'signup':
    $action = 'new';
    require DIR . 'web/login.php';
    break;
case 'logout':
    $se->logout();
    redirect(URL);
default:
    break;
}

$res->content[] = $tmpl->display();
