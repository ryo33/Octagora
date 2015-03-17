<?php
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
    ];
    if($tags === ''){
        $tags_form['placeholder'] = 'Tags';
    }else{
        $tags_form['value'] = $req->get_param(POST_TAGS, '');
    }

    $tmpl->add(
        Design::form_start(false, '', 'GET') .
        Design::tag('input', '', ['id'=>'reloadform', 'name'=>FORM_TAGS, 'class'=>'uk-form uk-form-large uk-width-5-6', 'style'=>'height: 100%;', 'value'=>$ts]) .
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
            Script::switch_id('postform', 'displaypostform', $show, $hide, $postform === 'true')
        );
    $tmpl->add(get_messages(get_access_token(), $request_data));
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
