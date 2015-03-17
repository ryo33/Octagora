<?php

class Design{

    static $message_count = 0;
    const show_tags = 'Show';
    const hide_tags = 'Hide';

    static function message_panel($message, $source_data=[]){
        dump($message, true);
    $tag_class = 'uk-button uk-button-small uk-margin-small-bottom uk-margin-small-right';
        self::$message_count ++;
        $tags = '';
        if(count($message['ts']) > 0){
            unset($source_data[FORM_TAGS]);
            $source_data = '&' . http_build_query($source_data);
            $tmp = '';
            $buttons1 = ['to_message:' . $message['i'], 'message:' . $message['i']];
            //post tags
            foreach($buttons1 as $a){
                $tmp .= Design::tag('a', 'POST ' . $a, ['class'=>'uk-button-primary ' . $tag_class, 'href'=>URL . 'messages?' . POST_TAGS . '=' . $a . $source_data]); 
            }
            //form tags
            foreach($buttons1 as $a){
                $tmp .= Design::tag('a', $a, ['class'=>'uk-button-success ' . $tag_class, 'href'=>URL . 'messages?' . FORM_TAGS . '=' . $a . $source_data]);
            }
            //tags
            foreach($message['ts'] as $a){
                $tmp .= Design::tag('a', $a, ['class'=>$tag_class, 'href'=>URL . 'messages?' . FORM_TAGS . '=' . $a . $source_data]);
            }
            $tags .= self::tag('div',
                $tmp
                    , ['id'=>'tag' . self::$message_count, 'style'=>'display: none;']) .
                    self::tag('button', self::show_tags, ['id'=>'message' . self::$message_count, 'class'=>'uk-button']);
        }
        $relate = '';
        $result = self::tag('div',
            Design::tag('div', $message['t']) . $tags . $relate
            , ['class'=>'uk-panel uk-panel-box' . (isset($message['class']) ? ' ' . $message['class'] : '') . ' uk-margin-small']) .
            Script::switch_id('tag' . self::$message_count, 'message' . self::$message_count, self::show_tags, self::hide_tags, false);
        return $result;
    }

    static function form_incorrect($message){
        if($message !== false){
            return '<div class="uk-form-row"><p class="uk-text-danger">' . $message . '</p></div>';
        }
        return '';
    }

    static function login_form($message, $url=false){
        return Design::form_start('login', 'users', 'POST') .
        Design::form_incorrect($message) .
        Design::form_input([
            'placeholder'=>'User Name',
            'name'=>'name',
            'required'=>true,
            'type'=>'text'
        ]) .
        Design::form_input([
            'placeholder'=>'Password',
            'name'=>'password',
            'required'=>true,
            'type'=>'password'
        ]) .
        Design::form_submit('Login') .
        Design::login_bottom($url) .
        Design::form_end();
    }

    static function credential_form($application){
    }

    /**
     * required
     * placeholder
     * value
     * name
     * label
     * type
     */
    static function form_input($form){
        $required = isset($form['required']) ? ' required' : '';
        $placeholder = isset($form['placeholder']) ? ' placeholder="' . $form['placeholder'] . '"' : '';
        $value = isset($form['value']) ? ' value="' . $form['value'] . '"' : '';
        $input = '<input class="uk-width-1-1 uk-form-large" name="' . $form['name'] . '" type="' . $form['type'] . '"' . $value . $placeholder . $required . ' />';
        if(isset($form['label'])){
            $input = '<label>' . $form['label'] . $input . '</label>';
        }
        return '<div class="uk-form-row uk-text-left">' . $input . '</div>';
    }

    /**
     * required
     * placeholder
     * value
     * name
     * label
     */
    static function form_textarea($form){
        $required = isset($form['required']) ? ' required' : '';
        $placeholder = isset($form['placeholder']) ? ' placeholder="' . $form['placeholder'] . '"' : '';
        $id = isset($form['id']) ? ' id="' . $form['id'] . '"' : '';
        $name = isset($form['name']) ? ' name="' . $form['name'] . '"' : '';
        $value = isset($form['value']) ? $form['value'] : '';
        if(isset($form['label'])){
            $label = '<p>' . $form['label'] . '</p>';
        }else{
            $label = '';
        }
        return self::tag('div', $label . '<textarea class="uk-width-1-1"' . $name . $id . $placeholder . $required . '>' . $value . '</textarea>', ['class'=>'uk-form-row uk-text-left']);
    }

