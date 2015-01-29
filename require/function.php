<?php

function check_numeric($text, $length=false, $max=false){
    $num = (int)$text;
    if(ctype_digit($text) && $num !== 0 && ($max === false ? true : ($num <= $max)) && strlen($text) !== 0 && ($length === false ? true : (strlen($text) === $length))){
        return false;
    }
    return true;
}

$_ = function($a){
    return $a;
};

function h($text){
    return htmlspecialchars($text, ENT_QUOTES, 'EUC-JP');
}

function stremp($text){
    if(is_array($text)){
        foreach($text as $t){
            if(stremp($t)){
                return true;
            }
        }
        return false;
    }else{
        return $text === null || strlen($text) === 0;
    }
}

function check_request($arg){
    if(stremp($arg)){
        return false;
    }
    return true;
}

function tag($text, $tag = 'p'){
    return '<' . $tag . '>' . h($text) . '</' . $tag . '>' . "\n";
}

function debug($text){
    echo tag($text);
}

function echoh($text){
    echo h($text);
}

function redirect($url=''){
    if(DEBUG){
        error_log($url);
    }
    header('Location: ' . URL . $url);
    exit();
}

function get_token($form_name){
    global $_SESSION;
    $key = 'csrf_tokens/' . $form_name;
    $tokens = isset($_SESSION[$key]) ? $_SESSION[$key] : array();
    if(count($tokens) >= 10){
        array_shift($tokens);
    }
    if(! is_array($tokens)){
        $tokens = [];
    }
    $tokens[] = $token = sha256($form_name . session_id() . microtime() . 'BOSE');
    $_SESSION[$key] = $tokens;
    return $token;
}

function check_token($form_name, $token){
    global $_SESSION;
    $key = 'csrf_tokens/' . $form_name;
    $tokens = isset($_SESSION[$key]) ? $_SESSION[$key] : array();
    if(false !== ($pos = array_search($token, $tokens, true))){
        unset($tokens[$pos]);
        $_SESSION[$key] = $tokens;
        return false;
    }
    redirect(URL);
}

function sha256($target) {
    return hash('sha256', $target);
}

function now($format=false, $option = null){
    if($option === null){
        $datetime = new DateTime('now',new DateTimeZone('GMT'));
        return $format?$datetime->format('U'):$datetime;
    }else{
        $datetime = new DateTime($option,new DateTimeZone('GMT'));
        return $format?$datetime->format('U'):$datetime;
    }
}

function delete_null_byte($value){
    if(is_string($value) === true){
        $value = str_replace("\0","",$value);
    }else if(is_array($value) === true){
        $value = array_map('delete_null_byte',$value);
    }
    return $value;
}
