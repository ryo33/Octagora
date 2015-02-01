<?php

function get_app(){
    global $_SERVER, $client_id, $client_secret, $application;
    if(isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])){
        $client_id = $_SERVER['PHP_AUTH_USER'];
        $client_secret = $_SERVER['PHP_AUTH_PW'];
        $result = $application->check_client($client_id, $client_secret);
        if($result === true){
            error('client certification');
        }else if($result['client_type'] === Application::TYPE_PUBLIC){
            error('Application Type');
        }
    }else if($client_id !== false && $client_secret !== false){
        $result = $application->check_client($client_id, $client_secret);
        if($result === true){
            error('client certification');
        }else if($result['client_type'] === Application::TYPE_PUBLIC){
            error('Application Type');
        }
    }else if($client_id !== false){
        $result = $application->check_client_id($client_id);
        if($result === true){
            error('client_id');
        }else if($result['client_type'] === Application::TYPE_CONFIDENTIAL){
            error('Application Type');
        }
    }else{
        error('client_id');
    }
    return $result;
}

header('Content-Type: application/json; charset=utf-8');

$se = new Session();
$user = new User($con);

$token = $req->get_param('token', false);
if($token === false){
    $responce_type = $req->get_param('responce_type', false);
    $client_id = $req->get_param('client_id', false);
    $redirect_uri = $req->get_param('redirect_uri', false);
    $scope = $req->get_param('scope', false);
    $state = $req->get_param('state', false);
    $app = get_app();
    if($app === true){
        error('client_id');
    }
    if($redirect_uri !== false && $app['redirect'] !== $app['redirect']){
        error('invalid_request', $app['redirect'], $state);
    }
    if($responce_type === false){
        error('invalid_request', $app['redirect'], $state);
    }
    if($scope !== false && $scope !== 'post'){
        error('invalid_scope', $app['redirect'], $state);
    }
    $_SESSION['_oauth_state'] = $state;
    $token = $auth->create_auth_code($app['id'], $state);
}else{
    $result = $auth->update_auth_code_token($token);
    $token = $result[0];
    $app = $application->check_client_id($result[1]);
    $state = $_SESSION['_oauth_state'];
    switch($req->get_param('action', false)){
    case 'login':
        do_login($se, $req, $user, $token);
        break;
    case 'grant':
        if($se->is_login){
            $auth->create_credential($app['id'], $se->user_id);
        }
        break;
    case 'deny':
        error('access_denied', $app['redirect'], $state);
        break;
    }
}

$content = '';
switch($responce_type){
case 'code':
    login();
    if($se->is_login){
        if($auth->check_credential($app['id'], $se->user_id){
            oauth_redirect($app['redirect'] . '?code=' . $auth->create_auth_code($app['id'], $se->user_id), $state);
        }else{
            $content = Design::credential_form($app);
        }
    }else{
        $content = Design::login_form('oauth/code?token=' . $token);
    }
    break;
case 'token':
    login();
    if($se->is_login){
        if($auth->check_credential($app['id'], $se->user_id){
        }else{
            $content = Design::credential_form($app);
        }
    }else{
        $content = Design::login_form('oauth/code?action=login&token=' . $token);
    }
    break;
default:
    error($app['redirect'], 'unsupported_response_type', $state);
}

header_remove();
$tmpl->add($content);
echo $tmpl->display();

exit();
