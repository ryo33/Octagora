<?php

define('NAME', 'name');
define('TYPE', 'type');
define('REDIRECT', 'redirect');
define('DESCRIPTION', 'description');
define('WEB', 'web');

$application = new Application($con);

$uri = $req->get_uri();
if($uri === false){
    check_login();
    if($req->request_method === REQUEST::GET){
        switch($req->get_param('action', false)){
        case false:
            $apps = $application->get_applications($se->user_id);
            $rows = [];
            if(count($apps) > 0){
                foreach($apps as $app){
                    $rows[] = [
                        Design::link('applications/' . urlencode($app['id']), 'Detail', 'uk-button uk-button-primary uk-float-right'),
                        Design::link('applications/' . urlencode($app['id']) . '?action=edit', 'Edit', 'uk-button uk-button-success uk-float-right'), $app['id'], $app['name'], $app['client_type'], $app['description'], $app['web']];
                }
            }
            $tmpl->add('<div class="uk-width-3-5 uk-container-center">');
            $tmpl->content[] = Design::tag('div', Design::table([
                '', '', 'ID', 'Name', 'Type', 'Description', 'Web Page'
            ], $rows, 'Applications', 'uk-table'), ['class'=>'uk-overflow-container uk-container-center']);
            $tmpl->add(
                Design::tag('div', Design::link('applications?action=new', 'Add New Application', 'uk-button uk-width-1-1'), ['class'=>'uk-container-center uk-width-5-6', 'style'=>'margin-top: 10px;'])
            );
            $tmpl->add('</div>');
            break;
        case 'new':
            $tmpl->content[] =
                Design::tag('div',
                    Design::form_start('new_application', 'applications', 'POST') .
                    Design::form_incorrect($req->get_param('message', false)) .
                    Design::form_input([
                        'type'=>'text',
                        'label'=>'Application Name',
                        'name'=>NAME,
                        'required'=>true
                    ]) .
                    Design::form_select([
                        'label'=>'Application Type',
                        'name'=>TYPE,
                        'required'=>true,
                        'options'=>[
                            'confidencial',
                            'public'
                        ]
                    ]) .
                    Design::form_input([
                        'type'=>'url',
                        'label'=>'Redirect URI',
                        'name'=>REDIRECT,
                    ]) .
                    Design::form_textarea([
                        'label'=>'Description',
                        'name'=>DESCRIPTION,
                    ]) .
                    Design::form_input([
                        'type'=>'url',
                        'label'=>'Web Page',
                        'name'=>WEB,
                    ]) .
                    Design::form_input([
                        'type'=>'hidden',
                        'name'=>'action',
                        'value'=>'new'
                    ]) .
                    Design::form_submit('Register') .
                    Design::form_end(),
                ['class'=>'uk-container-center']);
            break;
        }
    }else if($req->get_param('action', false) === 'new'){
        check_token('new_application', $req->get_param(TOKEN, ''));
        $result = $application->add_application(
            $se->user_id,
            [
                'name'=>$req->get_param(NAME, ''),
                'type'=>$req->get_param(TYPE, ''),
                'redirect'=>$req->get_param(REDIRECT, ''),
                'description'=>$req->get_param(DESCRIPTION, ''),
                'web'=>$req->get_param(WEB, '')
            ]);
        if($result[0] !== false){
            redirect('applications?action=new&message=' . $result[1]);
        }
        redirect('applications/' . urlencode($result[1]));
    }else{
        redirect();
    }
}else if($application->is_exists($uri)){
    if($req->request_method === REQUEST::GET){
        $app = $application->get_application($uri, true);
        $action = $req->get_param('action', false);
        if($action === false){
            if($app['user_id'] === $se->user_id){
                $tmpl->add(Design::link('applications', 'Applications', 'uk-button uk-button-primary'));
                $tmpl->content[] = Design::tag('div', Design::table([
                    '', 'ID', 'Name', 'Type', 'Redirect URI', 'CLIENT_ID', 'CLIENT_SECRET', 'Description', 'Web Page'
                ], [
                    [
                        Design::link('applications/' . urlencode($uri) . '?action=edit', 'Edit', 'uk-button uk-button-success'), $uri, $app['name'], $app['client_type'], $app['redirect'], $app['client_id'], $app['client_secret'], $app['description'], $app['web']
                    ]
                ], false, 'uk-table'), ['class'=>'uk-container-center uk-overflow-container']);
            }else{
                $tmpl->content[] = Design::tag('div', Design::table([
                    'ID', 'Name', 'Description'
                ], [
                    [
                        $uri, $app['name'], $app['description']
                    ]
                ], false, 'uk-table'), ['class'=>'uk-container-center uk-overflow-container']);
            }
        }else if($action === 'edit'){
            $tmpl->content[] =
                Design::tag('div',
                    Design::form_start('edit_application', 'applications/' . urlencode($uri), 'POST') .
                    Design::form_incorrect($req->get_param('message', false)) .
                    Design::form_input([
                        'value'=>$app['name'],
                        'type'=>'text',
                        'label'=>'Application Name',
                        'name'=>NAME,
                        'required'=>true
                    ]) .
                    Design::form_select([
                        'value'=>$app['client_type'],
                        'label'=>'Application Type',
                        'name'=>TYPE,
                        'required'=>true,
                        'options'=>[
                            'confidencial',
                            'public'
                        ]
                    ]) .
                    Design::form_input([
                        'value'=>$app['redirect'],
                        'type'=>'url',
                        'label'=>'Redirect URI',
                        'name'=>REDIRECT,
                    ]) .
                    Design::form_textarea([
                        'value'=>$app['description'],
                        'label'=>'Description',
                        'name'=>DESCRIPTION,
                    ]) .
                    Design::form_input([
                        'value'=>$app['web'],
                        'type'=>'url',
                        'label'=>'Web Page',
                        'name'=>WEB,
                    ]) .
                    Design::form_input([
                        'type'=>'hidden',
                        'name'=>'action',
                        'value'=>'edit'
                    ]) .
                    Design::form_submit('Edit') .
                    Design::form_end(),
                'uk-container-center');
        }else{
            redirect();
        }
    }else if($req->get_param('action', false) === 'edit'){
        check_login();
        check_token('edit_application', $req->get_param(TOKEN, ''));
        $result = $application->edit_application(
            $uri,
            $se->user_id,
            [
                'name'=>$req->get_param(NAME, ''),
                'type'=>$req->get_param(TYPE, ''),
                'redirect'=>$req->get_param(REDIRECT, ''),
                'description'=>$req->get_param(DESCRIPTION, ''),
                'web'=>$req->get_param(WEB, '')
            ]);
        if($result === true){
            redirect();
        }else if($result !== false){
            redirect('applications?action=new&message=' . $result);
        }
        redirect('applications/' . urlencode($uri));
    }
}else{
    error(400, 'uri');
}
