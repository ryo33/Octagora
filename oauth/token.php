<?php

$grant_type = $req->get_param('grant_type', false);
header('Content-Type: application/json; charset=utf-8');
switch($grant_type){
case 'password':
    $user = new User($con);
    $username = $req->get_param('username', false);
    $password = $req->get_param('password', false);
    $scope = $req->get_param('scope', false);
    $client_id = $req->get_param('client_id', false);
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
    $app = $application->check_client_id($client_id);
    if($app === true){
        error('client_id');
    }
    switch($app['client_type']){
    case Application::TYPE_CONFIDENTIAL:
        //TODO
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
    $client_id = $req->get_param('client_id', false);
    $app = $application->check_client_id($client_id);
    $scope = $req->get_param('scope', false);
    if($app === true){
        error('client_id');
    }
    switch($app['client_type']){
    case Application::TYPE_CONFIDENTIAL:
        //TODO
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
    break;
default:
    exit('grant_type');
}
exit();
