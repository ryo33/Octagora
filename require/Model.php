<?php

class Model{

    $con;
    $id_length = 16;
    $id_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$%&()~=~|{}`*+_?><,./;]:[@^-\'"\\';

    function __construct($con){
        $this->con = $con;
    }

    function create_id($table){
        do{
            $result = '';
            for($i = 0; $i < $this->id_length; $i ++){
                $result .= $this->id_characters[mt_rand(0, strlen($this->id_characters) - 1)];
            }
        }while($this->con->fetch('SELECT COUNT(`id`) FROM `' . $table . '` WHERE `id` = ?', $result) === '1');
        return $result;
    }

}
