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
    $se->remove('access_token');
    $se->remove('refresh_token');
}

function regenerate_access_token(){
    global $se;
    $result = curl('oauth/token', ['grant_type'=>'refresh_token', 'refresh_token'=>$se->get('refresh_token')]);
    if(isset($result['access_token'], $result['refresh_token'])){
        $se->set('access_token', $access_token = $result['access_token']);
        $se->set('refresh_token', $result['refresh_token']);
        return $access_token;
    }
    return true;
}

function get_access_token($new=false){
    global $req, $auth, $se, $user, $webapp_id, $webapp_client_id, $webapp_client_secret, $request_data;
    $access_token = $se->get('access_token', false);
    if($access_token === false || $new){
        if($se->is_login){
            if(! $auth->check_credential($webapp_client_id, $se->user_id)){
                $auth->create_credential($webapp_client_id, $se->user_id);
            }
            $token = $auth->create_auth_code($webapp_id);
            $auth->activate_auth_code($token, $webapp_id, $se->user_id);
            $result = $auth->get_auth_code($token);
            $data = [
                'grant_type'=>'authorization_code',
                'code'=>$result['code']
            ];
            $result = curl('oauth/token', $data, false, true);
            if(isset($result['access_token'], $result['refresh_token'])){
                $se->set('access_token', $access_token = $result['access_token']);
                $se->set('refresh_token', $result['refresh_token']);
            }
        }else{
            $data = [
                'grant_type'=>'client_credentials'
            ];
            $result = curl('oauth/token', $data, false, true);
            if(isset($result['access_token'])){
                $se->set('access_token', $access_token = $result['access_token']);
            }
        }
    }
    return $access_token;
}

function went_wrong(){
    return Design::tag('div', '<p>Sorry. Something went wrong.</p>', ['class'=>'uk-panel uk-panel-box']);
}

function get_messages($access_token, $request_data){
    global $req, $auth, $se, $user, $webapp_id, $webapp_client_id, $webapp_client_secret;
    $result_text = '';
    if(is_string($access_token)){
        $data = [
            ACCESS_TOKEN=>$access_token,
            LAST=>$req->get_param(LAST, ''),
            START=>$req->get_param(START, '0'),
            LENGTH=>$req->get_param(LENGTH, '100'),
            ORDER=>$req->get_param(ORDER, 'desc'),
            TAGS=>$req->get_param(FORM_TAGS, ''),
            TAGS_OPTION=>$req->get_param(TAGS_OPTION, 'normal,by_user,to_user,user,message,to_message,year,month,day,hour,minute,hash,not_used')
        ];
        $data[NEEDS] = 'i,t,ts';
        $tmp = [];
        foreach($data as $key => $item){
            if($item !== false && $item !== ''){
                $tmp[$key] = $item;
            }
        }
        $content = curl('api/1/messages', $tmp);
        if($content['status'] !== 200){
            if(!isset($content['error']) || $content['error'] === Error::old_access_token){
                $se->remove('access_token');
                $se->remove('refresh_token');
                if($req->get_param('reload', false) === false){
                    $uri = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    redirect_uri($uri . ((strpos($uri, '?') !== false) ? '&' : '?') . 'reload=true');
                }
            }else{
                $result_text .= went_wrong();
            }
        }
        if($content['status'] === 200){
            foreach($content['messages'] as $message){
                $result_text .= Design::message_panel($message, $request_data);
            }
        }
    }else{
        $result_text .= went_wrong();
    }
    return $result_text;
}
