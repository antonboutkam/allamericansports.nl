<?php
class BackofficePlugin {
    public static function trigger($methodToTrigger = '_doFirst',$plugins,$params){        
        if(!empty($plugins)){            
            foreach($plugins as $plugin){
                $params = $plugin->$methodToTrigger($params);
                if(empty($params)){
                    trigger_error("Plugin ".get_class($plugin)."::".$methodToTrigger." is returning an empty array",E_USER_WARNING);
                }
            }
        }
        return $params;                       
    }
    
    public static function loadPlugins($pluginDir){
        $parts      = array_reverse(explode('/',$pluginDir));                                
        $plugins    = glob($pluginDir.'/*');
        $aPluginClasses = [];
        if(!empty($plugins)){
            foreach($plugins as $pluginFilePath){
                $className  = $parts[1].'_'.basename($pluginFilePath);
                require_once($pluginFilePath.'/'.$className.'.php');
                // is_abst

                $oReflector = new ReflectionClass($className);

                if($oReflector->isAbstract())
                {
                    continue;
                }
                $aPluginClasses[] = new $className;
            }            
        }
        return $aPluginClasses;
    }
}
