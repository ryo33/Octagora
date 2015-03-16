<?php

header('Content-Type: application/json; charset=utf-8');

$se = new Session();
$user = new User($con);

$token = $req->get_param('token', false);
if($token === false){
    $response_type = $req->get_param('response_type', false);
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
    if($response_type === false){
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
switch($response_type){
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
