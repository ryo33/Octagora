<?php

class ClassLoader{

    protected $dirs;

    public function register(){
        spl_autoload_register([$this, 'load_class']);
    }

    public function register_directory($dir){
        $this->dirs[] = $dir;
    }

    public function load_class($class){
        foreach($this->dirs as $dir){
            $file = $dir . '/' . $class . '.php';
            if(is_readable($file)){
                require $file;
                return;
            }
        }
    }

}
