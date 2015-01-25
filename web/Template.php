<?php

class Template{

    function __construct(){
        $this->site_name = 'Octagora';
        $this->title = '';
        $this->content = [];
        $this->description = <<<'DESC'
This is a website which post texts with tags.
DESC;
    }

    function display(){
        global $_;
        $display[] = <<<HEAD
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{$this->title}{$this->site_name}</title>
<meta charset="utf-8">
<meta name="description" content="{$this->description}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{$_(URL)}/uikit.almost-flat.min.css">
<script src="{$_(URL)}/jquery.js"></script>
<script src="{$_(URL)}/uikit.min.js"></script>
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

}
