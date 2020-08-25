<?php
class Page{
    public static function run($params){
        if(Cfg::getSiteType()=='backoffice'){  
            GlobalCache::clearAll();
        }
        
        $params['session_id'] =   session_id();
        $params['root']       =   Folder::root();

        // User has cookie based authentication, change the cookie key on each reload.
        if($user = User::getCurent()){
            if($user['remember_id']){
                User::setAutologin();
            }
        }   
        $params['is_signed_in'] = false; 
        if(isset($_SESSION['relation']) && $_SESSION['relation']['id']){
            $params['is_signed_in'] = true;    
        }
         
        if(isset($params['set_location'])&&!empty($params['set_location'])){
            User::setLocaton($params['set_location']);
        }

        if(isset($params['_do']) && $params['_do']=='logout'){
            session_unset($_SESSION); 
            session_destroy();  
            $_COOKIE['auto'] = null;  
            unset($_COOKIE['auto']);                         
            setcookie("auto", "", time() - 3600,'/');   
        }
		Template::addVars(array('session'=>$_SESSION));			
        $params['lang']                   =   Lang::detect($params);
        $params['locale']                 =   Lang::getLocaleIdByLanguageCode($params['lang']);

        Db::setLocale($params['lang']);
        if(isset($params['windowstate']) && $params['windowstate']) {
            User::setWindowState($params['windowstate']);
        }
                                                      
        $params['windowstate']            =   User::getWindowState();                                                                              
        $params['translations']           =   Lang::getAvailable(false);                
        $params['pages']                  =   Cms::getPages($params['lang'],0);        
        $params['user_level']             =   User::getLevel();                
        $params['is_devel']               =   $_SERVER['IS_DEVEL'];		
        $params['js_anti_cache_str']      =   isset($_SERVER['IS_DEVEL'])?'?dev_anticache='.rand(0,1000000000):''; 
        $page                             =   Cms::currentPage($params['lang']);

        if($params['user_level']=='S' && $page['page'] !='edit'){
            $page['section']              =  'products';
            $page['page']                 =  'catalogue';
            $page['class']                =  'products_catalogue';
            $page['file']                 =  'products/products_catalogue';
            $params['showwostock']        =   true;
        }

        $params['is_member']              =   User::isMember();                             
        $params['is_logged_in']           =   RelationDao::isMember();
        $params                           =   array_merge($params,$page);
 
        if(Cfg::getSiteType()=='backoffice'){            
            // Get all locations (except current user)
            $params['locations']              =   WarehouseDao::getLocations();
            $params['location_name']          =   User::getLocationName();

            foreach($params['locations'] as $id=>$location)
                if(User::getLocaton()==$location['id'])
                        unset($params['locations'][$id]);            
            // Er kan iets gewijzigd zijn in de backend dus we clearen altijd alle cache voor alle users
            WebshopCache::clear();
            
        }
            #Conversion::track();


        Translate::init($params['lang'],(isset($page['section']) && $page['section'])?$page['section']:$page['page']);
        $params['module_stockpile']                 =   Cfg::isModuleActive('module_stockpile');



        if((!isset($params['ajax']) || !$params['ajax']) && $params['is_member']){
            $params['module_product_spotlight']         =   Cfg::isModuleActive('product_spotlight');
            $params['module_product_groups']            =   Cfg::isModuleActive('product_groups');
            $params['module_webshops']                  =   Cfg::isModuleActive('module_webshops');                        
            $params['main_nav']                         =   parse('inc/main_nav',$params);
            $params['sub_nav']                          =   parse('inc/sub_nav',$params);
        }        
        $foundSomething = false;
        #_d($params);



        if(file_exists($dirs[0] = './templates/'.Cfg::getSiteType().'/'.Cfg::getCustomRoot().'/'.$page['file'].'.php')){
            $foundSomething = true;
            require_once($dirs[0]);
        }else if(file_exists($dirs[1] = './templates/'.Cfg::getSiteType().'/_default/'.$page['file'].'.php')){
            $foundSomething = true;
            require_once($dirs[1]);       
        }else if(file_exists($dirs[3] = './templates/'.Cfg::getSiteType().'/'.Cfg::getCustomRoot().'/'.$page['file'].'/'.$page['class'].'.php')){
            $foundSomething = true;
            require_once($dirs[3]);       
        }else if(file_exists($dirs[4] = './templates/'.Cfg::getSiteType().'/_default/'.$page['file'].'/'.$page['class'].'.php')){
            $foundSomething = true;
            require_once($dirs[4]);       
        }

        if($foundSomething && class_exists($page['class'])){                  
            $class                        =   new $page['class'];
            $params                       =   $class->run($params);
        }
        
        if(!isset($params['content']) || !$foundSomething){
            $params['content']            =   parse($page['file'],$params);
            if(empty($params['content'])){
                header("HTTP/1.0 404 Not Found"); 
                $params = Webshop::doFirst($params);
                $params['content']            =   parse('h404',$params);                              
            }    
        }

        /*
        if(Cfg::getSiteType()!='backoffice'){            
            require_once('./templates/webshops/_default/debuginfo.php');
            $tmp = Debuginfo::run($params);            
            $params['debug_info'] = $tmp['content'];
        }
         * 
         */	        
        $params['testmsg']                =   (isset($_SESSION['testmode']) && $_SESSION['testmode'])?'block':'none';
        

        if($params['page'] !='product' && $params['page']!='shop'){
            unset($_SESSION['prodids']);
        }
        
        $params['querystring'] = '?r='.rand(0,999);
        if(strpos($_SERVER['REQUEST_URI'],'?')){
            $tmp = parse_url($_SERVER['REQUEST_URI']);
            $params['querystring'] .= '&'.$tmp['query'];
        }
        if(!isset($params['title_override']))
            $params['title_override'] = '';
        
        if(isset($params['iframe']) && $params['iframe']) {
            return parse('iframe', $params);
        }
        if(isset($params['view']) && $params['view']=='wide'){
            $params['index'] = parse('index_wide',$params);            
        }else{
            $params['index'] = parse('index',$params);             
        } 
        if(isset($params['ajax']) && $params['ajax']){
            return json_encode($params);
        }
        return $params['index'];
        
    }
}
