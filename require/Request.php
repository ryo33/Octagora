<?php

class Request{

    const POST = 0;
    const GET = 1;

    function __construct(){
        global $_SERVER;
        $this->uris = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->request_methid = self::POST;
        }else{
            $this->request_methid = self::GET;
        }
    }

    function get_uri($next=false){
        $result = current($this->uris);
        if($next === false){
            next($this->uris);
        }
        if($result === false){
            return false;
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
        if($this->request_methid === self::POST){
            if(isset($_POST[$name])){
                $default = $_POST[$name];
                unset($_POST[$name]);
            }
            return $default;
        }else{
            if(isset($_GET[$name])){
                $default = $_GET[$name];
                unset($_POST[$name]);
            }
            return $default;
        }
    }

    function check_param(){
        if($this->request_methid === self::POST){
            if(count($_POST) !== 0){
                $_POST = [];
                return true;
            }
        }else{
            if(count($_GET) !== 0){
                $_GET = [];
                return true;
            }
        }
        return false;
    }

}
