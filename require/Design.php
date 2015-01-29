<?php

class Design{

    static function form_incorrect($message){
        if($message !== false){
            return '<div class="uk-form-row"><p class="uk-text-danger">' . $message . '</p></div>';
        }
        return '';
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
        $value = isset($form['value']) ? $form['value'] : '';
        if(isset($form['label'])){
            $label = '<p>' . $form['label'] . '</p>';
        }else{
            $label = '';
        }
        return self::tag('div', $label . '<textarea class="uk-width-1-1" name="' . $form['name'] . $placeholder . $required . '">' . $value . '</textarea>', 'uk-form-row uk-text-left');
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
        return self::tag('div', $label . '<select name="' . $form['name'] . '"' . $required . '>' . implode('', array_map(function($a){return '<option>' . $a . '</option>';}, $form['options'])) . '</select>', 'uk-form-row uk-text-left');
    }

    static function form_submit($text){
        return '<div class="uk-form-row"><button class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit">' . $text . '</button></div>';
    }

    static function signup_bottom(){
        return '<div class="uk-form-row uk-text-small"><label class="uk-float-left"><input name="remember" type="checkbox" />Remember Me</label><a class="uk-float-right uk-link" href="' . URL . '/users?action=new">Sign Up</a></div>';
    }

    static function form_start($form_name, $url, $method){
        return '<form class="uk-panel uk-panel-box uk-form uk-container-center" action="' . URL . $url . '" method="' . $method . '"><input type="hidden" name="' . TOKEN . '" value="' . get_token($form_name) . '" />';
    }

    static function form_end(){
        return '</form>';
    }

    static function tag($tag, $text, $option=[]){
        $options = '';
        foreach($option as $key=>$o){
            $options .= ' ' . $key . '="' . $o . '"';
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
        $class = $class !== false ? ' class="' . $class . '"' : '';
        $style = $style !== false ? ' style="' . $style . '"' : '';
        $id = $id !== false ? ' id="' . $id . '"' : '';
        return '<' . $tag . $options . '>' . $text . '</' . $tag . '>';
    }

    static function link($url, $text, $class=false){
        $class = $class !== false ? ' class="' . $class . '"' : '';
        return '<a href="' . URL . $url . '"' . $class . '>' . $text . '</a>';
    }

    static function table($head, $rows, $class=false){
        $class = $class !== false ? ' class="' . $class . '"' : '';
        $result = '<table' . $class . '>';
        if(count($head) > 0){
            $result .= '<tr>' . implode('', array_map(function($a){return '<th>' . $a . '</th>';}, $head)) . '</tr>';
        }
        foreach($rows as $row){
            $result .= '<tr>' . implode('', array_map(function($a){return '<td>' . $a . '</td>';}, $row)) . '</tr>';
        }
        return $result . '</table>';
    }

    static function _list($tag, $lis, $class=false, $class2=false){
        $class2 = $class2 !== false ? ' class="' . $class2 . '"' : '';
        return Design::tag($tag,implode('', array_map(function($a){return '<li' . $class2 . '>' . $a . '</li>';}, $lis)), $class);
    }

}
