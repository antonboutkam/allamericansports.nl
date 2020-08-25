<?php
class Search{
    public static function run($params){  
        Log::search($params['query'],$params['current_webshop_id']);

		Translate::init($params['lang'],'shop');
       // $tmpTrans = Translate::getTranslation();  
		
        $params['query']                = htmlentities($params['query']);
        
        
        
        $params['query']                = ($params['query']);
        $itemsPP                        = 10;
        $params['current_page']         = (isset($params['current_page']))?$params['current_page']:1;
        $limit                          = sprintf('LIMIT %d, %d',$params['current_page']*$itemsPP-$itemsPP,$itemsPP);
        #echo 'webshopid '.$params['current_webshop_id'];
        
        $locale = $params['lang']=='gb'?'en':'nl';
        $vat = Cfg::get('btw');
        $sql = sprintf('SELECT 
                SQL_CALC_FOUND_ROWS            
                wm.menu_item menu_item,
                wmp.menu_item parent_menu_item,                                
                c.sale_price * 1.'.Cfg::getPref('btw').' sale_price_vat,                
                c.advice_price * 1.'.Cfg::getPref('btw').' advice_price_vat,
                c.*,
                ct.*,
                c.id,
                pcg.fk_product_group group_id,
                (SELECT type FROM product_type pt WHERE pt.id=c.`type`) as type_vis
            FROM 
                webshop_menu wm
                LEFT JOIN webshop_menu wmp ON wm.fk_parent=wmp.id,                
                catalogue_menu cm,
                catalogue c                
                LEFT JOIN catalogue_translation ct ON ct.fk_catalogue=c.id AND ct.fk_webshop=%3$d AND ct.fk_locale=(SELECT id FROM locales WHERE locale="%4$s" GROUP BY id),
                catalogue c2
                LEFT JOIN catalogue_product_group pcg ON pcg.fk_catalogue=c2.id AND pcg.fk_product_group!=0   
            WHERE 
                 cm.fk_webshop_menu = wm.id
                 AND c.id = c2.id
                 AND cm.fk_catalogue = c.id
                 AND c.in_webshop=1
                 AND c.deleted IS NULL                                 
                 AND (
                           wm.menu_item_lower LIKE "%%%1$s%%" 
                        OR wmp.menu_item_lower LIKE "%%%1$s%%"
                        OR c.ean LIKE "%%%1$s%%" 
                        OR c.description LIKE "%%%1$s%%"
                        OR c.title LIKE "%%%1$s%%"
                        OR c.article_name LIKE "%%%1$s%%")                                                                                                                                      
              GROUP BY IF(pcg.fk_product_group IS NULL,c.id,pcg.fk_product_group)
             --   GROUP BY c.id
            %2$s                                    
            ',quote(trim(strtolower($params['query']))),
                $limit,
                $params['current_webshop_id'],
                quote($locale));
            
            # exit(nl2br($sql));
            # mail('robert@nuicart.nl','search',$sql);
            # echo nl2br($sql)."<br>";
            $products = fetchArray($sql,__METHOD__);
            if(!empty($products)){
                foreach($products as $id=>$product){
                    $products[$id]['sale_price_vat_vis'] = number_format($products[$id]['sale_price_vat'],2,",",".");
                    $products[$id]['spotlight_title']   = readMore($product['title'],0,23);
                    $products[$id]['title_encoded']     = stripSpecial($product['title']);
                }                
            }
            #echo pre_r($products);
                             
            $params['products']['data']     = self::chunks($products);
            $params['products']['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);                        
            
            
            $params['products']['pages']    = paginate($params['current_page'],$params['products']['rowcount'],$itemsPP,'searchresgoto');
             #pre_r($params['products']['data']);
            $params['full_width']   = true;
            $translation            =  Translate::getTranslation();
            
            $params['page_title']   = $translation['trans_foundresults'].' "'.preg_replace('/[^a-zA-Z0-9 _-]+/','',$params['query']).'"';
            $params['content']      = parse('inc/products',$params);           

        if(isset($params['ajax'])){
            unset($params['products']);        
            exit(json_encode($params));
        }else{
            $params = Webshop::doFirst($params);
        }   
        return $params;     
    }
    
    private static function chunks($data){
        if(empty($data)||!is_array($data))   
            return;
        $c = $i = 1;            
        foreach($data as $row){
            
            $row['last'] = ($c==5)?'last':'';            
            $row['c'] = $c;
            $out[$i]['items'][] = $row;
            $c = $c + 1;
            if($c==6){
                $c = 1;
                $i = $i + 1;                
            }                        
        }
        return $out;
    }    
}
