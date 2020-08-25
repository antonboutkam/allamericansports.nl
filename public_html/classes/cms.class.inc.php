<?php

/**
 * AppCfg configuratie klasse, deze klasse dient om instellingen te achterhalen.
 * 
 * @author Anton Boutkam <anton@nui-boutkam.nl.nl>
 * @copyright Nui Boutkam
 * @version 2009
 * @access public
 */
class Cms{

    static function getPages(){
        return array();
    }
    static function currentPage($lang){

        if(preg_match('/\/current_stock_check_exact_online/', $_SERVER['REQUEST_URI']) &&  Cfg::getSiteType()=='backoffice'){
            return array('page'=>'stock','section'=>null,'file'=>'stock','class'=>'stock');
        }


        if(!User::isMember() && Cfg::getSiteType()=='backoffice'){

            if(preg_match('/\/stock\?product_id=[0-9]+/', $_SERVER['REQUEST_URI']))
            {
                
                // Dit is een voorraadcheck url die vanuit de webshop wordt aangeroepen.
                // Mag van de api alleen via de backoffice url
            }
            else
            {
                if(!strpos($_SERVER['REQUEST_URI'],'login')){
                    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
                }
                return array('page'=>'login','section'=>null,'file'=>'login','class'=>'login');
            }


		}
         

        $request_uri        =  explode('?',$_SERVER['REQUEST_URI']);        
        $path               = array_flip(array_reverse(explode("/",trim($request_uri[0],'/'))));
        
        
         #if($_SERVER['IS_DEVEL'] && preg_match('/[0-9a-z]+\.nl.dev/',$_SERVER['HTTP_HOST']))
		 if($_SERVER['IS_DEVEL'] && preg_match('/[0-9a-z]+\.nuidev.nl/',$_SERVER['HTTP_HOST']))
            #$out['hostname']    = str_replace('.dev','',$_SERVER['HTTP_HOST']);		 
		 	$out['hostname']    = str_replace('.nuidev','',$_SERVER['HTTP_HOST']);

        else
            $out['hostname']    = str_replace('www.','',$_SERVER['HTTP_HOST']);    

        $out['hostname'] = str_replace('nuicart.','',$out['hostname']);         
        $out['current_webshop_id']  = Webshop::getIdByWebshop($out['hostname']);        
        $tmp                        = array_keys($path);        
        $site                       = end($tmp);


        $parts              = explode(".",$_SERVER['HTTP_HOST']);
        $out['system']      = $parts[1];        
        $out['request_uri'] = $request_uri[0];          
        

        if($_SERVER['IS_DEVEL']){
            unset($path[$site]);
        }
        $path               = array_flip($path);

        if(Cfg::getSiteType()!='backoffice'){

            // Strip off the language code

            $request_uri_no_lang = preg_replace('/^\/[a-z]{2}/','',$request_uri[0]);
            $out['request_uri_no_lang'] = $request_uri_no_lang;

            $request_uri_no_lang_no_page = preg_replace('/-p[0-9]+/','',$request_uri_no_lang);
            if(preg_match('#\/([a-z0-9-]+).html#',$request_uri_no_lang_no_page,$matches)){
                $filetag = str_replace('-',' ',$matches[1]);

                $id = Webshop::getMenuIdByTranslationTag($out['hostname'],$lang,$filetag);

                if(isset($id)){
                    $out['page']=$out['class']=$out['file'] = 'shop';
                    return $out;
                }
                if(!isset($id)){
                    $brand_id=Lookup::getBrandIdByName($filetag);
                    if($brand_id){
                        $out['page']=$out['class']=$out['file'] = 'shop';
                        return $out;
                    }
                }
            }
            $strippedUrl = preg_replace('#^/#','',preg_replace('/\.html/','',$request_uri_no_lang));
            if($out['request_uri'] == '/feed/kieskeuriggen.xml'){
                $out['section'] = 'feed';
                $out['page']    = 'kieskeuriggen';
                $out['class']   = $out['section'].'_'.$out['page'];
                $out['file']    = $out['section'].'/'.$out['section'].'_'.$out['page'];
                return $out;
            }


            if($out['request_uri_no_lang'] =='/shop/turnen-turnpakjes-dreamlight-team-2013-2014-190.html'){
                $out['page']=$out['class']=$out['file'] = 'presignin';                
                return $out;                
            }		    
            if($strippedUrl=='amazon-de-export'){                
                $out['page']=$out['class']=$out['file'] = 'amazonexport';                
                return $out;
            } 
            if($out['request_uri_no_lang'] == '/cleanup.html'){
                $out['page']=$out['class']=$out['file'] = 'cleanup';
                return $out;                
            }            
            if($out['request_uri_no_lang'] == '/review.html'){
                $out['page']=$out['class']=$out['file'] = 'review';
                return $out;                
            }
            if($out['request_uri_no_lang']=='/h404.html'){
                $out['page']=$out['class']=$out['file'] = 'h404';
                return $out;
            }  		  
            if($out['request_uri_no_lang']=='/' || empty($out['request_uri_no_lang'])){
                $out['page']=$out['class']=$out['file'] = 'home';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/paymentok.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'paymentok';
                return $out;
            } 
            if(strpos($request_uri_no_lang,'/maattabellen.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'maattabellen';
                return $out;
            }            
            if(strpos($request_uri_no_lang,'/contant.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'contant';
                return $out;
            }                  
            if(strpos($request_uri_no_lang,'/overboeking.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'overboeking';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/rembours.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'rembours';                
                return $out;
            }                                       
            if(strpos($request_uri_no_lang,'/login.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'login';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/createaccount.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'createaccount';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/myaccount.html')===0){                  
                    $out['page']=$out['class']=$out['file'] = 'myaccount';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/basket.html')===0){                  
                    $out['page']=$out['class']=$out['file'] = 'basket';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/checkout.html')===0){                  
                $out['routed'] = 2;
                $out['page']=$out['class']=$out['file'] = 'checkout';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/checkout_sendmethod.html')===0){                  
                $out['routed'] = 2;
                $out['page']=$out['class']=$out['file'] = 'checkout_sendmethod';
                return $out;
            }	
    
            if(strpos($request_uri_no_lang,'/checkout_paymethod.html')===0){                  
                $out['routed'] = 2;
                $out['page']=$out['class']=$out['file'] = 'checkout_paymethod';
                return $out;
            }	
            if(strpos($request_uri_no_lang,'/checkout_final.html')===0){                  
                $out['routed'] = 2;
                $out['page']=$out['class']=$out['file'] = 'checkout_final';
                return $out;
            }
            if(strpos($request_uri_no_lang,'/checkout_empty.html')===0){                  
                $out['routed'] = 2;
                $out['page']=$out['class']=$out['file'] = 'checkout_empty';
                return $out;
            }
            
    
            if(strpos($request_uri_no_lang,'/contact.html')===0){                  
                $out['page']=$out['class']=$out['file'] = 'contact';
                return $out;
            }
            if(preg_match('/^\/shop\/(.+)/',$request_uri_no_lang,$matches)){                 
                $out['page']=$out['class']=$out['file'] = 'shop';
                return $out;
            }  
            if(preg_match('/^\/product\/(.+)/',$request_uri_no_lang,$matches)){                
                $out['page']=$out['class']=$out['file'] = 'product';
                return $out;
            } 
            if(preg_match('/^\/article\/([a-z0-9_\/-]+).html$/',$request_uri_no_lang,$matches)){            
                $out['page']=$out['class']=$out['file'] = 'article';
                return $out;
            }  	   		
            if(strpos($request_uri_no_lang,'/diverse-sportkleding')===0){                  
                $out['page']=$out['class']=$out['file'] = 'shop';
                return $out;
            } 
            // @todo deze twee queries kunnen gecombineerd worden.

    		$article =  Webshopcms::getPageByTag($out['current_webshop_id'],$strippedUrl,false);

    		if(empty($article)){
                $article = Webshopcms::getPageByUrl($out['current_webshop_id'],$strippedUrl);
            }


            if(!empty($article)){
                $_SESSION['article_loaded_from_cms_class'] = $article;
                $out['page']=$out['class']=$out['file'] = 'article';
                return $out;            
            }else{
                $_SESSION['article_loaded_from_cms_class'] = null;
                unset($_SESSION['article_loaded_from_cms_class']);   
            }             
	                        
		}

       if(preg_match('/^\/([A-Za-z_-]+)-([0-9]+).html$/',$request_uri[0],$matches)){  
            $out['page']=$out['class']=$out['file'] = 'shop';
            return $out;
        }
		if(preg_match('/^\/([a-z0-9_]+).html$/',$request_uri[0],$matches)){
            $out['page']=$out['class']=$out['file'] = $matches[1];
            return $out;
        }

        if(preg_match('/([a-z0-9_]+).php/',$request_uri[0],$matches)){            
            $out['page']=$out['class']=$out['file'] = $matches[1];
            return $out;
        }        
        if(preg_match('/pagina\/(.+)/',$request_uri[0])){
            $out['page']=$out['class']=$out['file'] = 'cmspage';
            return $out;            
        }

        if(preg_match('/^\/([a-z0-9_]+)\/([a-z0-9_]+).html$/',$request_uri[0],$matches)){		
            $out['section'] = $matches[1];
            $out['page']    = $matches[2];
            $out['class']   = $out['section'].'_'.$out['page'];
            $out['file']    = $out['section'].'/'.$out['section'].'_'.$out['page'];            
            return $out;
        }


        if(isset($path[0])){
            $aParts = explode(".", $path[0]);
            $out['page']        = current($aParts);
            $out['section']     = isset($path[1])?$path[1]:null;
        }else{
            $out['page'] = null;
            $out['section'] = null;
        }


        if(!isset($out['page'])||$out['page']=='')
            $out['page'] = 'home';

        $out['class']         =   ($out['section'])?$out['section'].'_'.$out['page']:$out['page'];

        if($out['section'])
            $out['file']        =  $out['section'].'/'.$out['section'].'_'.$out['page'];
        else
            $out['file']        =  $out['page'];

        return $out;
    }
    
    
}