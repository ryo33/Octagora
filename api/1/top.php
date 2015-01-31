<?php

$required = Design::tag('span', '◯', ['class'=>'uk-text-large']);

$tmpl->add(
    Design::tag('h1', 'Octagora API v1') .
    Design::tag('ol',
        Design::tag(['li', 'h2'], 'Introduction', ['id'=>'introduction']) .
        Design::tag('div',
            Design::tag('p', 'Octagora is a service saving texts with tags') .
            Design::tag('p', 'Octagora API v1 URL is "https://octagora.com/api/1"')
        ) .
        Design::tag(['li', 'h2'], 'OAuth', ['id'=>'oauth']) .
        Design::tag('div', ''
        ) .
        Design::tag(['li', 'h2'], 'Reference', ['id'=>'reference']) .
        Design::tag(['div', 'ul'],
            Design::tag(['li', 'h3'], 'Messages' . Design::tag('span', '/messages', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
            Design::tag(['div', 'ul'],
                Design::tag(['li', 'div'],
                    Design::tag('h4', Design::tag('span', 'GET', ['class'=>'uk-badge uk-badge-success uk-text-large', 'style'=>'margin-right: 5px']) . 'Get Messages' . Design::tag('span', '/', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
                    Design::table(['Parameter', 'Required', 'Description', 'Default'],
                        [
                            ['access_token', $required, Design::link('api/1#oauth', 'Detail'), '-'],
                            ['ts', '', '(tags) ' . Design::link('api/1#tags', 'Detail'), '""'],
                            ['o', '', '(order) "asc" to ascending order, "desc" to descending order', '"desc"'],
                            ['m', '', '(max) Maximum message number', "100"],
                            ['n', '', '(need) Select "i"(id) "t"(text) "c"(created) "ts"(tags), comma delimited', '"i,t"'],
                            ['tn', '', '(tagneed) Select ' . Design::link('api/1#qualifier', 'tag qualifiers') . ', comma delimited', '"normal,by_user,to_user,user,message,to_message"']
                        ], 'Parameters', 'uk-table'
                    ) . Design::tag('p', 'Example: curl "https://octagora.com/api/1/messages?access_token=ACCESS_TOKEN&ts=dog.cat"')
                ) .
                Design::tag(['li', 'div'],
                    Design::tag('h4', Design::tag('span', 'GET', ['class'=>'uk-badge uk-badge-success uk-text-large', 'style'=>'margin-right: 5px']) . 'Get a Message' . Design::tag('span', '/USER_ID', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
                    Design::table(['Parameter', 'Required', 'Description', 'Default'],
                        [
                            ['access_token', $required, Design::link('api/1#oauth', 'Detail'), '-'],
                            ['n', '', '(need) Select "i"(id) "t"(text) "c"(created) "ts"(tags), comma delimited', '"i,t"'],
                            ['tn', '', '(tagneed) Select ' . Design::link('api/1#qualifier', 'tag qualifiers') . ', comma delimited', '"normal,by_user,to_user,user,message,to_message"']
                        ], 'Parameters', 'uk-table'
                    ) . Design::tag('p', 'Example: curl "https://octagora.com/api/1/messages/MESSAGE_ID?access_token=ACCESS_TOKEN"')
                ) .
                Design::tag(['li', 'div'],
                    Design::tag('h4', Design::tag('span', 'POST', ['class'=>'uk-badge uk-badge-success uk-text-large', 'style'=>'margin-right: 5px']) . 'Post a Message' . Design::tag('span', '/', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
                    Design::table(['Parameter', 'Required', 'Description', 'Default'],
                        [
                            ['access_token', $required, Design::link('api/1#oauth', 'Detail'), '-'],
                            ['t', $required, '(text) message text', '-'],
                            ['ts', '', '(tags) ' . Design::link('api/1#tags', 'Detail'), '""'],
                        ], 'Parameters', 'uk-table'
                    ) . Design::tag('p', 'Example: curl "https://octagora.com/api/1/messages" --data "access_token=ACCESS_TOKEN&t=Which do you prefer, cats or dogs?&ts=dog*cat"')
                )
            ) .
            Design::tag(['li', 'h3'], 'Users' . Design::tag('span', '/users', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
            Design::tag(['div', 'ul'],
                Design::tag(['li', 'div'],
                    Design::tag('h4', Design::tag('span', 'GET', ['class'=>'uk-badge uk-badge-success uk-text-large', 'style'=>'margin-right: 5px']) . 'Get a User' . Design::tag('span', '/USER_ID', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
                    Design::table(['Parameter', 'Required', 'Description', 'Default'],
                        [
                            ['access_token', $required, Design::link('api/1#oauth', 'Detail'), '-'],
                            ['n', '', '(need) Select "i"(id) "n1"(name1) "n2"(name2) "c"(created), comma delimited', '"i,n1,n2"'],
                        ], 'Parameters', 'uk-table'
                    ) . Design::tag('p', 'Example: curl "https://octagora.com/api/1/users/USER_ID"')
                )
            ) .
            Design::tag(['li', 'h3'], 'Applications' . Design::tag('span', '/application', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
            Design::tag(['div', 'ul'],
                Design::tag(['li', 'div'],
                    Design::tag('h4', Design::tag('span', 'GET', ['class'=>'uk-badge uk-badge-success uk-text-large', 'style'=>'margin-right: 5px']) . 'Get a Application' . Design::tag('span', '/APPLICATION_ID', ['class'=>'uk-badge uk-badge-primary uk-text-large', 'style'=>'margin-left: 5px;'])) .
                    Design::table(['Parameter', 'Required', 'Description', 'Default'],
                        [
                            ['access_token', $required, Design::link('api/1#oauth', 'Detail'), '-'],
                            ['n', '', '(need) Select "i"(id) "n"(name) "d"(description) "w"(web page) "c"(created), comma delimited', '"i,n"'],
                        ], 'Parameters', 'uk-table'
                    ) . Design::tag('p', 'Example: curl "https://octagora.com/api/1/applications/APPLICATION_ID"')
                )
            ) .
            Design::tag(['li', 'h4'], 'Tags', ['id'=>'tags']) .
            Design::tag('p', 'Escape \'*\', \'.\', \'!\', \'-\', \'(\', \')\', \':\', \'/\' in tag text by \'/\'.') .
            Design::table(['Operator', 'Description'],
                [
                    ['*', 'AND'],
                    ['.', 'OR'],
                    ['!', 'NOT'],
                    ['-', 'XOR']
                ], 'Operators', 'uk-table'
            ) .
            Design::tag('ul',
                Design::tag('li', 'Example: "a*!(b/*/./!/-/:///(/).c)*!d"') .
                Design::tag('li', 'It means "having TAG \'a\', and not having TAG \'b*.!-:/()\' or TAG \'c\', and having TAG other than \'d\'."') .
                Design::tag('li', '\'!\' usually means NOT. But if it locates just before a TAG, it means "having tags other than the TAG."')
            ) .
            Design::tag(['li', 'h4'], 'Tag Qualifiers', ['id'=>'qualifiers']) .
            Design::table(['Qualifier', 'Type', 'Subject', 'Description'],
                [
                    ['normal', 'DEFAULT', 'Anything is OK', 'This will be used when it has no qualifiers. This API always omits it.'],
                    ['year, month, day, hour, minute', 'AUTO', '4 digits for year, zero-filled 2 digits for others', 'The posting time'],
                    ['by_user', 'AUTO', 'USER_ID', 'The user who posted'],
                    ['user, to_user', 'OPTION', 'USER_ID', 'Relate to a user, Reply to a user'],
                    ['message, to_message', 'OPTION', 'MESSAGE_ID', 'Relate to a message, Reply to a message'],
                    ['application', 'AUTO', 'APPLICATION_ID', 'The posted client'],
                    ['hash', 'OPTION', 'Anything is OK', 'Hash a string'],
                    ['not_used', 'AUTO', 'TAG QUALIFIER', 'Not used tag qualifier']
                ], 'Tag Qualifiers', 'uk-table'
            ) .
            Design::tag('ul',
                Design::tag('li', 'Type DEFAULT will be used when it has no qualifiers.') .
                Design::tag('li', 'Type AUTO is created by server, and unavailable when posting.') .
                Design::tag('li', 'Type OPTION is available when posting.')
            )
        ), ['type'=>'I']
    )
);
