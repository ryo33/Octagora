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
<link rel="apple-touch-icon-precomposed" sizes="57x57" href="{$_(URL)}icon/apple-touch-icon-57x57.png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$_(URL)}icon/apple-touch-icon-114x114.png" />
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$_(URL)}icon/apple-touch-icon-72x72.png" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="{$_(URL)}icon/apple-touch-icon-144x144.png" />
<link rel="apple-touch-icon-precomposed" sizes="60x60" href="{$_(URL)}icon/apple-touch-icon-60x60.png" />
<link rel="apple-touch-icon-precomposed" sizes="120x120" href="{$_(URL)}icon/apple-touch-icon-120x120.png" />
<link rel="apple-touch-icon-precomposed" sizes="76x76" href="{$_(URL)}icon/apple-touch-icon-76x76.png" />
<link rel="apple-touch-icon-precomposed" sizes="152x152" href="{$_(URL)}icon/apple-touch-icon-152x152.png" />
<link rel="icon" type="image/png" href="{$_(URL)}icon/favicon-196x196.png" sizes="196x196" />
<link rel="icon" type="image/png" href="{$_(URL)}icon/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/png" href="{$_(URL)}icon/favicon-32x32.png" sizes="32x32" />
<link rel="icon" type="image/png" href="{$_(URL)}icon/favicon-16x16.png" sizes="16x16" />
<link rel="icon" type="image/png" href="{$_(URL)}icon/favicon-128.png" sizes="128x128" />
<meta name="application-name" content="&nbsp;"/>
<meta name="msapplication-TileColor" content="#FFFFFF" />
<meta name="msapplication-TileImage" content="{$_(URL)}icon/mstile-144x144.png" />
<meta name="msapplication-square70x70logo" content="{$_(URL)}icon/mstile-70x70.png" />
<meta name="msapplication-square150x150logo" content="{$_(URL)}icon/mstile-150x150.png" />
<meta name="msapplication-wide310x150logo" content="{$_(URL)}icon/mstile-310x150.png" />
<meta name="msapplication-square310x310logo" content="{$_(URL)}icon/mstile-310x310.png" />
</head>
<body>
<div class="uk-container uk-container-center uk-margin-top uk-margin-large-botoom">
<nav class="uk-navbar uk-margin-large-bottom">
    <ul class="uk-navbar-nav">
        <li><a class="uk-navbar-brand" href="{$_(URL)}"><img src="{$_(URL)}icon/favicon-128.png" style="height: 100%; vertical-align: top; " /></a></li><li><a href="{$_(URL)}">Octagora</a></li>
        <li><a href="{$_(URL)}api">API</a></li>
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
