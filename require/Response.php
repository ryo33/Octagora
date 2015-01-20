<?php

class Response{


    const JSON = 0;
    const HTML = 1;

    function __construct($display_type=self::JSON){
        $this->display_type = $display_type;
        $this->site_name = 'Octagora';
        $this->title = '';
        $this->content = [];
        $this->description = <<<'DESC'
This is a website which post texts with tags.
DESC;
    }

    function display(){
        switch($this->display_type){
        case self::HTML:
            echo $this->display_html();
            break;
        case self::JSON:
            echo $this->display_json();
            break;
        }
    }

    function display_html(){
        $display[] = <<<HEAD
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{$this->title}{$this->site_name}</title>
<meta charset="utf-8">
<meta name="description" content="{$this->description}">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
HEAD;
        $display[] = implode('', $this->content);
        $display[] = <<<FOOT
</body>
</html>
FOOT;
        return implode('', $display);
    }

    function display_json(){
        header('Content-Type: application/json');
        echo json_encode($this->content);
    }

}
