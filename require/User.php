<?php

class User extends Model{

    const NAME_MAX = 32;
    const PASSWORD_MAX = 64;

    function __construct($con){
        parent::__construct($con);
    }

    function is_exists($id){
        if(strlen($id) === parent::ID_LENGTH && $this->con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `id` = ?', $id) === '0'){
            return false;
        }
        return true;
    }

    function check_name($name){
        if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `name` = BINARY ?', $name) === '0'){
            return false;
        }
        return true;
    }

    function check_login($name, $password){
        $result = $this->con->fetch('SELECT COUNT(`id`), `id` , `password` FROM `user` WHERE `name` = BINARY ?', $name);
        if($result['COUNT(`id`)'] !== '1'){
            return true;
        }
        if($result['password'] === $this->hash_password($password, $result['id'])){
            return $result['id'];
        }
        return true;
    }

    function hash_password($password, $user_id){
        return sha256($password . '\\Rss' . $user_id . '(^^)\\');
    }

    function add_user($informations){
        $user_name = $informations['name'];
        $display_name = $informations['name2'];
        $password = $informations['password'];
        $password2 = $informations['password2'];
        $user_name_length = mb_strlen($user_name);
        $password_length = mb_strlen($password);
        if($this->check_name($user_name)){
            return [true, '"' . $user_name . '" is used'];
        }
        if($user_name_length < 1 || $user_name_length > self::NAME_MAX){
            return [true, 'Please enter a User Name within ' . self::NAME_MAX . ' characters maximum'];
        }
        if($password_length < 1 || $password_length > self::PASSWORD_MAX){
            return [true, 'Please enter a Password within ' . self::PASSWORD_MAX . ' characters maximum'];
        }
        if($password !== $password2){
            return [true, 'Please enter the same as "Password" to "Confirm Password"'];
        }
        $id = parent::create_id('user');
        parent::insert('user', ['id', 'name', 'password', 'display'], [$id, $user_name, $this->hash_password($password, $id), $display_name]);
        return [false, $id];
    }

    function update_user($informations){
        if(isset($informations['name']) && check_name($informations['name'])){
            return true;
        }else{
            //update
        }
    }

    function add_app($informations){
    }

    function update_app($infomations){
    }

}
