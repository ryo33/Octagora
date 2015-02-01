<?php

function auto_login($user_id){
    global $con;
    do{
        $auto = sha256(mt_rand(0, 999) . microtime() . $user_id);
    }while($con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `auto` = ?', $auto) !== '0');
    $con->update('user', $user_id, ['auto'], [$auto]);
    setcookie(AUTO_LOGIN, $auto, time() + 30 * 24 * 3600);
}

function remove_auto(){
    setcookie(AUTO_LOGIN, '', time() - 3600);
}

function login(){
    global $se, $user, $_COOKIE, $con;
    if(! $se->check_login($user)){
        if(isset($_COOKIE[AUTO_LOGIN]) && strlen($_COOKIE[AUTO_LOGIN]) === 64){
            $result = $con->fetch('SELECT COUNT(`id`), `id` FROM `user` WHERE `auto` = ?', $_COOKIE[AUTO_LOGIN]);
            if($result['COUNT(`id`)'] === '1'){
                $se->login($result['id']);
                auto_login($result['id']);
            }else{
                remove_auto();
            }
        }
    }
}

function do_login(&$se, &$req, &$user, $token){
    check_token('login', $token);
    $result = $user->check_login($req->get_param('name', ''), $req->get_param('password', ''));
    if($result === true){
        redirect('users?message=incorrect');
    }
    $remember = $req->get_param('remember', false);
    if($remember === 'true'){
        auto_login($result);
    }
    $se->login($result);
}
