<?php
class Folder{
    public static function root(){
        $result = dirname($_SERVER['SCRIPT_NAME']);
	
        if($result=='/')
            return;
        return $result;
    }
}
