<?php

$tmpl->content[] = <<<HEAD
<div class="uk-vertical-align uk-text-center uk-height-1-1">
<div class="uk-vertical-align-middle" style="width: 320;">
HEAD;

//$action is declared
switch($action){
case false:
case 'login':
    $tmpl->content[] =
        Design::login_form($req->get_param('message', ''));
    break;
case 'new':
    $tmpl->content[] =
        Design::form_start('signup', 'users', 'POST') .
        Design::form_incorrect($req->get_param('message', false)) .
        Design::form_input([
            'label'=>'User Name',
            'name'=>'name',
            'required'=>true,
            'type'=>'text'
        ]) .
        Design::form_input([
            'label'=>'Sub User Name',
            'name'=>'name2',
            'type'=>'text'
        ]) .
        Design::form_input([
            'label'=>'Password',
            'name'=>'password',
            'required'=>true,
            'type'=>'password'
        ]) .
        Design::form_input([
            'label'=>'Confirm Password',
            'name'=>'password2',
            'required'=>true,
            'type'=>'password'
        ]) .
        Design::form_input([
            'name'=>'action',
            'value'=>'new',
            'type'=>'hidden'
        ]) .
        Design::form_submit('Sign up') .
        Design::form_end();
    break;
default:
    error(400, ACTION);
}

$tmpl->content[] = <<<FOOT
</div>
</div>
FOOT;

