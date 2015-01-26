<?php

class Application extends Model{

    const TYPE_CONFIDENTIAL = 0;
    const TYPE_PUBLIC = 1;

    const NAME_MAX = 128;
    const DESCRIPTION_MAX = 512;
    const URL_MAX = 512;
    const URL_MIN = 5;

    function __construct($con){
        parent::__construct($con);
    }

    function add_application($user_id, $args){
        $name = $args['name'];
        $redirect = $args['redirect'];
        $client_type = $args['client_type'];
        $description = $args['description'];
        $web_page = $args['web_page'];
        $name_length = mb_strlen($name);
        $redirect_length = mb_strlen($redirect);
        $description_length = mb_strlen($description);
        $web_page_length = mb_strlen($web_page_length);
        //name
        if($name_length === 0){
            return 'Application Name is too short';
        }
        if($name_length > self::NAME_MAX){
            return 'Application Name is too long';
        }
        //redirect
        if($redirect_length < URL_MIN){
            return 'Redirect URL is too short';
        }
        if($redirect_length > self::URL_MAX){
            return 'Redirect URL is too long';
        }
        //discription
        if($discription_length > self::DESCRIPTION_MAX){
            return 'Discription is too long';
        }
        //web_page
        if($web_page_length > self::URL_MAX){
            return 'Web Page URL is too long';
        }
        //TODO
    }

    function get_applications($user_id){
        return $this->con->fetchAll('SELECT `id`, `name` FROM `application` WHERE `user_id` = BINARY ?', $user_id);
    }

    function get_application($application_id, $detail=false){
        if($detail === false){
            $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `name`, `description`, `web_page` FROM `application` WHERE `application_id` = BINARY ?', [$application_id]);
        }else{
            $result = $this->con->fetch('SELECT COUNT(`id`), `id`, `name`, `description`, `web_page`, `redirect_url`, `client_type`, `client_id`, `client_secret` FROM `application` WHERE `application_id` = BINARY ?', [$application_id]);
        }
        if($result['COUNT(`id`)'] !== '1'){
            return false;
        }
        unset($result['COUNT(`id`)']);
        return $result;
    }

    function init_application($application_id){
        $this->con->execute('DELETE FROM `credential` WHERE `application_id` = ?', $application_id);
        $this->con->execute('DELETE FROM `access_token` WHERE `application_id` = ?', $application_id);
    }

}
