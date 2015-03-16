<?php

$application = new Application($con);

switch($req->get_uri()){
case 'messages':
    $auth->access($req->get_param(ACCESS_TOKEN, false), $auth_info);
    $message = new Message($con);
    if($req->request_method === REQUEST::GET){
        $uri = $req->get_uri();
        if($uri === false){
            $message->get_messages($json,
                $req->get_param(LAST, ''),
                $req->get_param(START, '0'),
                $req->get_param(LENGTH, '100'),
                $req->get_param(NEEDS, 'i,t'),
                $req->get_param(ORDER, 'desc'),
                $req->get_param(TAGS, false),
                $req->get_param(TAGS_OPTION, 'normal,by_user,to_user,user,message,to_message')
            );
        }else if(strlen($uri) === Model::ID_LENGTH){
            $message->get_message($json,
                $uri,
                $req->get_param(NEEDS, 'i,t'),
                $req->get_param(TAGS_OPTION, 'normal,by_user,to_user,user,message,to_message')
            );
        }
    }else if($req->get_uri() === false){
        $app = $application->check_client_id($auth_info['application_id']);
        $auth_info['client_type'] = $application->get_application($auth_info['application_id'], true)['client_type'];
        $message->post_message($json,
            $req->get_param(TAGS, false),
            $req->get_param(TEXT, ''),
            $auth_info
        );
    }else{
        error(400, 'uri');
    }
    break;
case 'users':
    $auth->access($req->get_param(ACCESS_TOKEN, false), $auth_info);
    if($req->request_method === REQUEST::GET){
        $uri = $req->get_uri();
        if(strlen($uri) === Model::ID_LENGTH){
            $user->get_user($json,
                $uri,
                $req->get_param(NEEDS, 'i,n1,n2')
            );
        }else{
            error(400, 'user_id');
        }
    }else{
        error(400, 'uri');
    }
    break;
case 'applications':
    $auth->access($req->get_param(ACCESS_TOKEN, false), $auth_info);
    if($req->request_method === REQUEST::GET){
        $uri = $req->get_uri();
        if(strlen($uri) === Model::ID_LENGTH){
            $application->get_application_json($json,
                $uri,
                $req->get_param(NEEDS, 'i,n')
            );
        }else{
            error(400, 'application_id');
        }
    }else{
        error(400, 'uri');
    }
    break;
default:
    $is_api = false;
    require DIR . 'api/1/top.php';
    break;
}
