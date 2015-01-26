<?php

class Session{

    function __construct(){
        session_start();
        $this->is_login = false;
        $this->user_id = false;
    }

    function get($name, $default=false){
        if(isset($_SESSION[$name])){
            return $_SESSION[$name];
        }
    }

    function set($name, $value){
        $_SESSION[$name] = $value;
    }

    function remove($name=false){
        if($name === false){
            $_SESSION = array();
        }else{
            unset($_SESSION[$name]);
        }
    }

    function regenerate(){
        session_regenerate_id(true);
    }

    function login($user_id){
        $this->set('_login_', true);
        $this->set('user_id', $user_id);
        $this->regenerate();
    }

    function logout(){
        $this->set('_login_', false);
        $this->regenerate();
    }

    function check_login($user){
        $tmp = $this->get('_login_', false) ? $this->get('user_id', false) : false;
        if($tmp !== false && $user->is_exists($tmp)){
            $this->is_login = true;
            $this->user_id = $tmp;
            return true;
        }else{
            $this->is_login = false;
            $this->logout();
            return false;
        }
    }

}
