<?php
class RedirectEngine {
    public static function productPage($catalogueId){
        Db::instance();
        $product        = ProductDao::getBy('oldid',$catalogueId, false);
        $translated     = TranslateWebshop::getTranslatedProductInfo($productId,$params['locale'],$params['current_webshop_id']);
        
        if(empty($product)){           
            redirect('/');            
        }else{
            #echo $_SERVER['REQUEST_URI'];
            if(preg_match('#/?(.+)?/products/details/([0-9]+)/.+#', $_SERVER['REQUEST_URI'],$matches)){            
                #/nl/product/reebok-binnenveldhandschoen-voor-de-beginnende-en-recreatieve-speler59-8399.html
                $newUrl = '/'.$matches[1].'/product/'.$product['title_encoded'].'-'.$product['id'].'.html';                
                header( "HTTP/1.1 301 Moved Permanently" ); 
                header( "Location: ".$newUrl);
                exit();                 
                
            }
            /*
            $lang       = empty($matches[1])?'nl':$matches[1];        
            $lang       = ($lang=='en')?'gb':$lang;
            */
                        
            

            #redirect();
            
        }
        
    }
    public static function categoryPage(){
        if(preg_match('#/?(.+)?/products/show/category/([0-9]+)/.+#', $_SERVER['REQUEST_URI'],$matches)){ 
            // AB Oude pagina's uit
            header( "HTTP/1.1 301 Moved Permanently" ); 
            header( "Location: /");
            exit();     
                
            
            
            
            $lang       = empty($matches[1])?'nl':$matches[1];        
            $lang       = ($lang=='en')?'gb':$lang;
            $locale_id  = Lang::getLocaleIdByLanguageCode($lang);

            $sql        = sprintf('SELECT wmt.menu_item_lower,wm.id,wm.fk_parent
                                    FROM 
                                        webshop_menu wm,webshop_menu_translations wmt
                                     WHERE                              
                                        wm.oldid=%d 
                                    AND wm.id=wmt.fk_webshop_menu
                                    AND wmt.fk_locale = %d
                                    AND fk_webshop=1',$matches[2],$locale_id);    

            $newPage   = fetchRow($sql,__METHOD__);

            if(!$newPage['fk_parent']){
                #/products/show/category/12/Honkbal -> /nl/honkbal.html
                $url = '/'.$lang.'/'.str_replace(' ','-',$newPage['menu_item_lower']).'.html';                
            }else{
                #/products/show/category/21/Schoenen -> /nl/shop/honkbal-schoenen-13.html
                $sql        = sprintf('SELECT 
                                            wmt.menu_item_lower,wm.id,wm.fk_parent
                                        FROM 
                                            webshop_menu wm,webshop_menu_translations wmt
                                         WHERE                              
                                            wm.id=%d 
                                        AND wm.id=wmt.fk_webshop_menu
                                        AND wmt.fk_locale = %d
                                        AND fk_webshop=1',$newPage['fk_parent'],$locale_id);            

                $newPageParent      = fetchRow($sql,__METHOD__);         
                $url                = '/'.$lang.'/'.str_replace(' ','-',$newPageParent['menu_item_lower']).'-'.str_replace(' ','-',$newPage['menu_item_lower']).'-'.$newPage['id'].'.html';                
            }
            header( "HTTP/1.1 301 Moved Permanently" ); 
            header( "Location: ".$url);
            exit();                 
        }        
    }
}