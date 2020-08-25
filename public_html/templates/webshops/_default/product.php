<?php
class Product{
    public static function run($params){        
        if(preg_match_all('/([a-z0-9]+)-([0-9]+)-([0-9]+)\.html/',$params['request_uri'],$matches)){                    
            $groupId                                = $matches[2][0];      
            $params['product_group_id']             = $groupId;                  
            $productId                              = $matches[3][0];                        
            $params['product_sizes']                = TranslatedLookup::getProductsInGroup('product_size',$groupId,$params['locale'],$productId);
            if(!empty($params['product_sizes'])){
                foreach($params['product_sizes'] as &$aSize){
                    $aSize['url'] = $params['root'].'/'.$params['lang'].'/product/'.$aSize['title_encoded'].'-'.$aSize['gid'].'-'.$aSize['product_id'].'.html';

                }
            }
            $params['has_sizes']                    = (count($params['product_sizes'])>1)?1:0;                                                                
        }else if(preg_match_all('/([a-z0-9-]+)-([0-9]+)\.html/',$params['request_uri'],$matches)){                        
            $productId                      =   $matches[2][0];
        }

        if(!isBot()){
            $sStockCheckUrl = 'http://backoffice.allamericansports.nl/stock?product_id='.$productId;
            if(isset($_SERVER['IS_DEVEL'])){
                $sStockCheckUrl = str_replace('allamericansports.nl', 'allamericansports.nuidev.nl', $sStockCheckUrl);
            }
            // Exact voorraad ophalen bij Exact online  (mag alleen vanaf het backoffice domein, en lokaal opslaan)
            file_get_contents($sStockCheckUrl);
        }

        $params['product']                              = ProductDao::getBy('c.id',$productId,false);
        if($groupId){
            $params['others_in_group'] = ProductGroup::getOthersInGroup($groupId,$params['product']['fk_size'],$params['product']['id']);
        }


                                                
        $params['product']['translated']                = TranslateWebshop::getTranslatedProductInfo($productId,$params['locale'],$params['current_webshop_id']);        
        $params['product']['translated']['description'] = nl2br($params['product']['translated']['description']);       
        if($params['product']['in_webshop']!=1){
            $params             = Webshop::doFirst($params);
            $params['content']  = parse('product-removed',$params);
            return $params;                            
        }
        if(!trim($params['product']['video_link']))            
            $params['product']['video_link'] = false;
                
       $params['not_orderable']= 0;
       
       
        if($params['product']['exact_stock']>0){
            $params['delivery_time_id'] = $params['product']['delivery_time_id'];
            $params['product']['delivery_time_vis_frontend'] = DeliveryDao::getTranslatedDeliveryTime($params['product']['delivery_time_id'],$params['lang']);                
        }else{
            $params['delivery_time_id'] = $params['product']['delivery_time_nostock_id'];
            $params['product']['delivery_time_vis_frontend'] = DeliveryDao::getTranslatedDeliveryTime($params['product']['delivery_time_nostock_id'],$params['lang']);
            if($params['product']['delivery_time_nostock_id']==22){
                $params['not_orderable']= 1;            
            }                        
        }                  
  
        $params['product']['colors']        =   ColorDao::getProductColorsCommaSeparated($productId);                        
        $params['product']['has_colors']    =   $params['product']['colors'] ? 1 : 0;
        $params['product']['usages']        =   ProductUsageDao::getProductColorsCommaSeparated($productId);
                
        if(!empty($params['product']['translated']['meta_description'])){
            $params['description']  = $params['product']['meta_description'];
        }else{
            $params['description']  = ucfirst($params['product']['color']).' '.$params['product']['product_type'];
        }
        
        if(!empty($params['product']['translated']['meta_keyword'])){
            $params['keywords']     = $params['product']['meta_keyword'];
        }else{
            $params['keywords']     = 'Allamericansports '.$params['product']['product_type'].', '.$params['product']['product_type'].', '.$params['product']['color'].' '.$params['product']['product_type'].', '.$params['product']['article_name'];
        }
        $params['title_override']   = $params['product']['translated']['title'];
        
        $params['crumble_group'] = $_SESSION['last_crumble'];
        $params['crumble_group'][] = array('lnk'=>$params['request_uri'],'lbl'=>$params['product']['translated']['title']);
        
        /*
        * Redirect if no product has been found.
        */   		       
        if(!isset($params['product']['id'])){        
          Header( "HTTP/1.1 301 Moved Permanently" );                   
           redirect($params['root'].'/');                
        }                                           
                                            
        $params                       = Webshop::doFirst($params);            
		$params['extra_images']       = Image::getExtraImages($params['product']['id']);                                 
		$params['similar_products']   = ProductDao::getRelatedProducts($params['product']['id']);
        
        $params['writereview']        = 'false'; 
		$params['relation_id']        = $_SESSION['relation']['id'];		
		$params['webshop_id']         = $params['current_webshop_id'];
		$itemreview                   = ReviewDao::find('fk_catalogue',$productId,1);
        
		$params['productreview']      = $itemreview['data'];
        $params['review_count']       = $itemreview['review_count'];
        $params['avg_review']         = $itemreview['avg_review'];
        
        if(!empty($params['productreview'])){
            foreach($params['productreview'] as $review)
                $params['total_rating'] =  $params['total_rating']+$review['rating_stars'];                        
            $params['avg_rating'] = ($params['total_rating']/ceil(count($params['productreview'])));    
            for($i=1;$i<5;$i++){
                $star = ($params['avg_rating']<=$i)?'empty':'filled';
                $params['avg_rating_array'][$i] = array('starval'=>$star); 
            }            
        }        
        
        $params['productreview_count']  =   count($itemreview['data']);                
        $params["sess_relation_set"]    =   (isset($_SESSION['relation']['id']) && $_SESSION['relation']['id']!="")?1:0;
	        
        if(isset($params['write_review']) && !isset($params['is_member']))
		    redirect('/login.html?r='.urlencode($params['request_uri'].'?write_review=true'));  		  	
        		
        if(isset($params['write_review']) && isset($params['is_member']))
			$params['writereview']='true'; 
		 		
        if($params['_do'] =='write_review'){
            if($params['review']['bot_prevent'] == 21){
                $params['review_id']	= ReviewDao::store($params['review'], null);
                if(isset($params['webshop_settings']['mailing_email'])){
                    $params['email']        = $_SESSION['relation']['email'];
                    $params['name']         = $_SESSION['relation']['cp_firstname'].' '.$_SESSION['relation']['cp_lastname'];
                    $params['password']     = $_SESSION['relation']['password'];	
                    $params['time']         = date('d-m-Y H:i:s');
                    $params['ip']           = $_SERVER['REMOTE_ADDR'];                                
                    $params['sendmailto']   = $params['webshop_settings']['mailing_email'];                 
                    Mailer::sendReviewMail($params);             
                }	
            }		 
        }        		
        if($params['product']['fk_size_table'])        
            $params['size_file']                    = Sizetables::getBy('id',$params['product']['fk_size_table']);

            if(isset($_SESSION['prodids'])){
                $currval                     =  $_SESSION['prodids'][$params['product']['id']];
                $params['prev_product']      = self::getPrevVal($_SESSION['prodids'],$currval);
                $params['next_product']      = self::getNextVal($_SESSION['prodids'],$currval);
            }
    
                        
        $params['body_class']                       = "detailpagina";
	    $params['product']['description']           = nl2br($params['product']['description']);        
        $params['social_url']                       = urlencode('http://'.$params['hostname'].$params['request_uri']);        	
        $params['phone']                            = Webshop::getWebshopSetting($params['hostname'], 'contact_phone');		  
        $filter['c.in_spotlight']                   = 1;        
        $params['spotlight']                        = ProductDao::find($filter, $sort,null,1,3, true);
        if($params['product_colors'])
            $params['product_color_list_selector']  = parse('inc/product_color_list_selector',$params);

                                                                     
        $params['product_size_list_selector']       = parse('inc/product_size_list_selector',$params);

        if(!empty($params['crumble_group'])){
            foreach($params['crumble_group'] as $row)   
                $params['crumble_str'][] = '<a href="'.str_replace('+','-',$row['lnk']).'" class="h1">'.$row['lbl'].'</a>';            
            $params['crumble_str'] = join(' <h1 class="inline" style="color:gray">&raquo;</h1> ',$params['crumble_str']);            
        }          
        $params['simular_products']                 = parse('inc/simularproducts',$params);                                         

        $params['content']                          = parse('product',$params);    
        return $params;
    }
    

    public static function getNextVal(&$array, $curr_val){
        $next = 0;
        reset($array);
    
        do{
            $tmp_val    = current($array);
            $res        = next($array);
        } while ( ($tmp_val != $curr_val) && $res );
        
        if($res){
            $next = current($array);
        }
        return $next;
    }

    public static function getPrevVal(&$array, $curr_val){
        end($array);
        $prev = current($array);
    
        do{
            $tmp_val    = current($array);
            $res        = prev($array);
        } while (($tmp_val != $curr_val) && $res );
    
        if($res){
            $prev = current($array);
        }
        return $prev;
    }
}
