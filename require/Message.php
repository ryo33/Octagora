<?php

class Message extends Model{

    const TAG_MAX = 256;
    const TEXT_MAX = 16384;

    function __construct($con){
        parent::__construct($con);
        $this->tag_types = array(
            'normal',
            'year', 'month', 'day',
            'hour', 'minute',
            'by_user', 'to_user', 'user',
            'message', 'to_message',
            'length',
            'application',
            'not_used'
        );
        foreach($this->tag_types as $i=>$tag_type){
            $this->tag_types_key[$tag_type] = $i;
        }
        $this->allow_post_tag_types = array(
            $this->tag_types_key['to_user'],
            $this->tag_types_key['user'],
            $this->tag_types_key['to_message'],
            $this->tag_types_key['message'],
            $this->tag_types_key['normal'],
        );
    }

    function format_selects(&$needs, $other=false){
        if($other !== false){
            $other .= ', ';
        }else{
            $other = '';
        }
        $needs = explode(',', $needs);
        $selects = array();
        foreach($needs as $need){
            switch($need){
            case 't':
                if(in_array('text', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'text';
                break;
            case 'c':
                if(in_array('created', $selects)){
                    error(400, 'needs');
                }
                $selects[] = 'created';
                break;
            }
        }
        return count($selects) === 0 ? $other . '`id`' : ($other . '`id`, ' . implode(', ', array_map(function($x){return '`'.$x.'`';}, $selects)));
    }

    function check_order($order){
        if($order === 'desc'){
            $order = 'DESC';
        }else if($order === 'asc'){
            $order = 'ASC';
        }else{
            error(400, 'order');
        }
        return $order;
    }

    function format_message(&$json, $message, $needs, $tso){
        foreach($needs as $need){
            switch($need){
            case 'i':
                if(array_key_exists('i', $json)){
                    error(400, 'needs');
                }
                $json['i'] = $message['id'];
                break;
            case 't':
                if(array_key_exists('t', $json)){
                    error(400, 'needs');
                }
                $json['t'] = $message['text'];
                break;
            case 'c':
                if(array_key_exists('c', $json)){
                    error(400, 'needs');
                }
                $json['c'] = $message['created'];
                break;
            case 'ts':
                if(array_key_exists('ts', $json)){
                    error(400, 'needs');
                }
                $tags_option = explode(',', $tso);
                $types = array();
                foreach($tags_option as $option){
                    $num = $this->tag_types_key[$option];
                    if($num === false || in_array($num, $types)){
                        error(400, 'tag option');
                    }
                    $types[] = $num;
                }
                if(count($types) === 0){
                    error(400, 'tag option');
                }
                $tags = $this->con->fetchAll('SELECT `type`, `text` FROM `tag` WHERE `id` in (SELECT `tag_id` FROM `tagging` WHERE `message_id` = ?) AND `type` in (\'' . implode('\', \'', $types) . '\')', $message['id']);
                $json['ts'] = array();
                foreach($tags as $tag){
                    $json['ts'][] = $tag['type'] === '0' ? $tag['text'] : $this->tag_types[$tag['type']] . ':' . $tag['text'];
                }
                break;
            default:
                error(400, 'needs');
            }
        }
    }

    function get_messages(&$json, $last, $start, $length, $needs, $order, $ts, $tso){
        if(! preg_match('/^[0-9]+$/', $start)){
            error(400, START);
        }
        if(! preg_match('/^[0-9]+$/', $length)){
            error(400, LENGTH);
        }
        $selects = $this->format_selects($needs);
        $order = $this->check_order($order);
        $tokens = $this->get_tokens($ts, $types);
        $result = $this->get_tags_where($tokens);
        $tags_where = $result[0];
        $tag_values = $result[1];
        if(strlen($last) !== 0){
            $messages = $this->con->fetchAll('SELECT ' . $selects . ' FROM `message` WHERE `created` >= ? AND ' . $tags_where . ' ORDER BY `created` ' . $order . ' LIMIT ' . $start . ', ' . $length, array_merge(array($last), $tag_values));
        }else if(strlen($tags_where) !== 0){
            $messages = $this->con->fetchAll('SELECT ' . $selects . ' FROM `message` WHERE ' . $tags_where . ' ORDER BY `created` ' . $order . ' LIMIT ' . $start . ', ' . $length, $tag_values);
        }else{
            $messages = $this->con->fetchAll('SELECT ' . $selects . ' FROM `message` ORDER BY `created` ' . $order . ' LIMIT ' . $start . ', ' . $length);
        }
        $json['data-count'] = count($messages);
        $json['messages'] = array();
        foreach($messages as $i=>$message){
            $json['messages'][$i] = array();
            $this->format_message($json['messages'][$i], $message, $needs, $tso);
        }
    }

    function get_message(&$json, $id, $needs, $tso){
        $selects = $this->format_selects($needs, 'COUNT(`id`)');
        $message = $this->con->fetch('SELECT ' . $selects . ' FROM `message` WHERE `id` = ?', $id);
        if($message['COUNT(`id`)'] !== '1'){
            error(400, 'message_id');
        }
        $json['data-count'] = 1;
        $this->format_message($json['message'], $message, $needs, $tso);
    }

    function post_message(&$json, $ts, $text, $user_id, $client_id){
        global $now;
        $tags = array();
        if(strlen($text) === 0 || mb_strlen($text) > self::TEXT_MAX){
            error(400, 'text length');
        }
        $tokens = $this->get_tokens($ts, $types, true);
        $before_is_and = true;
        $error = false;
        foreach($tokens as $token){
            if($before_is_and){
                if($token === '&' or $token === '|' or $token === '^' or $token === '~' or $token === '(' or $token === ')'){
                    error(400, 'tags');
                    break;
                }else{
                    if(in_array($token, $tags)){
                        error(400, 'tags');
                    }
                    $tags[] = $token;
                }
                $before_is_and = false;
            }else{
                if($token === '&'){
                    $before_is_and = true;
                }else{
                    error(400, 'tags');
                }
            }
        }
        $message_id = parent::create_id('message');
        parent::insert('message', array('id', 'text'), array($message_id, $text));
        foreach($tags as $tag){
            parent::insert('tagging', array('tag_id', 'message_id'), array($tag, $message_id));
        }
        $autotags[] = $this->get_tag_id('year:' . $now->format('Y'), $type); $types[$type] = true;
        $autotags[] = $this->get_tag_id('month:' . $now->format('m'), $type); $types[$type] = true;
        $autotags[] = $this->get_tag_id('day:' . $now->format('d'), $type); $types[$type] = true;
        $autotags[] = $this->get_tag_id('hour:' . $now->format('H'), $type); $types[$type] = true;
        $autotags[] = $this->get_tag_id('minute:' . $now->format('i'), $type); $types[$type] = true;
        $autotags[] = $this->get_tag_id('length:' . mb_strlen($text), $type); $types[$type] = true;
        $autotags[] = $this->get_tag_id('application:' . $client_id, $type); $types[$type] = true;
        if($user_id !== false){
            $autotags[] = $this->get_tag_id('by_user:' . $user_id, $type); $types[$type] = true;
        }
        foreach($this->tag_types as $tag_type){
            if($tag_type !== 'not_used' && array_key_exists($this->tag_types_key[$tag_type], $types) === false){
                $autotags[] = $this->get_tag_id('not_used:' . $tag_type, $type);
            }
        }
        foreach($autotags as $autotag){
            parent::insert('tagging', array('tag_id', 'message_id'), array($autotag, $message_id));
        }
        $json['id'] = $message_id;
    }

    function get_tokens($tags_request, &$types, $post=false){
        if($tags_request === false){
            return [];
        }
        $tokens = $this->format_tags($this->get_tags($tags_request), $types, $post);
        if($tokens === true){
            error(400, 'tag');
        }
        //log tags
        parent::insert('get_messages_log', ['ts'], [implode('', $tokens)]);
        return $tokens;
    }

    function get_tags($tags_request){
        $escape = '/';
        $tags = $this->tokenize_tag($tags_request, $escape);
        if($tags === true){
            error(400, 'tag');
        }
        return $tags;
    }

    function tokenize_tag($text, $escape){
        if(strlen($text) === 0){
            return array();
        }
        $before_escape = false;
        $result = array();
        $token = '';
        $opens = 0;
        $tag = '';
        foreach(str_split($text) as $c){
            if($c === $escape){
                $tag .= $escape;
                if($before_escape){
                    $before_escape = false;
                }else{
                    $before_escape = true;
                }
            }else{
                if(!$before_escape){
                    if($c === '&'){
                        if(strlen($tag) !== 0){
                            $result[] = $tag;
                            $tag = '';
                        }
                        $result[] = '&';
                    }else if($c === '|'){
                        if(strlen($tag) !== 0){
                            $result[] = $tag;
                            $tag = '';
                        }
                        $result[] = '|';
                    }else if($c === '^'){
                        if(strlen($tag) !== 0){
                            $result[] = $tag;
                            $tag = '';
                        }
                        $result[] = '^';
                    }else if($c === '~'){
                        if(strlen($tag) !== 0){
                            $result[] = $tag;
                            $tag = '';
                        }
                        $result[] = '~';
                    }else if($c === '('){
                        if(strlen($tag) !== 0){
                            $result[] = $tag;
                            $tag = '';
                        }
                        $result[] = '(';
                        $opens ++;
                    }else if($c === ')'){
                        if(strlen($tag) !== 0){
                            $result[] = $tag;
                            $tag = '';
                        }
                        $result[] = ')';
                        $opens --;
                    }else{
                        $tag .= $c;
                    }
                }else{
                    switch($c){
                    case '&':
                    case '|':
                    case '^':
                    case '~':
                    case '(':
                    case ')':
                    case ':':
                        $tag .= $c;
                        break;
                    default:
                        error(400, 'tag');
                    }
                }
                $before_escape = false;
            }
        }
        if(strlen($tag) !== 0){
            $result[] = $tag;
        }
        if($opens !== 0){
            error(400, 'tag');
        }
        return $result;
    }

    function format_tags($tags, &$types, $post=false){
        if(count($tags) === 0){
            return $tags;
        }
        if($tags === true){
            return true;
        }
        $last_is_tag = false;
        $last = '';
        foreach($tags as $i=>$tag){
            if($tag === '&'){
                if(!$last_is_tag and $last !== ')'){
                    error(400, 'tag');
                }
                $last_is_tag = false;
                $last_is_not = false;
            }else if($tag === '|'){
                if(!$last_is_tag and $last !== ')'){
                    error(400, 'tag');
                    return true;
                }
                $last_is_tag = false;
                $last_is_not = false;
            }else if($tag === '^'){
                if(!$last_is_tag and $last !== ')'){
                    error(400, 'tag');
                    return true;
                }
                $last_is_tag = false;
                $last_is_not = false;
            }else if($tag === '~'){
                if($last === ')'){
                    error(400, 'tag');
                    return true;
                }
                $last_is_tag = false;
                $last_is_not = true;
            }else if($tag === '('){
                if($last_is_tag or $last === ')'){
                    error(400, 'tag');
                    return true;
                }
                $last_is_tag = false;
                $last_is_not = false;
            }else if($tag === ')'){
                if(!$last_is_tag and $last !== ')'){
                    error(400, 'tag');
                    return true;
                }
                $last_is_tag = false;
                $last_is_not = false;
            }else{
                if($last_is_tag and $last !== ')'){
                    error(400, 'tag');
                    return true;
                }
                $last_is_tag = true;
                $last_is_not = false;
                $tags[$i] = $this->get_tag_id($tag, $type, $post);
                $types[$type] = true;
            }
            $last = $tag;
        }
        if($last !== ')' and !$last_is_tag){
            error(400, 'tag');
        }
        return $tags;
    }

    function check_message_id($tag){
        if(mb_strlen($tag) === parent::ID_LENGTH){
            for($i = 0; $i < parent::ID_LENGTH; $i ++){
                if(strpos($this->id_characters, $tag[$i]) === false){
                    error(400, 'special tag message_id');
                }
            }
            if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `message` WHERE `id` = ?', $tag) !== '1'){
                error(400, 'special tag message_id');
            }
        }else{
            error(400, 'special tag message_id');
        }
        return false;
    }

    function check_user_id($tag){
        if(mb_strlen($tag) === parent::ID_LENGTH){
            for($i = 0; $i < parent::ID_LENGTH; $i ++){
                if(strpos($this->id_characters, $tag[$i]) === false){
                    error(400, 'special tag user_id');
                }
            }
            if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `id` = ?', $tag) !== '1'){
            error(400, 'special tag user_id');
            }
        }else{
            error(400, 'special tag user_id');
        }
        return false;
    }

