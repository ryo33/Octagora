<?php

if(strpos($_SERVER['HTTP_HOST'], 'www.') === 0){
    $url = 'https://' . substr($_SERVER['HTTP_HOST'], 4) . $_SERVER['REQUEST_URI'];
    header('Location: ' . $url, true, 301);
    exit();
}
if(isset($ssl) && $ssl != false && empty($_SERVER['HTTPS'])){
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit();
}
