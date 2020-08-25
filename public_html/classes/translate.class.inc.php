<?php
class Translate{
    private static $translation;
    
    public static function init($lang, $page) {        
        $webshopOrBackoffice    = Cfg::getSiteType();
        self::$translation      = self::getTranslationNoInject($lang, $page,$webshopOrBackoffice);        
        Template::addVars(self::$translation);
    }
    
    public static function getTranslationNoInject($lang,$page,$webshopOrBackoffice){
        if($lang == 'en'){
            $lang = 'gb';
                    
        }        
		if($lang == '')
			$lang = 'nl';		
        

        $data[0]['translation'] = $data[1]['translation'] = array();        
        
        if($webshopOrBackoffice=='backoffice' && $page=='login'){
			$page = 'home';
        }
        $files[0] = './translations/'.$webshopOrBackoffice.'/'.$lang.'/default.xml';
        $files[1] = './translations/'.$webshopOrBackoffice.'/'.$lang.'/'.$page.'.xml';        

        if(file_exists($files[0]))
            $data[0]        = Xml2Array::parse(file_get_contents($files[0]));
       #     pre_r$     
        
        
        if(file_exists($files[1]))
            $data[1]    = Xml2Array::parse(file_get_contents($files[1]));
        
        $translation    = array_merge($data[0]['translation'] ?? [], $data[1]['translation'] ?? []);
        #pre_r($translation);
        
        return $translation;                 
    }
    
    public static function getTranslation(){
        return self::$translation;
    }
    
}
