<?php

class User extends Model{

    $con;
    $user_id;

    function __construct($con, $id){
        $this->con = $con;
        $this->user_id = $id;
    }

    static function is_exists($id){
        if($con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `id` = ?', $id) === '0'){
            return false;
        }
        return true;
    }

    static function check_name($name){
        if($con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `name` = ?', $name) === '0'){
            return false;
        }
        return true;
    }

    function add_user($informations){
        if(check_name($informations['name'])){
            return true;
        }else{
            //add
        }
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
