<?php

require 'EasySql.php';

class Database{

    $id_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$%&()~=~|{}`*+_?><,./;]:[@^-\'"\\';
    $tag_types = array(
        'normal',
        'year', 'month', 'day',
        'hour', 'minute', 'second',
        'by_user', 'to_user', 'user',
        'message', 'to_message',
        'length',
        'system'
    );
    $tag_types_key = array();
    $allow_post_tag_types = array(
        $tag_types_key['to_user'],
        $tag_types_key['user'],
        $tag_types_key['to_message'],
        $tag_types_key['message'],
        $tag_types_key['normal'],
    );
    $id_length = 16;
    $tag_max = 256;
    $text_max = 65536;

    $con;

    function __construct($database_dsn, $database_username, $database_password){
        $this->con = new EasySql($database_dsn, $database_username, $database_password);
        foreach($this->tag_types as $i=>$tag_type){
            $this->tag_types_key[$tag_type] = $i;
        }
    }
