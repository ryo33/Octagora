<?php

class Script{

    static $switch_id_flag = false;

    static function switch_id($id, $button_id, $show, $hide, $default=true, $time=400){
        $id = '#' . $id;
        $button_id = '#' . $button_id;
        $result = '<script>';
        if(self::$switch_id_flag === false){
            self::$switch_id_flag = true;
            $result .= <<<SCRIPT
function click_func(id, button, show, hide, time){
    switch($(button).text()){
        case show:
            show_func(id, button, hide, time);
            break;
        case hide:
            hide_func(id, button, show, time);
            break;
    }
}
function hide_func(id, button, text, time){
    $(button).text(text);
    $(id).hide(time);
}
function show_func(id, button, text, time){
    $(button).text(text);
    $(id).show(time);
}
SCRIPT;
        }
        $result .= ($default ? "show_func(\"$id\", \"$button_id\", \"$hide\", $time);" : "hide_func(\"$id\", \"$button_id\", \"$show\", $time);") . <<<SCRIPT
$("$button_id").click(function(){click_func("$id", this, "$show", "$hide", $time);});
SCRIPT;
        return $result .= '</script>';
    }

}
