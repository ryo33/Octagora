<?php

$responce_type = $req->get_param('responce_type', false);
$client_id = $req->get_param('client_id', false);
$redirect_uri = $req->get_param('redirect_uri', false);
$scope = $req->get_param('scope', false);
$state = $req->get_param('state', false);

header('Content-Type: application/json; charset=utf-8');

if($client_id === false){
    exit('nothing client_id');
}
$app = $application->check_client_id($client_id);
if($app === true){
    exit('wrong client_id');
}
if($redirect_uri !== false && $app['redirect'] !== $app['redirect']){
    oauth_error($app['redirect'], 'invalid_request', $state);
}
if($responce_type === false){
    oauth_error($app['redirect'], 'invalid_request', $state);
}
if($scope !== false && $scope !== 'post'){
    oauth_error($app['redirect'], 'invalid_scope', $state);
}
switch($responce_type){
case 'code':
    break;
case 'token':
    break;
default:
    oauth_error($app['redirect'], 'unsupported_response_type', $state);
}
exit();
