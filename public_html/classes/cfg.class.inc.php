<?php

/**
 * AppCfg configuratie klasse, deze klasse dient om instellingen te achterhalen.
 * 
 * @package SharedClasses  
 * @author Anton Boutkam <anton@ratus.nl>
 */
class Cfg{
	static $config; 
	/**
	 * Stel de configuratie array in.
	 * @param $config bevat een array met alle instellingen voor de applicatie.
	 */
	static function set($config){
		if(is_array(self::$config)){
			self::$config = array_merge(self::$config,$config);
		}else{
			self::$config = $config;
		}
	}
	/**
	 * Haal een of meerdere configuratie instellingen op.
	 * @param $param 1, key uit de (multidimentionele) array
 	 * @param $param 2, key uit de (multidimentionele) array
 	 * @param $etc gaat onbeperkt door.
	 */			
	static function get(){
		$args 		= func_get_args();
		$num_args 	= func_num_args();
				
		if($num_args>3){
			echo "Teveel argumenten CFG klasse";
			return false;
		}						
		else if($num_args==1){
			if(isset($config[$args[0]])){
				return $config[$args[0]];
			}
            if(isset(self::$config[$args[0]])){
			    return self::$config[$args[0]];
            }
		}
		else if($num_args==2){
			return self::$config[$args[0]][$args[1]];
		}
		else if($num_args==3){
			return self::$config[$args[0]][$args[1]][$args[2]];
		}				
	}
        static function storePref($setting,$value){
            query($sql = sprintf('INSERT INTO settings (setting,value)
                        VALUES ("%1$s","%2$s")
                        ON DUPLICATE KEY UPDATE value="%2$s"',
                    addslashes($setting),
                    addslashes($value)),__METHOD__);                        
        }
        static function getPref($setting){        
            if(!$result = WebshopCache::cached($setting)){
                $result = fetchVal(sprintf('SELECT value FROM settings WHERE setting="%s"',$setting),__METHOD__);
                WebshopCache::store($setting,$result);                
            }        
            return $result;            
        }
        static function getPrefs(){
            $result  = fetchArray(sprintf('SELECT setting, value FROM settings'),__METHOD__);
            foreach($result as $row)
                $out[$row['setting']] = $row['value'];
            return $out;                                
        }

        static function storeModule($name,$value){
            query($sql = sprintf('INSERT INTO modules (module,setting)
                        VALUES ("%1$s","%2$s")
                        ON DUPLICATE KEY UPDATE setting="%2$s"',
                    addslashes($name),
                    addslashes($value)),__METHOD__);
        }
        static function getModules(){

            $cacheKey = 'webshop_modues';
            $modules = GlobalCache::isCached($cacheKey);
            if(!$modules){
                $tmp = fetchArray(sprintf('SELECT * FROM modules'),__METHOD__);
                foreach($tmp as $setting)
                    $modules[$setting['module']] = $setting['setting'];                
                GlobalCache::store($cacheKey, $modules);
                
            }              
            return $modules;
        }
        static function areModulesActive($modules){
            if(!empty($modules)){
                $tmp = fetchArray($sql = sprintf('SELECT module,setting FROM modules WHERE module IN ("%s")',join('","',$modules)),__METHOD__);
                if(!empty($tmp))
                    foreach($tmp as $module)
                        $modulesDb[$module['module']] = $module['setting'];                                    
                foreach($modules as $module)
                    $out[$module] = isset($modulesDb[$module])?$modulesDb[$module]:0;                                
                return $out;                
            }
        }
        static function isModuleActive($moduleName){
            $cacheKey   = 'Cfg_isModuleActive'.$moduleName;
            $out        = GlobalCache::isCached($cacheKey);
            if(!$out){
                $out = fetchVal(sprintf('SELECT setting FROM modules WHERE module="%s"',$moduleName),__METHOD__);
                GlobalCache::store($cacheKey,$out);
            }
            return $out;
        }
        static function getSiteType(){
            if(strpos(" ".$_SERVER['HTTP_HOST'],'backoffice'))
                    return 'backoffice';
            return 'webshops';
        }
        static function getCustomRoot(){
            
            $root = str_replace('www.','',$_SERVER['HTTP_HOST']);
            $root = str_replace('backoffice.','',$root);
            $root = str_replace('nuidev.','',$root);
            $root = str_replace('office.aceview.','',$root);           
	    $root = str_replace('nuicart.','',$root);			
            return $root;
        }
}
