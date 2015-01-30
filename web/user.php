<?php

switch($req->get_uri()){
case false:
    if($req->request_method === REQUEST::GET){
        $tmpl->add(
            Design::tag('ul',
                Design::tag('li', Design::link('applications', 'Applications', 'uk-button uk-button-primary')) .
                Design::tag('li', Design::link('users?action=logout', 'Logout', 'uk-button uk-button-warning'))
                , ['type'=>'none']
            )
        );
    }else{
    }
    break;
default:
    error(400, ACTION);
}
