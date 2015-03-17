<?php

$show_desc = 'Show Description';
$hide_desc = 'Hide';
$go_to_agora = 
    Design::tag('a', 'GO TO AGORA', ['class'=>'uk-button uk-button-primary uk-button-large uk-margin-small', 'href'=>URL . 'messages']);
$tmpl->add(
    $go_to_agora .
    <<<HTML
<h2>Welcome to Octagora!<h2>
<h4>Octagora is an Agora to communicate texts with tags</h4>
<h4>You can post texts with tags and search texts by tags</h4>
<h4>Let's post texts! You can post without login</h4>
HTML
.
    $go_to_agora .
    Design::tag('div',
        '<h3>Description about Tags</h3><ul>' .
        "<li>First, escape '*', '.', '!', '-', '(', ')', ':', '/' in tag text by '/' like '/*', '/.', '/!', '/-', '/(', '/)', '/:', '//'.</li>" .
        "<li>Second, delimit tags by '*'(AND), '.'(OR), '!'(NOT), '-'(XOR).</li>" .
        "<li>Third, You can only use '*'(AND) to delimit tags when posting.</li>" .
        '<li>Example: game*screenshot</li>' .
        '</ul>'
        , ['class'=>'uk-panel uk-panel-box']) .
    $go_to_agora
    );
