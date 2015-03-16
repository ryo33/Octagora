<?php
$auth = new Auth($con);
$request_data = [];
if($req->request_method ===  Request::GET){
    $show = 'Display Post Form';
    $hide = 'Hide Post Form';
    $form_animation = 500;

    $ts = $req->get_param(TAGS, '');

    $message = $req->get_param('message', '');
    $postform = $req->get_param('form', false);
    $tags = $req->get_param('form_tags', '');
    $request_data = [
        'form' => ($postform !== false) ? 'true' : '',
        'form_tags' => $tags
    ];
    $tags_form = [
        'name'=>'tags',
        'required'=>true,
        'type'=>'text'
    ];
    if($tags === ''){
        $tags_form['placeholder'] = 'Tags';
    }else{
        $tags_form['value'] = $tags;
    }

    $tmpl->add(
        Design::form_start(false, '', 'GET') .
        Design::tag('input', '', ['id'=>'reloadform', 'name'=>TAGS, 'class'=>'uk-form uk-form-large uk-width-5-6', 'style'=>'height: 100%;', 'value'=>$ts]) .
        Design::tag('button', 'Reload', ['id'=>'reload', 'type'=>'submit', 'class'=>'uk-button uk-button-large uk-button-success uk-width-1-6 uk-vertical-align-middle', 'style'=>'height: 100%;']) .
        Design::form_end()
    );

    $tmpl->add(
        Design::tag('div',
        Design::tag('button', $show, ['id'=>'displaypostform', 'class'=>'uk-button uk-button-primary']) .
        Design::tag('div',
            Design::form_start('post', '', 'POST') .
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
            Script::switch_id('postform', 'displaypostform', $show, $hide, $postform)
        );
    $tmpl->add(get_messages(get_access_token()));
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
    error_log(dump($result));
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

function regenerate_access_token(){
    global $se;
    $result = curl('oauth/token', ['grant_type'=>'refresh_token', 'refresh_token'=>$se->get('refresh_token')]);
    if(isset($result['access_token'], $result['refresh_token'])){
        $se->set('access_token', $access_token = $result['access_token']);
        $se->set('refresh_token', $result['refresh_token']);
        return $access_token;
    }
    return true;
}

function get_access_token($new=false){
    global $req, $auth, $se, $user, $webapp_id, $webapp_client_id, $webapp_client_secret, $request_data;
    dump(curl('/'));
    $access_token = $se->get('access_token', false);
    if($access_token === false || $new){
        if($se->is_login){
            if(! $auth->check_credential($webapp_client_id, $se->user_id)){
                $auth->create_credential($webapp_client_id, $se->user_id);
            }
            $token = $auth->create_auth_code($webapp_id);
            $auth->activate_auth_code($token, $webapp_id, $se->user_id);
            $result = $auth->get_auth_code($token);
            $data = [
                'grant_type'=>'authorization_code',
                'code'=>$result['code']
            ];
            $result = curl('oauth/token', $data, false, true);
            dump($result, true);
            if(isset($result['access_token'], $result['refresh_token'])){
                $se->set('access_token', $access_token = $result['access_token']);
                $se->set('refresh_token', $result['refresh_token']);
            }
        }else{
            $data = [
                'grant_type'=>'client_credentials'
            ];
            $result = curl('oauth/token', $data, false, true);
            dump($result, true);
            if(isset($result['access_token'])){
                $se->set('access_token', $access_token = $result['access_token']);
            }
        }
    }
    return $access_token;
}

function get_messages($access_token){
    global $req, $auth, $se, $user, $webapp_id, $webapp_client_id, $webapp_client_secret, $request_data;
    $result_text = '';
    if($access_token !== false){
        $data = [
            ACCESS_TOKEN=>$access_token,
            LAST=>$req->get_param(LAST, ''),
            START=>$req->get_param(START, '0'),
            LENGTH=>$req->get_param(LENGTH, '100'),
            ORDER=>$req->get_param(ORDER, 'desc'),
            TAGS=>$req->get_param(TAGS, ''),
            TAGS_OPTION=>$req->get_param(TAGS_OPTION, 'normal,by_user,to_user,user,message,to_message,hash,year,month,day,hour,minute')
        ];
        $request_data = $request_data + $data;
        $data[NEEDS] = 't,ts';
        $tmp = [];
        foreach($data as $key => $item){
            if($item !== false && $item !== ''){
                $tmp[$key] = $item;
            }
        }
        $content = curl('api/1/messages', $tmp);
        if($content['status'] !== 200){
            if(!isset($content['error']) || $content['error'] === Error::old_access_token){
                $se->remove('access_token');
                $se->remove('refresh_token');
                if($req->get_param('reload', false) === false){
                    $uri = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    redirect_uri($uri . ((strpos($uri, '?') !== false) ? '&' : '?') . 'reload=true');
                }
            }else{
                $result_text .= Design::tag('div', '<p>Sorry. Something went wrong.</p>', ['class'=>'uk-panel uk-panel-box']);
            }
        }
        if($content['status'] === 200){
            foreach($content['messages'] as $message){
                $result_text .= Design::message_panel($message, $request_data);
            }
        }
    }
    return $result_text;
}
