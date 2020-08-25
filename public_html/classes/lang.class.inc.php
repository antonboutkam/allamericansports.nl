<?php
class Lang{
    public static function detect($params){       
        if(Cfg::getSiteType()=='backoffice'){
            if(isset($params['lang'])&&$params['lang']!=''&&User::getId())
                query(sprintf('UPDATE users SET lang="%s" WHERE id=%d',$params['lang'],User::getId()),__METHOD__);
            if(User::getId())
                $lang =  fetchVal(sprintf('SELECT lang FROM users WHERE id=%s',User::getId()),__METHOD__);
            return (isset($lang) && $lang!='')?$lang:'nl';
        }else{
            
            $lang = substr($_SERVER['REQUEST_URI'],1,2);            
            if(!$lang){
                $lang = 'en';
            }
            return $lang;
        }
    }
    public static function langToCountry($lang){
        $lang = str_replace('Dutch','The Netherlands',$lang);
        $lang = str_replace('French','France',$lang);
        $lang = str_replace('Italian','Italy',$lang);
        $lang = str_replace('German','Germany',$lang);
        return $lang;
    }
    public static function getCodeByLanguageId($langId){
        if($lang=='gb')
            $lang = 'en';                
        $sql = sprintf('SELECT locale FROM locales WHERE id="%d"',$langId);
        return fetchVal($sql,__METHOD__);
    }    
    public static function getLocaleIdByLanguageCode($lang){
        $key    = 'localebylang'.$lang;        
        if(!$cached = GlobalCache::isCached($key)){
            if($lang=='gb')
                $lang = 'en';                
            $sql    = sprintf('SELECT id FROM locales WHERE locale="%s"',$lang);
            $cached = fetchVal($sql,__METHOD__);            
            GlobalCache::store($key,$cached);            
        }
        return $cached;        
    }
    public static function getAvailable($langAsKey=true){
        $files = glob('./translations/*');
        foreach($files as $file){
            $tmp = explode('.',basename($file));
            $langs[$tmp[0]] = $tmp[0];                        
        }            
        return $langs;                   
    }
}
/*
Oude versie, mag weg
class Lang{
    public static function detect($params){       
        if(Cfg::getSiteType()=='backoffice'){
            if(isset($params['lang'])&&$params['lang']!=''&&User::getId())
                query(sprintf('UPDATE users SET lang="%s" WHERE id=%d',$params['lang'],User::getId()),__METHOD__);
            if(User::getId())
                $lang =  fetchVal(sprintf('SELECT lang FROM users WHERE id=%s',User::getId()),__METHOD__);
            return ($lang!='')?$lang:'nl';
        }else{
            $lang = substr($_SERVER['REQUEST_URI'],0,3);
            if($lang=='/gb')
                return 'gb';
            return 'nl';
        }
    }
    public static function getLocaleIdByLanguageCode($lang){
        if($lang=='gb')
            $lang = 'en';                
        $sql = sprintf('SELECT id FROM locales WHERE locale="%s"',$lang);
        return fetchVal($sql,__METHOD__);
    }
    public static function getAvailable($langAsKey=true){
        $files = glob('./translations/'.Cfg::getSiteType().'/*');        
        foreach($files as $file){
            $tmp = explode('.',basename($file));
            $langs[$tmp[0]] = $tmp[0];                        
        }            
        return $langs;                   
    }
}
*/