    function get_tag_id($tag, &$type, $post=false){
        //check tag length
        if(mb_strlen($tag) > self::TAG_MAX){
            error(400, 'tag length');
        }
        $escaped_tag = $this->escape_tag($tag, $type);
        //check tag type is allowed
        if($post and ! in_array($type, $this->allow_post_tag_types)){
            error(400, 'tag not allowed tag type \'' . $this->tag_types[$type] . '\'');
        }
        //check tag using another id
        switch($type){
        case $this->tag_types_key['message']:
        case $this->tag_types_key['to_message']:
            $this->check_message_id($escaped_tag);
            break;
        case $this->tag_types_key['user']:
        case $this->tag_types_key['by_user']:
        case $this->tag_types_key['to_user']:
            $this->check_user_id($escaped_tag);
            break;
        case $this->tag_types_key['not_used']:
            if(! in_array($escaped_tag, $this->tag_types_key)){
                error(400, 'special tag not_used');
            }
            break;
        }
        $result = $this->con->fetch('SELECT COUNT(`id`), `id` FROM `tag` WHERE `text` = ? AND `type` = ?', array($escaped_tag, $type));
        if($result['COUNT(`id`)'] !== '0'){
            return $result['id'];
        }else{
            return parent::insert('tag', array('text', 'type'), array($escaped_tag, $type));
        }
    }

