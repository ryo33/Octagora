<?php

class Application extends Model{

    const TYPE_CONFIDENTIAL = '0';
    const TYPE_PUBLIC = '1';

    const NAME_MAX = 128;
    const DESCRIPTION_MAX = 512;
    const URL_MAX = 512;
    const URL_MIN = 5;

    const CLIENT_ID_LENGTH = 32;
    const CLIENT_SECRET_LENGTH = 32;

    public static $client_id_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

    function __construct($con){
        parent::__construct($con);
    }

    function check_args(&$args){
        $name_length = mb_strlen($args['name']);
        $redirect_length = mb_strlen($args['redirect']);
        $description_length = mb_strlen($args['description']);
        $web_length = mb_strlen($args['web']);
        //name
        if($name_length === 0){
            return 'Application Name is too short';
        }
        if($name_length > self::NAME_MAX){
            return 'Application Name is too long';
        }
        //redirect
        if($redirect_length < self::URL_MIN){
            return 'Redirect URL is too short';
        }
        if($redirect_length > self::URL_MAX){
            return 'Redirect URL is too long';
        }
        //discription
        if($description_length > self::DESCRIPTION_MAX){
            return 'Discription is too long';
        }
        //web_page
        if($web_length > self::URL_MAX){
            return 'Web Page URL is too long';
        }
        //type
        switch($args['type']){
        case 'confidencial':
            $args['type'] = self::TYPE_CONFIDENTIAL;
            break;
        case 'public':
            $args['type'] = self::TYPE_PUBLIC;
            break;
        default:
            return 'Application Type is incorrect';
        }
        return false;
    }

    function add_application($user_id, $args){
        global $_SERVER;
        $check = $this->check_args($args);
        if($check !== false){
            return [true, $check];
        }
        $name = $args['name'];
        $redirect = $args['redirect'];
        $type = $args['type'];
        $description = $args['description'];
        $web = $args['web'];

        $id = parent::create_id('application');
        $client_id = $this->create_client_id();
        $client_secret = $this->create_client_secret();
        parent::insert('application', ['id', 'name', 'redirect', 'client_type', 'client_id', 'client_secret', 'description', 'web', 'user_id', 'ip'],
            [$id, $name, $redirect, $type, $client_id, $client_secret, $description, $web, $user_id, ip2long($_SERVER['REMOTE_ADDR'])]);
        return [false, $id];
    }

    function edit_application($application_id, $user_id, $args){
        global $_SERVER;
        if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `application` WHERE `id` = BINARY ? AND `user_id` = BINARY ?', [$application_id, $user_id]) !== '1'){
            return true;
        }
        $check = $this->check_args($args);
        if($check !== false){
            return $check;
        }
        $name = $args['name'];
        $redirect = $args['redirect'];
        $type = $args['type'];
        $description = $args['description'];
        $web = $args['web'];

        $this->con->update('application', $application_id, ['name', 'redirect', 'client_type', 'description', 'web', 'ip'],
            [$name, $redirect, $type, $description, $web, ip2long($_SERVER['REMOTE_ADDR'])]);
        return false;
    }


    function create_client_id(){
        do{
            $result = '';
            for($i = 0; $i < self::CLIENT_ID_LENGTH; $i ++){
                $result .= self::$client_id_characters[mt_rand(0, strlen(self::$client_id_characters) - 1)];
            }
        }while($this->con->fetchColumn('SELECT COUNT(`id`) FROM `application` WHERE `client_id` = BINARY ?', $result));
        return $result;
    }

    function create_client_secret(){
        $result = '';
        for($i = 0; $i < self::CLIENT_SECRET_LENGTH; $i ++){
            $result .= self::$client_id_characters[mt_rand(0, strlen(self::$client_id_characters) - 1)];
        }
        return $result;
    }

    function get_applications($user_id){
        return $this->con->fetchAll('SELECT `id`, `name`, `client_type`, `description`, `web` FROM `application` WHERE `user_id` = BINARY ?', $user_id);
    }

    function get_application($application_id, $detail=false){
        if($detail === false){
            $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `name`, `user_id`, `description`, `web` FROM `application` WHERE `id` = BINARY ?', [$application_id]);
        }else{
            $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `name`, `user_id`, `description`, `web`, `redirect`, `client_type`, `client_id`, `client_secret` FROM `application` WHERE `id` = BINARY ?', [$application_id]);
        }
        if($result['COUNT(`id`)'] !== '1'){
            return false;
        }
        unset($result['COUNT(`id`)']);
        return $result;
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
            case 'n':
                if(in_array('name', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'name';
                break;
            case 'd':
                if(in_array('description', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'description';
                break;
            case 'w':
                if(in_array('web', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'web';
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

    function format_application(&$json, $application, $needs){
        foreach($needs as $need){
            switch($need){
            case 'i':
                if(array_key_exists('i', $json)){
                    error(400, 'needs');
                }
                $json['i'] = $application['id'];
                break;
            case 'n':
                if(array_key_exists('n', $json)){
                    error(400, 'needs');
                }
                $json['n'] = $application['name'];
                break;
            case 'd':
                if(array_key_exists('d', $json)){
                    error(400, 'needs');
                }
                $json['d'] = $application['description'];
                break;
            case 'w':
                if(array_key_exists('w', $json)){
                    error(400, 'needs');
                }
                $json['w'] = $application['web'];
                break;
            case 'c':
                if(array_key_exists('c', $json)){
                    error(400, 'needs');
                }
                $json['c'] = $application['created'];
                break;
            default:
                error(400, 'needs');
            }
        }
    }

    function get_application_json(&$json, $id, $needs){
        $selects = $this->format_selects($needs, 'COUNT(`id`)');
        $application = $this->con->fetch('SELECT ' . $selects . ' FROM `application` WHERE `id` = BINARY ?', $id);
        if($application['COUNT(`id`)'] !== '1'){
            error(400, 'application_id');
        }
        $json['data-count'] = 1;
        $json['application'] = [];
        $this->format_application($json['application'], $application, $needs);
    }

    function is_exists($application_id){
        return $this->con->fetchColumn('SELECT COUNT(`id`) FROM `application` WHERE `id` = BINARY ?', $application_id) === '1';
    }

    function init_application($application_id){
        $this->con->execute('DELETE FROM `credential` WHERE `application_id` = ?', $application_id);
        $this->con->execute('DELETE FROM `access_token` WHERE `application_id` = ?', $application_id);
    }

    function check_client($client_id, $client_secret){
        $client = $this->check_client_id($client_id);
        if($client === true || $client_secret !== $client['client_secret']){
            return true;
        }
        return $client;
    }

    function check_client_id($client_id){
        $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `redirect`, `client_type`, `client_id`, `client_secret` FROM `application` WHERE `client_id` = BINARY ?', [$client_id]);
        if($result['COUNT(`id`)'] !== '1'){
            return true;
        }
        unset($result['COUNT(`id`)']);
        return $result;
    }

}
