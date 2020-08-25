<?php
class Settings_components{
    function  run($params){        
        if(isset($params['modules']))
            foreach($params['modules'] as $module=>$setting)                
                Cfg::storeModule ($module, $setting);
                        
        $modules = Cfg::getModules();                       
        if(count($modules)>0)
            $params  = array_merge($params,$modules);
        if(isset($params['settings'])){
            foreach($params['settings'] as $key=>$val){
                Cfg::storePref($key, $val);        
                $params['saved'] = true;                
            }
        }
        $params['settings']        = Cfg::getPrefs();
        
        
        return $params;
    }
}