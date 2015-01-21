<?php

class Response{

    function __construct(){
        $this->site_name = 'Octagora';
        $this->title = '';
        $this->content = [];
        $this->description = <<<'DESC'
This is a website which post texts with tags.
DESC;
    }

    function display(){
        echo implode('', $this->content);
    }

}
