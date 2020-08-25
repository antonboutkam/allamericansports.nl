<?php
class WebshopCache {
    private static function cacheFile(){        
        return dirname($_SERVER['SCRIPT_FILENAME']).'/tmp/lastchange';
    }
    public static function clear(){

        if(isset($_SESSION['webshop_cache'])){
            unset($_SESSION['webshop_cache']);
        }

        if(isset($_SESSION['lastchange'])){
            unset($_SESSION['lastchange']);
        }

		touch(self::cacheFile());
        $_SESSION['lastchange'] = filemtime(self::cacheFile());
    }
    public static function cached($key){

        if(basename($_SERVER['SCRIPT_FILENAME'])!='index.php'){
            return false;
        }      
        if(!isset($_SESSION['lastchange'])){
            if(!file_exists(self::cacheFile())){
                self::clear(); // Maak bestand aan.
            }
            $_SESSION['lastchange'] = filemtime(self::cacheFile());
        }        
        if($_SESSION['lastchange'] < filemtime(self::cacheFile())){
            self::clear();
            return false;        
        }

        if(isset($_SESSION['webshop_cache']) && isset($_SESSION['webshop_cache'][$_SESSION['lastchange']]) && isset($_SESSION['webshop_cache'][$_SESSION['lastchange']][$key])){
            return $_SESSION['webshop_cache'][$_SESSION['lastchange']][$key];
        }
        return false;
    }		
    public static function store($key,$data){

        if(!isset($_SESSION['lastchange'])){
            $_SESSION['lastchange'] = filemtime(self::cacheFile());
        }

        $_SESSION['webshop_cache'][$_SESSION['lastchange']][$key] = $data;
        return $_SESSION['webshop_cache'][$_SESSION['lastchange']][$key];
    }
}
