<?php

class Template{

    function __construct(){
        $this->site_name = 'Octagora';
        $this->title = '';
        $this->content = [];
        $this->description = <<<'DESC'
This is a website which post texts with tags.
DESC;
        $this->navbar = '';
    }

    function add($text){
        $this->content[] = $text;
    }

    function add_navbar($text){
        $this->navbar .= $text;
    }

    function display(){
        global $_;
        if(strlen($this->title) !== 0){
            $this->title .= ' - ';
        }
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
<div class="uk-container uk-container-center uk-margin-top uk-margin-large-botoom">
<nav class="uk-navbar uk-margin-large-bottom">
    <ul class="uk-navbar-nav">
        <li><a class="uk-navbar-brand" href="{$_(URL)}">Octagora</a></li>
        <li><a href="{$_(URL)}/api">API</a></li>
    {$this->navbar}
    </ul>
</nav>
HEAD;
        $display[] = implode('', $this->content);
        $display[] = <<<FOOT
</div>
</body>
</html>
FOOT;
        return implode('', $display);
    }

}
