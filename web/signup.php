<?php
$tmpl->content[] = <<<HEAD
<div class="uk-vertical-align uk-text-center uk-height-1-1">
<div class="uk-vertical-align-middle" style="width: 250;">
<form class="uk-panel uk-panel-box uk-form" action="{$_(URL)}/users" method="POST">
HEAD;

switch($req->get_param(LOGIN_ACTION, false)){
case false:
    $tmpl->content[] = <<<LOGIN
<div class="uk-form-row">
    <input name="name" class="uk-width-1-1 uk-form-large" type="text" placeholder="User Name">
</div>
<div class="uk-form-row">
    <input name="password" class="uk-width-1-1 uk-form-large" type="text" placeholder="Password">
</div>
<div class="uk-form-row">
    <button class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit">Login</button>
</div>
<div class="uk-form-row uk-text-small">
    <label class="uk-float-left"><input name="remember" type="checkbox">Remember Me</label>
    <a class="uk-float-right uk-link" href="{$_(URL)}/users?action=new">Sign Up</a>
</div>
LOGIN;
    break;
case 'new':
    $tmpl->content[] = <<<NEW
NEW;
    break;
default:
    error(400, LOGIN_ACTION);
}

$tmpl->content[] = <<<FOOT
</form>
</div>
</div>
FOOT;

