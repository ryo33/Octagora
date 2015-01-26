<?php

$application = new Application($con);

switch($req->get_uri()){
case 'applications':
    if($req->request_method === REQUEST::GET){
        $uri = $req->get_uri();
        if($uri === false){
        }else if($uri){
        }else{
            redirect('users/applications');
        }
    }else{
        $action == $req->get_param(ACTION, false);
        switch($action){
        case false:
            $application->add_application([
                'name'=>$req->get_param('name', ''),
                'application_type'=>$req->get_param('application_type', ''),
                'redirect'=>$req->get_param('redirect', ''),
                'description'=>$req->get_param('description', ''),
                'web'=>$req->get_param('web', '')
            ]);
            break;
        default:
            error(400, 'action');
        }
    }
    break;
case false:
    if($req->request_method === REQUEST::GET){
    }else{
    }
    break;
default:
    error(400, ACTION);
}
