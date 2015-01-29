<?php

$grant_type = $req->get_param('grant_type', false);
header('Content-Type: application/json; charset=utf-8');
switch($grant_type){
case 'authorization_code':
    $redirect_uri = $req->get_param('redirect_uri', false);
    $code = $req->get_param('code', false);
    break;
case 'client_credentials':
    $client_id = $req->get_param('client_id', false);
    $app = $application->check_client_id($client_id);
    if($app === true){
        exit('client_id');
    }
    switch($app['client_type']){
    case Application::TYPE_CONFIDENTIAL:
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
default:
    exit('grant_type');
}
exit();
