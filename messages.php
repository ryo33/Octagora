<?php

require REQ . 'Message.php';

$message = new Message($con);
$uri = $req->get_uri();
echo $uri;
if($uri === false){
    //get start and length
    $start = $req->get_param(START, '0');
    if(! preg_match('/^[0-9]+$/', $start)){
        error(400, START);
    }
    $length = $req->get_param(LENGTH, '100');
    if(! preg_match('/^[0-9]+$/', $length)){
        error(400, LENGTH);
    }
    //get needs
    $needs = explode(',', $req->get_param(NEEDS, 'i,t'));
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
    //get order
    $order = $req->get_param(ORDER, 'desc');
    if($order === 'desc'){
        $order = 'DESC';
    }else if($order === 'asc'){
        $order = 'ASC';
    }else{
        error(400, 'order');
    }
    //tokenize tag
    $tokens = $message->get_tokens($req->get_param(TAGS, false));
    $result = $message->get_tags_where($tokens);
    $tags_where = $result[0];
    $tag_values = $result[1];
    //access to database
    if(strlen($last) !== 0){
        $messages = $con->fetchAll('SELECT ' . (count($selects) === 0 ? '`id`' : ('`id`, ' . implode(', ', array_map(function($x){return '`'.$x.'`';}, $selects)))) . ' FROM `message` WHERE `created` >= ? AND ' . $tags_where . ' ORDER BY `created` ' . $order . ' LIMIT ' . $start . ', ' . $length, array_merge(array($last), $tag_values));
    }else if(strlen($tags_where) !== 0){
        $messages = $con->fetchAll('SELECT ' . (count($selects) === 0 ? '`id`' : ('`id`, ' . implode(', ', array_map(function($x){return '`'.$x.'`';}, $selects)))) . ' FROM `message` WHERE ' . $tags_where . ' ORDER BY `created` ' . $order . ' LIMIT ' . $start . ', ' . $length, $tag_values);
    }else{
        $messages = $con->fetchAll('SELECT ' . (count($selects) === 0 ? '`id`' : ('`id`, ' . implode(', ', array_map(function($x){return '`'.$x.'`';}, $selects)))) . ' FROM `message` ORDER BY `created` ' . $order . ' LIMIT ' . $start . ', ' . $length);
    }
    $res->content['data-count'] = count($messages);
    $res->content['messages'] = array();
    foreach($messages as $i=>$message){
        $res->content['messages'][$i] = array();
        foreach($needs as $need){
            switch($need){
            case 'i':
                if(array_key_exists('i', $res->content['messages'][$i])){
                    error(400, 'needs');
                }
                $res->content['messages'][$i]['i'] = $message['id'];
                break;
            case 't':
                if(array_key_exists('t', $res->content['messages'][$i])){
                    error(400, 'needs');
                }
                $res->content['messages'][$i]['t'] = $message['text'];
                break;
            case 'c':
                if(array_key_exists('c', $res->content['messages'][$i])){
                    error(400, 'needs');
                }
                $res->content['messages'][$i]['c'] = $message['created'];
                break;
            case 'ts':
                if(array_key_exists('ts', $res->content['messages'][$i])){
                    error(400, 'needs');
                }
                $tags_option = explode(',', $req->get_param(TAGS_OPTION, 'normal,by_user,to_user,user,message,to_message'));
                $types = array();
                foreach($tags_option as $option){
                    $num = $tag_types_key[$option];
                    if($num === false || in_array($num, $types)){
                        error(400, 'tag option');
                    }
                    $types[] = $num;
                }
                if(count($types) === 0){
                    error(400, 'tag option');
                }
                $tags = $con->fetchAll('SELECT `type`, `text` FROM `tag` WHERE `id` in (SELECT `tag_id` FROM `tagging` WHERE `message_id` = ?) AND `type` in (\'' . implode('\', \'', $types) . '\')', $message['id']);
                $res->content['messages'][$i]['ts'] = array();
                foreach($tags as $tag){
                    $res->content['messages'][$i]['ts'][] = $tag['type'] === '0' ? $tag['text'] : $tag_types[$tag['type']] . ':' . $tag['text'];
                }
                break;
            default:
                error(400, 'needs');
            }
        }
    }
}else if($message->check_message_id($uri) === false){
}
