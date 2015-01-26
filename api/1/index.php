<?php

switch($req->get_uri()){
case 'messages':
    require REQ . 'Message.php';
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
        }else if(count($uri) === Model::ID_LENGTH){
            $message->get_message($json,
                $uri,
                $req->get_param(NEEDS, 'i,t'),
                $req->get_param(TAGS_OPTION, 'normal,by_user,to_user,user,message,to_message')
            );
        }
    }else if($req->get_uri() === false){
        $message->post_message($json,
            $req->get_param(TAGS, false),
            $req->get_param(TEXT, ''),
            $user_id = false,
            $client_id
        );
    }else{
        error(400, 'uri');
    }
    break;
case 'users':
    break;
default:
    $is_api = false;
    $tmpl->title = 'Octagora API v1';
    require DIR . 'api/1/top.php';
    break;
}
