<?php

$tmpl->content[] = Design::tag('h1', 'Octagora API') . Design::tag('h2', 'Versions');
$tmpl->content[] =
    Design::_list('ul',
        [
            Design::link('api/1', 'Octagora API v1', 'uk-button uk-button-primary')
        ]
    );
