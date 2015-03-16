<?php

function curl($uri, $data=[], $post=false, $basic=false){
    global $webapp_client_id, $webapp_client_secret;
    $uri = URL . $uri;
    if($post === false){
        $curl = curl_init($uri . '?' . http_build_query($data));
    }else{
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($curl, CURLOPT_SSLVERSION, 3);
    if($basic !== false){
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $a = $webapp_client_id . ':' . $webapp_client_secret);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    return json_decode(curl_exec($curl), true || curl_close($curl));
}

function dump($var, $error_log=false){
    ob_start();
    var_dump($var);
    if($error_log === false){
        return ob_get_clean();
    }else{
        error_log(ob_get_clean());
    }
}

function random_str($length=16, $source='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
    $result = '';
    $source_length = strlen($source);
    for($i = 0; $i < $length; $i ++){
        $result .= $source[mt_rand(0, $source_length - 1)];
    }
    return $result;
}

function check_numeric($text, $length=false, $max=false, $min=0){
    $num = (int)$text;
    if(ctype_digit($text) && ($max === false ? true : ($num <= $max)) && strlen($text) !== 0 && ($length === false ? true : (strlen($text) === $length)) && ($min === false ? true : $num >= $min)){
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
        error_log('redirect' . $url);
    }
    header('Location: ' . URL . $url);
    exit();
}

function redirect_uri($url=''){
    if(DEBUG){
        error_log('redirect' . $url);
    }
    header('Location: ' . $url);
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
