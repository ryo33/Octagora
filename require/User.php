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

    function format_selects(&$needs, $other=false){
        if($other !== false){
            $other .= ', ';
        }else{
            $other = '';
        }
        $needs = explode(',', $needs);
        $selects = array();
        foreach($needs as $need){
            switch($need){
            case 'n1':
                if(in_array('name', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'name';
                break;
            case 'n2':
                if(in_array('display', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'display';
                break;
            case 'c':
                if(in_array('created', $selects)){
                    error(400, 'needs');
                }                     ;
                $selects[] = 'created';
                break;
            }
        }
        return count($selects) === 0 ? $other . '`id`' : ($other . '`id`, ' . implode(', ', array_map(function($x){return '`'.$x.'`';}, $selects)));
    }

    function format_user(&$json, $user, $needs){
        foreach($needs as $need){
            switch($need){
            case 'i':
                if(array_key_exists('i', $json)){
                    error(400, 'needs');
                }
                $json['i'] = $user['id'];
                break;
            case 'n1':
                if(array_key_exists('n1', $json)){
                    error(400, 'needs');
                }
                $json['n1'] = $user['name'];
                break;
            case 'n2':
                if(array_key_exists('n2', $json)){
                    error(400, 'needs');
                }
                $json['n2'] = $user['display'];
                break;
            case 'c':
                if(array_key_exists('c', $json)){
                    error(400, 'needs');
                }
                $json['c'] = $user['created'];
                break;
            default:
                error(400, 'needs');
            }
        }
    }

    function get_user(&$json, $id, $needs){
        $selects = $this->format_selects($needs, 'COUNT(`id`)');
        $user = $this->con->fetch('SELECT ' . $selects . ' FROM `user` WHERE `id` = BINARY ?', $id);
        if($user['COUNT(`id`)'] !== '1'){
            error(400, 'user_id');
        }
        $json['data-count'] = 1;
        $json['user'] = [];
        $this->format_user($json['user'], $user, $needs);
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

}
