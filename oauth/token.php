<?php

$grant_type = $req->get_param('grant_type', false);
$client_id = $req->get_param('client_id', false);
$client_secret = $req->get_param('client_secret', false);
$scope = $req->get_param('scope', false);

header('Content-Type: application/json; charset=utf-8');

$app = get_app();
if($app === true){
    error('client_id');
}

switch($grant_type){
case 'password':
    $user = new User($con);
    $username = $req->get_param('username', false);
    $password = $req->get_param('password', false);
    if($client_id === false){
        error('client_id');
    }
    if($password === false){
        error('password');
    }
    if($username === false || ! $user->check_name($username)){
        error('username');
    }
    $user_id = $user->check_login($username, $password);
    if($user_id === true){
        error('incorrect');
    }
    switch($app['client_type']){
    case Application::TYPE_CONFIDENTIAL:
        $result = $auth->create_access_token([
            'type'=>Auth::AT_PASSWORD,
            'application_id'=>$app['id'],
            'user_id'=>$user_id
        ]);
        exit(json_encode([
            'access_token'=>$result[0],
            'expires_in'=>Auth::AT_LIMIT,
            'refresh_token'=>$result[1]
        ]));
        break;
    case Application::TYPE_PUBLIC:
        $result = $auth->create_access_token([
            'type'=>Auth::AT_PASSWORD,
            'application_id'=>$app['id'],
            'user_id'=>$user_id
        ]);
        exit(json_encode([
            'access_token'=>$result[0],
            'expires_in'=>Auth::AT_LIMIT,
            'refresh_token'=>$result[1]
        ]));
        break;
    }
    break;
case 'client_credentials':
    switch($app['client_type']){
    case Application::TYPE_CONFIDENTIAL:
        $result = $auth->create_access_token([
            'type'=>Auth::AT_CLIENT,
            'application_id'=>$app['id']
        ]);
        exit(json_encode([
            'access_token'=>$result,
            'expires_in'=>Auth::AT_LIMIT
        ]));
        break;
    case Application::TYPE_PUBLIC:
        $result = $auth->create_access_token([
            'type'=>Auth::AT_CLIENT,
            'application_id'=>$app['id']
        ]);
        exit(json_encode([
            'access_token'=>$result,
            'expires_in'=>Auth::AT_LIMIT
        ]));
        break;
    }
    break;
case 'authorization_code':
    $redirect_uri = $req->get_param('redirect_uri', false);
    $code = $req->get_param('code', false);
    $scope = $req->get_param('scope', false);
    $auth_code = $auth->check_auth_code($code, $app['id']);
    $result = $auth->create_access_token([
        'type'=>Auth::AT_CODE,
        'application_id'=>$app['id'],
        'user_id'=>$auth_code['user_id']
    ]);
    exit(json_encode([
        'access_token'=>$result[0],
        'expires_in'=>Auth::AT_LIMIT,
        'refresh_token'=>$result[1]
    ]));
    break;
case 'refresh_token':
    $refresh_token = $req->get_param('refresh_token', false);
    if($refresh_token === false){
        error(400, 'invalid_request');
    }
    break;
default:
    error(400, 'invalid_request');
}
exit();
