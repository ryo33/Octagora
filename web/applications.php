<?php

$uri = $req->get_uri();
if($uri === false){
    if($req->request_method === REQUEST::GET){
        check_login();
        switch($req->get_param('action', false)){
        case false:
            $apps = $application->get_applications($se->user_id);
            if(count($apps) > 0){
                $tmpl->content[] = '<ul>';
                foreach($apps as $app){
                    $tmpl->content[] =
                        Design::tag('li', Design::link('applications/' . $app['id'], $app['name']));
                }
                $tmpl->content[] = '</ul>';
            }
        case 'new':
            $tmpl->content[] =
                Design::form_start('new_application', 'applications', 'POST') .
                Design::form_incorrect($req->get_param('message', false)) .
                Design::form_input([
                    'type'=>'text',
                    'label'=>'Application Name',
                    'name'=>'name',
                    'required'=>true
                ]) .
                Design::form_input([
                    'type'=>'text',
                    'label'=>'Application Type',
                    'name'=>'type',
                    'required'=>true
                ]) .
                Design::form_input([
                    'type'=>'url',
                    'label'=>'Redirect URL',
                    'name'=>'redirect',
                ]) .
                Design::form_textarea([
                    'label'=>'Description',
                    'name'=>'description',
                ]) .
                Design::form_input([
                    'type'=>'url',
                    'label'=>'Web Page',
                    'name'=>'web',
                ]) .
                Design::form_submit('Register') .
                Design::form_end();
        }
    }else if($req->get_param('action', false) === 'new'){
        check_login();
        check_token('new_application', $req->get_param(TOKEN, ''));
        $result = $application->add_application();
        if($result[0] === true){
            redirect('applications?action=new');
        }
        redirect('applications/' . $result[1]);
    }else{
        redirect(URL);
    }
}else if($application->is_exists($uri)){
    if($req->request_method === REQUEST::GET){
        $app = $application->get_applicatoin($uri);
        $action = $req->get_param('action', false);
        if($action === false){
            if($app['user_id'] === $se->user_id){
                $tmpl->content[] = Design::table([
                    '', 'ID', 'Name', 'Type', 'CLIENT_ID', 'CLIENT_SECRET', 'Description'
                ], [
                    [
                        Design::link('applications/' . $app['id'] . '?action=edit', 'Edit', 'uk-button-success'), $app['id'], $app['name'], $app['type'], $app['client_id'], $app['client_secret'], $app['description']
                    ]
                ]);
            }else{
                $tmpl->content[] = Design::table([
                    'ID', 'Name', 'Description'
                ], [
                    [
                        $app['id'], $app['name'], $app['description']
                    ]
                ]);
            }
        }else if($action === 'edit'){
        }else{
            redirect(URI);
        }
    }else if($req->get_param('action', false) === 'edit'){
    }
}else{
    error(400, 'uri');
}
