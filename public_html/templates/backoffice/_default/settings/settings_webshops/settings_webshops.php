<?php
class Settings_webshops{
    function  run($params){        
        // Create "missing" webshops (the ones that have a custom folder but are not in the database)
        $currentshops   = Webshop::getAvailable();
        
        $tmpshopdirs    = glob('./templates/webshops/*');
        foreach($tmpshopdirs as $tmpshop)
        {
            $shopdirs[] = basename($tmpshop);
        }

        foreach($shopdirs as $shopdir)
        {
            $available = false;
            foreach($currentshops as $currentshop){
                if($currentshop['hostname']==$shopdir){
                    $available = true;

                }
            }
            if(!$available){
                Webshop::create($shopdir);
            }
        }

        // Get all available webshops
        $params['webshops'] = Webshop::getAvailable();

        $params['content']                  =   parse('settings_webshops', $params, __FILE__);

        return $params;
    }
}