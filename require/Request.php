<?php

class Request{

    const POST = 0;
    const GET = 1;

    function __construct(){
        global $_SERVER;
        $uri = $_SERVER['REQUEST_URI'];
        //TODO
        if(($pos = strpos($uri, '?')) !== false){
            $uri = substr($uri, 0, $pos);
        }
        $this->uris = explode('/', trim($uri, '/'));
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->request_method = self::POST;
        }else{
            $this->request_method = self::GET;
        }
    }

    function get_uri($get=0, $jump=1){
        $jump -= $get;
        for(;$get != 0;$get < 0 ? $get ++ : $get --){
            $get < 0 ? prev($this->uris) : next($this->uris);
        }
        $result = current($this->uris);
        for(;$jump != 0;$jump < 0 ? $jump ++ : $jump --){
            $jump < 0 ? prev($this->uris) : next($this->uris);
        }
        return $result;
    }

    function check_uri(){
        $result = current($this->uris);
        end($this->uris);
        next($this->uris);
        if($result === false){
            return false;
        }
        return true;
    }

    function check_login(){
    }

    function get_param($name, $default=false){
        if($this->request_method === self::POST){
            if(isset($_POST[$name])){
                $default = $_POST[$name];
                unset($_POST[$name]);
            }
            return $default;
        }else{
            if(isset($_GET[$name])){
                $default = $_GET[$name];
                unset($_GET[$name]);
            }
            return $default;
        }
    }

    function check_param(){
        if($this->request_method === self::POST){
            if(isset($_POST) && count($_POST) !== 0){
                $_POST = [];
                return true;
            }
        }else{
            if(isset($_GET) && count($_GET) !== 0){
                $_GET = [];
                return true;
            }
        }
        return false;
    }

}
