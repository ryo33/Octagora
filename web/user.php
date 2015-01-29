<?php

switch($req->get_uri()){
case false:
    if($req->request_method === REQUEST::GET){
        $tmpl->content[] = Design::link('applications', 'Applications', 'uk-button uk-button-primary');
    }else{
    }
    break;
default:
    error(400, ACTION);
}
