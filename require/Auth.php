<?php

class Auth extends Model{

    const CREDENTIAL_LIMIT = 3;//minute
    const ACCESS_KEY_LIMIT = 30;//minute

    function __construct($con){
        parent::__construct($con);
    }

    function check_client($arg){
    }

    //start authorization return code for redirect_url
    function start_auth($arg){
    }

    //create and return credential key
    function create_credential($arg){
    }

    //create and return access key and refresh key
    function do_authorization($arg){
    }

    //check authorization and return user_id
    function get_user_id($arg){
    }

    function update_key($client_id){
    }

    function access($access_key, &$user_id, &$client_id){
        $access_key = $this->con->fetch('SELECT COUNT(`id`), `status`, `user_id` WHERE `access_key` = ?', $access_key);
        if($access_key['COUNT(`id`)'] !== '1'){
            error(400, 'wrong access_key');
        }else if($access_key['status'] === '1'){
            error(400, 'old access_key');
        }else{
            $user_id = $access_key['user_id'];
            $client_id = $access_key['id'];
        }
    }

}
