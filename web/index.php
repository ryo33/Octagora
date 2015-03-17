<?php

switch($req->get_uri()){
case 'messages':
    $auth = new Auth($con);
    $request_data = [];
    if($req->request_method ===  Request::GET){
        $show = 'Display Post Form';
        $hide = 'Hide Post Form';
        $form_animation = 500;

        $message = $req->get_param('message', '');
        $postform = $req->get_param('form', 'true');
        $ts = $req->get_param(FORM_TAGS, '');
        $request_data = [
            'form' => $postform = (($postform === 'false') ? 'false' : 'true'),
            FORM_TAGS => $ts
        ];
        $tags_form = [
            'name'=>'tags',
            'type'=>'text',
            'placeholder'=>'Tags'
        ];
        if(($post_tags = $req->get_param(POST_TAGS, '')) !== ''){
            $tags_form['value'] = $post_tags;
        }


        $tmpl->add(
            Design::form_start(false, 'messages', 'GET') .
            Design::tag('div', Design::tag('div', Design::tag('input', '', ['id'=>'reloadform', 'name'=>FORM_TAGS, 'class'=>'uk-form uk-form-large', 'style'=>'height: 100%; width: 100%;', 'value'=>$ts, 'placeholder'=>'Tags']), ['style'=>'margin-right: 100px;']), ['style'=>'float: left; width: 100%; margin-right: -100px;']) .
            Design::tag('button', 'Reload', ['id'=>'reload', 'type'=>'submit', 'class'=>'uk-button uk-button-large uk-button-success uk-vertical-align-middle', 'style'=>'height: 100%; float: right; width: 100px;']) .
            Design::form_end()
        );

        $tmpl->add(
            Design::tag('div',
            Design::tag('button', $show, ['id'=>'displaypostform', 'class'=>'uk-button uk-button-primary']) .
            Design::tag('div',
                Design::form_start('post', 'messages', 'POST') .
                (count($message) > 0 ? Design::form_incorrect($message) : '') .
                Design::form_input($tags_form) .
                Design::form_textarea([
                    'placeholder'=>'Text',
                    'name'=>'text',
                    'id'=>'text',
                ]) .
                Design::form_submit('Post', ['id'=>'post']),
                    ['id'=>'postform', 'style'=>'display: none;'])
                ) .
                Design::form_end() .
                Script::switch_id('postform', 'displaypostform', $show, $hide, $postform === 'true')
            );
        $access_token = get_access_token();
        $tmpl->add(get_messages($access_token, $request_data));
    }else{
        check_token('post', $req->get_param(TOKEN, ''));
        $message = $req->get_param('text', '');
        $tags = $req->get_param('tags', '');
        $data = [
            ACCESS_TOKEN=>get_access_token(),
                TAGS=>$tags,
                TEXT=>$message
            ];
        $result = curl('api/1/messages', $data, true);
        if($result['status'] !== 200 && ($result['error'] === Error::old_access_token || $result['error'] === Error::wrong_access_token)){
            $data = [
                ACCESS_TOKEN=>get_access_token(),
                    TAGS=>$tags,
                    TEXT=>$message,
                    ERROR=>'200'
                ];
            if($se->is_login){
                if(($data['access_token'] = regenerate_access_token()) !== true){
                    $result = curl('api/1/messages', $data, true);
                }
            }else{
                $data['access_token'] = $access_token = get_access_token();
                $data = http_build_query($data, '', '&');
                $key = strpos(URL, 'https') === 0 ? 'https' : 'http';
                $context = [
                    $key=>array(
                        'method'=>'POST',
                        'header'=>implode('\r\n', [
                            'Content-Type: application/x-www-form-urlencoded',
                            'Content-Length: ' . strlen($data)
                        ]),
                        'content'=>$data
                    )
                ];
                $result = curl('api/1/messages', $data, true);
            }
        }
        redirect();
    }
    break;
case 'users':
    if($se->is_login){
        $action = $req->get_param(ACTION, false);
        if($action === 'logout'){
            $se->logout();
            redirect(URL);
        }
        require DIR . 'web/user.php';
    }else if($req->request_method === REQUEST::GET){//GET
        $uri = $req->get_uri();
        if($uri === false){
            $action = $req->get_param(ACTION, false);
            require DIR . 'web/login.php';
        }else if($user->is_exists($uri)){
            require DIR . 'web/user.php';
        }else{
            error(400, 'user_id');
        }
    }else{//POST
        if($req->get_uri() !== false){
            error(400, 'url');
        }
        $action = $req->get_param(ACTION, false);
        $token = $req->get_param(TOKEN, '');
        if($action === false){//login
            do_login($se, $req, $user, $token);
            redirect();
        }else if($action === 'new'){//signup
            check_token('signup', $token);
            $result = $user->add_user(['name'=>$req->get_param('name', ''), 'name2'=>$req->get_param('name2', ''), 'password'=>$req->get_param('password', ''), 'password2'=>$req->get_param('password2', '')]);
            dump($result, true);
            if($result[0] !== false){
                redirect('users?action=new&message=' . $result[1]);
            }
            $se->login($result[1]);
            redirect();
        }else{
            error(400, ACTION);
        }
    }
    break;
case 'login':
    $action = false;
    require DIR . 'web/login.php';
    break;
case 'signup':
    $action = 'new';
    require DIR . 'web/login.php';
    break;
case 'applications':
    require DIR . 'web/applications.php';
    break;
case 'logout':
    $se->logout();
    redirect(URL);
default:
    require DIR . 'web/webapp.php';
    break;
}

$res->content[] = $tmpl->display();

function check_login(){
    global $se;
    if(! $se->is_login){
        redirect('users');
    }
}
