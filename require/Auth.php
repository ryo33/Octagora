<?php

class Auth extends Model{

    const $credential_limit = 10;//minute
    const $access_key_limit = 30;//minute

    function __construct($con){
        $this->con = $con;
        $client_id = $this->get_param(CLIENT_ID, false);
        if($this->client_id === false){
            error(400, 'client_id');
        }else{
            $this->client = $this->con->fetch('SELECT * FROM `app` WHERE `client_id` = ?', $client_id);
        }
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

    function access($access_key){
        $access_key = $this->con->fetch('SELECT COUNT(`id`), `status`, `user_id` WHERE `access_key` = ?', $access_key);
        if($access_key['COUNT(`id`)'] !== '1'){
            error(400, 'wrong access_key');
        }else if($access_key['status'] === '1'){
            error(400, 'old access_key');
        }else{
            return array('user_id'=>$access_key['user_id'], 'app_id'=>$access_key['app_id']);
        }
    }

}
