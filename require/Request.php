<?php

class Request{

    const POST = 0;
    const GET = 1;

    private $params;

    function __construct(){
        global $_SERVER, $_GET, $_POST;
        $uri = $_SERVER['REQUEST_URI'];
        if(($pos = strpos($uri, '?')) !== false){
            $uri = substr($uri, 0, $pos);
        }
        $this->uris = explode('/', trim($uri, '/'));
        $this->uri_position = 0;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->request_method = self::POST;
            foreach($_POST as $key=>$param){
                $this->params[$key] = true;
            }
        }else{
            $this->request_method = self::GET;
            foreach($_GET as $key=>$param){
                $this->params[$key] = true;
            }
        }
    }

    function get_uri($get=0, $jump=1){
        $jump -= $get;
        for(;$get != 0;($get < 0) ? $get ++ : $get --){
            $this->uri_position += ($get < 0) ? -1 : 1;
        }
        $result = isset($this->uris[$this->uri_position]) ? $this->uris[$this->uri_position] : false;
        for(;$jump != 0;($jump < 0) ? $jump ++ : $jump --){
            $this->uri_position += ($jump < 0) ? -1 : 1;
        }
        return $result;
    }

    function check_uri(){
        $result = isset($this->uris[$this->uri_position]) ? $this->uris[$this->uri_position] : false;
        $this->uri_position = count($this->uris) + 1;
        if($result === false){
            return false;
        }
        return true;
    }

    function get_param($name, $default=false){
        if($this->request_method === self::POST){
            if(isset($_POST[$name])){
                $default = $_POST[$name];
                unset($this->params[$name]);
            }
            return $default;
        }else{
            if(isset($_GET[$name])){
                $default = $_GET[$name];
                unset($this->params[$name]);
            }
            return $default;
        }
    }

    function check_param(){
        if(count($this->params) !== 0){
            $this->params = [];
            return true;
        }
        return false;
    }

}
