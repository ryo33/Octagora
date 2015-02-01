<?php

class Auth extends Model{

    const CREDENTIAL_LIMIT = 180;//3 minutes
    const AT_LIMIT = 1800;//30 minutes

    const TOKEN_LENGTH = 32;

    const GT_MIN = 0;//min
    const AT_CODE = '0';
    const AT_TOKEN = '1';
    const AT_PASSWORD = '2';
    const AT_CLIENT = '3';
    const GT_MAX = 3;//max

    const AC_NOT_AVAILABLE = '0';
    const AC_AVAILABLE = '1';
    const AC_USED = '2';

    public static $token_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

    function __construct($con){
        parent::__construct($con);
    }

    function create_auth_code($application_id, $state=false){
        $token = $this->create_auth_code_token($application_id);
        if($state !== false){
            parent::insert('auth_code', ['token', 'state', 'status', 'application_id'], [$token, $state, self::AC_NOT_AVAILABLE, $application_id]);
        }else{
            parent::insert('auth_code', ['token', 'status', 'application_id'], [$token, self::AC_NOT_AVAILABLE, $application_id]);
        }
        return $token;
    }

    function update_auth_code_token($application_id, $token){
        $result = $con->fetch($con->select('auth_code', 'COUNT(`id`), `id`, `application_id`', ['token', 'application_id', 'status']), [$token, $application_id, self::AC_NOT_AVAILABLE]);
        if($result['COUNT(`id`)'] !== '1'){
            exit();
        }
        $token = $this->create_auth_code_token($application_id);
        $con->update('auth_code', ['token'], $token);
        return [$token, $result['id']];
    }

    function get_auth_code($code){
        //TODO
    }

    function create_auth_code_token($application_id){
        do{
            $token = random_str();
        }while($con->get_count('auth_code', ['token'=>$token, 'application_id'=>$application_id]) !== '0');
        return $token;
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

    function create_credential($application_id, $user_id){
        if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `credential` WHERE `application_id` = BINARY ? AND `user_id` = BINARY ?', [$application_id, $user_id]) === '0'){
            parent::insert('credential', ['application_id', 'user_id'], [$application_id, $user_id]);
        }
    }

    function check_credential($application_id, $user_id){
        if($con->get_count('credential', ['application_id'=>$app['id'], 'user_id'=>$user->user_id]) === '1'){
            return true;
        }
        return false;
    }

    function create_access_token($args){
        $remove_access_token = function($application_id, $user_id){
            $this->con->execute('DELETE FROM `access_token` WHERE `application_id` = BINARY ? AND `user_id` = BINARY ?', [$application_id, $user_id]);
        };
        switch($args['type']){
        case self::AT_CODE:
            break;
        case self::AT_TOKEN:
            break;
        case self::AT_PASSWORD:
            $this->create_credential($args['application_id'], $args['user_id']);
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

    function access($access_token, &$auth_info){
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
                $auth_info = $access;
                return;
            case self::AT_CLIENT:
                if($this->check_at_limit($access['created'])){
                    $this->con->execute('DELETE FROM `access_token` WHERE `id` = ?', $access['id']);
                    error(400, 'old access_token');
                }
                $auth_info = $access;
                $auth_info['user_id'] = false;
                return;
            }
        }
    }

}
