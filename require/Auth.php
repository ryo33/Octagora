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

    function update_access_token($args){
        switch($args['type']){
        case self::AT_CODE:
            break;
        case self::AT_TOKEN:
            break;
        case self::AT_PASSWORD:
            $access = $this->get_access_token($access_token);
            if($this->check_at_limit($access['created'])){
                $access_token = $this->create_token('access_token');
                $refresh_token = $this->create_token('refresh_token');
                parent::update('access_token', $access['id'], ['access_token', 'refresh_token'], [$access_token, $refresh_token]);
                return [$access_token, $refresh_token];
            }else{
                return true;
            }
        case self::AT_CLIENT:
            return true;
        }
    }

    function get_access_token($access_token){
        $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `type`, `user_id`, `application_id`, `refresh_token`, `created` FROM `access_token` WHERE `access_token` = BINARY ?', $access_token);
        if($result['COUNT(`id`)'] !== '1'){
            return true;
        }
        return $result;
    }

    function create_access_token($args){
        $remove_access_token = function($application_id, $user_id){
            $this->con->execute('delete from `access_token` where `application_id` = BINARY ? AND `user_id` = BINARY ?', [$application_id, $user_id]);
        };
        switch($args['type']){
        case self::AT_CODE:
            break;
        case self::AT_TOKEN:
            break;
        case self::AT_PASSWORD:
            if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `credential` WHERE `application_id` = BINARY ? AND `user_id` = BINARY ?', [$args['application_id'], $args['user_id']]) === '0'){
                parent::insert('credential', ['application_id', 'user_id'], [$args['application_id'], $args['user_id']]);
            }
            $access_token = $this->create_token('access_token');
            $refresh_token = $this->create_token('refresh_token');
            $remove_access_token($args['application_id'], $args['user_id']);
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

    function check_at_limit($created){
        global $now;
        $created = new DateTime($created, new DateTimeZone('GMT'));
        return (int)$now->format('U') - (int)$created->format('U') > Auth::AT_LIMIT;
    }

    function access($access_token, &$user_id, &$client_id){
        $access = $this->get_access_token($access_token);
        if($access === true){
            error(400, 'wrong access_token');
        }else{
            switch($access['type']){
            case self::AT_CODE: exit();
            case self::AT_TOKEN:
                exit();
            case self::AT_PASSWORD:
                if($this->check_at_limit($access['created'])){
                    error(400, 'old access_token');
                }
                $user_id = $access['user_id'];
                $client_id = $access['application_id'];
                return;
            case self::AT_CLIENT:
                if($this->check_at_limit($access['created'])){
                    $this->con->execute('DELETE FROM `access_token` WHERE `id` = ?', $access['id']);
                    error(400, 'old access_token');
                }
                $user_id = false;
                $client_id = $access['application_id'];
                return;
            }
        }
    }

}