    function get_tags_where($tags){
        $result = '';
        $tag_ids = array();
        foreach($tags as $tag){
            if($tag === '|'){
                $result .= ' OR ';
            }else if($tag === '^'){
                $result .= ' XOR ';
            }else if($tag === '&'){
                $result .= ' AND ';
            }else if($tag === '~'){
                $result .= 'NOT ';
            }else if($tag === '('){
                $result .= '(';
            }else if($tag === ')'){
                $result .= ')';
            }else{
                if(substr($result, -1, 1) !== '~'){
                    $result .= '`id` IN (SELECT `message_id` FROM `tagging` WHERE `tag_id` = ?)';
                }else{
                    $result .= '`id` IN (SELECT `message_id` FROM `tagging` WHERE `tag_id` != ?)';
                }
                $tag_ids[] = $tag;
            }
        }
        return array($result, $tag_ids);
    }

    function escape_tag($tag, &$rtype){
        $result = $tag;
        $rtype = 0;
        foreach($this->tag_types as $i=>$type){
            if(strpos($result, $type . ':') === 0){
                $result = str_replace($type . ':', '', $result, $count);
                if($count !== 1){
                    error(400, 'tag modifier');
                }
                $rtype = $i;
                break;
            }
        }
        $result = preg_replace('/\/([\|\^\&\(\)\/~])/', '$1', $result);
        $result = str_replace('/:', ':', $result, $count);
        if(substr_count($result, ':') !== $count){
            error(400, 'tag escape \':\'');
        }
        return $result;
    }
}
