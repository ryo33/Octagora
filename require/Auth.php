<?php

class Auth extends Model{

    const CREDENTIAL_LIMIT = 180;//3 minutes
    const AT_LIMIT = 1800;//30 minutes

    const TOKEN_LENGTH = 32;

    const AT_CODE = '0';
    const AT_TOKEN = '1';
    const AT_PASSWORD = '2';
    const AT_CLIENT = '3';

    public static $token_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

    function __construct($con){
        parent::__construct($con);
    }

    function create_access_token($args){
        switch($args['type']){
        case self::AT_CODE:
            break;
        case self::AT_TOKEN:
            break;
        case self::AT_PASSWORD:
            parent::insert('credential', ['application_id', 'user_id'], [$args['application_id'], $args['user_id']]);
            $access_token = $this->create_token('access_token');
            $refresh_token = $this->create_token('refresh_token');
            parent::insert('access_token', ['type', 'application_id', 'access_token', 'refresh_token', 'user_id'],
                [$args['type'], $args['application_id'], $access_token, $refresh_token, $args['user_id']]);
            return [$access_token, $refresh_token];
            break;
        case self::AT_CLIENT:
            $access_token = $this->create_token('access_token');
            parent::insert('access_token', ['type', 'application_id', 'access_token'],
                [$args['type'], $args['application_id'], $access_token]);
            return $access_token;
        }
    }

    function create_token($column){
        do{
            $result = '';
            for($i = 0; $i < self::ID_LENGTH; $i ++){
                $result .= self::$token_characters[mt_rand(0, strlen(self::$token_characters) - 1)];
            }
        }while($this->con->fetchColumn('SELECT COUNT(`id`) FROM `access_token` WHERE `' . $column . '` = BINARY ?', $result) !== '0');
        return $result;
    }

    //check authorization and return user_id
    function get_user_id($arg){
    }

    function update_key($client_id){
    }

    function access($access_token, &$user_id, &$client_id){
        global $now;
        $access = $this->con->fetch('SELECT COUNT(`id`), `id`, `type`, `user_id`, `application_id`, `refresh_token`, `created` FROM `access_token` WHERE `access_token` = BINARY ?', $access_token);
        $created = new DateTime($access['created'], new DateTimeZone('GMT'));
        if($access['COUNT(`id`)'] !== '1'){
            error(400, 'wrong access_token');
        }else if((int)$now->format('U') - (int)$created->format('U') > Auth::AT_LIMIT){
            $this->con->execute('DELETE FROM `access_token` WHERE `id` = ?', $access['id']);
            error(400, 'old access_token');
        }else{
            switch($access['type']){
            case self::AT_CODE: exit();
            case self::AT_TOKEN:
                exit();
            case self::AT_PASSWORD:
                $user_id = $access['user_id'];
                $client_id = $access['application_id'];
                return;
            case self::AT_CLIENT:
                $user_id = false;
                $client_id = $access['application_id'];
                return;
            }
        }
    }

}
