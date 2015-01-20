<?php

class Message{

    const TAG_MAX = 256;
    const TEXT_MAX = 65536;

    function __construct($con){
        $this->con = $con;
        $this->tag_types = array(
            'normal',
            'year', 'month', 'day',
            'hour', 'minute', 'second',
            'by_user', 'to_user', 'user',
            'message', 'to_message',
            'length',
            'application',
            'system'
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

    function get_tokens($tags_request, $post=false){
        $tokens = $this->format_tags($this->get_tags($tags_request), $post);
        if($tokens === true){
            error(400, 'tag');
        }
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

    function format_tags($tags, $post=false){
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
                $tags[$i] = $this->get_tag_id($tag, $post);
            }
            $last = $tag;
        }
        if($last !== ')' and !$last_is_tag){
            error(400, 'tag');
        }
        return $tags;
    }

    function check_message_id($tag){
        if(mb_strlen($tag) === $this->id_length){
            for($i = 0; $i < $this->id_length; $i ++){
                if(strpos($this->id_characters, $tag[$i]) === false){
                    error(400, 'special tag ' . $this->tag_types[$type]);
                }
            }
            if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `message` WHERE `id` = ?', $tag) !== '1'){
                error(400, 'special tag ' . $this->tag_types[$type]);
            }
        }else{
            error(400, 'special tag ' . $this->tag_types[$type]);
        }
        return false;
    }

    function check_user_id($tag){
        if(mb_strlen($tag) === $this->id_length){
            for($i = 0; $i < $this->id_length; $i ++){
                if(strpos($this->id_characters, $tag[$i]) === false){
                    error(400, 'special tag ' . $this->tag_types[$type]);
                }
            }
            if($this->con->fetchColumn('SELECT COUNT(`id`) FROM `user` WHERE `id` = ?', $tag) !== '1'){
                error(400, 'special tag ' . $this->tag_types[$type]);
            }
        }else if($tag !== 'anonymous'){
            error(400, 'special tag ' . $this->tag_types[$type]);
        }
        return false;
    }

    function get_tag_id($tag, $post=false){
        //check tag length
        if(mb_strlen($tag) > $this->tag_max){
            error(400, 'tag length');
        }
        $escaped_tag = escape_tag($tag, $type);
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
        }
        $result = $this->con->fetch('SELECT COUNT(`id`), `id` FROM `tag` WHERE `text` = ? AND `type` = ?', array($escaped_tag, $type));
        if($result['COUNT(`id`)'] !== '0'){
            return $result['id'];
        }else{
            $id = $this->create_id('tag');
            $this->con->insert('tag', array('id', 'text', 'type'), array($id, $escaped_tag, $type));
            return $id;
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

    function escape_tag($tag, &$type=false){
        $result = $tag;
        $type = 0;
        foreach($this->tag_types as $i=>$type){
            if(strpos($result, $type . ':') === 0){
                $result = str_replace($type . ':', '', $result, $count);
                if($count !== 1){
                    error(400, 'tag modifier');
                }
                if($type !== false){
                    $type = $i;
                }
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
