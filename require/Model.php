<?php

class Model{

    const ID_LENGTH = 16;
    public static $id_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$%&()~=~|{}`*+_?><,./;]:[@^-\'"\\';

    function __construct($con){
        global $_SERVER;
        $this->con = $con;
    }

    function create_id($table, $column='id'){
        do{
            $result = '';
            for($i = 0; $i < self::ID_LENGTH; $i ++){
                $result .= self::$id_characters[mt_rand(0, strlen(self::$id_characters) - 1)];
            }
        }while($this->con->fetch('SELECT COUNT(`id`) FROM `' . $table . '` WHERE `' . $column . '` = BINARY ?', $result) === '1');
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
