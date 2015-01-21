<?php

class Model{

    const ID_LENGTH = 16;

    function __construct($con){
        global $_SERVER;
        $this->con = $con;
        $this->id_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$%&()~=~|{}`*+_?><,./;]:[@^-\'"\\';
    }

    function create_id($table){
        do{
            $result = '';
            for($i = 0; $i < self::ID_LENGTH; $i ++){
                $result .= $this->id_characters[mt_rand(0, strlen($this->id_characters) - 1)];
            }
        }while($this->con->fetch('SELECT COUNT(`id`) FROM `' . $table . '` WHERE `id` = ?', $result) === '1');
        return $result;
    }

    function insert($table, $columns, $values){
        global $now;
        if(in_array('created', $columns, true) === false){
            $columns[] = 'created';
            $values[] = $now->format('Y:m:d H:i:s');
        }
        return $this->con->insert($table, $columns, $values, true);
    }

}