    /**
     * required
     * name
     * label
     * options
     */
    static function form_select($form){
        $required = isset($form['required']) ? ' required' : '';
        if(isset($form['label'])){
            $label = '<p>' . $form['label'] . '</p>';
        }else{
            $label = '';
        }
        return self::tag('div', $label . '<select name="' . $form['name'] . '"' . $required . '>' . implode('', array_map(function($a){return '<option>' . $a . '</option>';}, $form['options'])) . '</select>', ['class'=>'uk-form-row uk-text-left']);
    }

    static function form_submit($text, $option=[]){
        $options = '';
        foreach($option as $key=>$o){
            $options .= ' ' . $key . '="' . $o . '"';
        }
        return '<div class="uk-form-row"><button' . $options . ' class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit">' . $text . '</button></div>';
    }

    static function login_bottom($url=false){
        if($url === false){
            return '<div class="uk-form-row uk-text-small"><label class="uk-float-left"><input name="remember" value="true" type="checkbox" />Remember Me</label><a class="uk-float-right uk-link" href="' . URL . 'users?action=new">Sign Up</a></div>';
        }else{
            return '<div class="uk-form-row uk-text-small"><label class="uk-float-left"><input name="remember" value="true" type="checkbox" />Remember Me</label><a class="uk-float-right uk-link" href="' . URL . $url . '?action=new">Sign Up</a></div>';
        }
    }

    static function form_start($form_name, $url, $method){
        return '<form class="uk-panel uk-panel-box uk-form uk-container-center uk-margin-small" action="' . URL . $url . '" method="' . $method . '">' . ($form_name === false ? '' : '<input type="hidden" name="' . TOKEN . '" value="' . get_token($form_name) . '" />');
    }

    static function form_end(){
        return '</form>';
    }

    static function tag($tag, $text, $option=[]){
        $options = '';
        $close = true;
        foreach($option as $key=>$o){
            if($key === 'close'){
                $close = $o;
            }else{
                $options .= ' ' . $key . '="' . $o . '"';
            }
        }
        if(is_array($tag)){
            if(count($tag) > 1){
                $tmp = $tag[0];
                array_shift($tag);
                $text = self::tag($tag, $text);
                $tag = $tmp;
            }else{
                $tag = $tag[0];
            }
        }
        return '<' . $tag . $options . '>' . $text . ($close ? '</' . $tag . '>' : '');
    }

    static function link($url, $text, $class=false){
        $class = $class !== false ? ' class="' . $class . '"' : '';
        return '<a href="' . URL . $url . '"' . $class . '>' . $text . '</a>';
    }

    static function table($head, $rows, $caption=false, $class=false){
        $class = $class !== false ? ' class="' . $class . '"' : '';
        $caption = $caption !== false ? '<caption>' . $caption . '</caption>' : '';
        $result = '<table' . $class . '>' . $caption;
        if(count($head) > 0){
            $result .= '<tr>' . implode('', array_map(function($a){return '<th>' . $a . '</th>';}, $head)) . '</tr>';
        }
        foreach($rows as $row){
            $result .= '<tr>' . implode('', array_map(function($a){return '<td>' . $a . '</td>';}, $row)) . '</tr>';
        }
        return $result . '</table>';
    }

    static function _list($tag, $lis, $class=false, $class2=false){
        $class = $class !== false ? ' class="' . $class . '"' : '';
        $class2 = $class2 !== false ? ' class="' . $class2 . '"' : '';
        return Design::tag($tag,implode('', array_map(function($a)use($class2){return '<li' . $class2 . '>' . $a . '</li>';}, $lis)), ['class'=>$class]);
    }

}
