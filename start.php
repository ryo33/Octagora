<?php

if(strpos($_SERVER['HTTP_HOST'], 'www.') === 0){
    $url = 'http://' . substr($_SERVER['HTTP_HOST'], 4) . $_SERVER['REQUEST_URI'];
    header('Location: ' . $url, true, 301);
    exit();
}
