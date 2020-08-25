<?php
class Home{
    function  run($params){   
        
        $params['unique_selling_points']    = Weshopusp::getWebshopUsps($params['current_webshop_id'], str_replace('gb','en',$params['lang']));
                         
        if(isset($params['_do']) && $params['_do'] =='subscribe'){
		  if(RelationDao::isMember()){
                $params['member_name']=$_SESSION['relation']['cp_firstname']." ".$_SESSION['relation']['cp_lastname'];
                $params['relation_id']=$_SESSION['relation']['id'];
		  }else{
                $params['member_name']=""; 
                $params['relation_id']="0";	
		  }			
			Newsletter::signIn($params['hostname'], $params['emailaddr'],$params['member_name'],$params['relation_id']);
            if(!empty($params['r'])){
                redirect($params['r']); 
                exit();
            }
			redirect($params['root'].'/');
        }

        $params                       = Webshop::doFirst($params);                
        $filter['c.in_spotlight']     = 1;

        if(!isset($sort)){
            $sort = null;
        }
        $params['spotlight']          = ProductDao::find($filter, $sort,null,1,5, true);                                         
        $params['article']            = Webshopcms::getPageByTag($params['current_webshop_id'],'home',true);		        	
        $params['banner_items']       = WebshopBanner::getAll($params['current_webshop_id']); #pre_r($banner_items);
                
        if($params['lang'] == 'gb' && !empty($params['banner_items']))
            foreach($params['banner_items'] as $id =>$item)
                $params['banner_items'][$id]['overlaytext'] = $item['overlaytext_en'];                
                                      
        $params['phone']              = Webshop::getWebshopSetting($params['hostname'],'contact_phone');
        $params['body_class']         = "home";
        $params['currentpage']        = 'index';		
        $params['sitemap']	          = Webshop::getSitemapTree($params['current_webshop_id'],$params['locale']);
        
        $sitemapHtml                  = array();
        
        if(!empty($params['sitemap'])){
        	foreach($params['sitemap'] as $id => &$line) {
               if(!empty($line['data'])){
        	        $sitemapHtml[] = '<div class="catagory">';
                    $sitemapHtml[] = '  <ul>';                
                    $sitemapHtml[] = '      <li><b><a class="cms navtree" href="'.$params['root'].'/'.$params['lang'].'/'.$line['url'].'.html">'.$line['label'].'</a></b></li>';
                    #$sitemapHtml[] = '<ul>';
                   	foreach($line['data'] as &$product) {
                        $sitemapHtml[] = '      <li><a class="cms navtree" href="'.$params['root'].'/'.$params['lang'].'/shop/'.$product['url'].'.html">'.$product['child'].'</a></li>';	
                   	}
                    #$sitemapHtml[] = '</ul>';
                    $sitemapHtml[] = '      </li>';
                    if(in_array($id,array(6,12,18))){
                        $sitemapHtml[] = '  <div class="cl"></div>';         
                    }
                    $sitemapHtml[] = '  </ul>';
                    $sitemapHtml[] = '</div>';
               }
        	}
        }
                                		        
		#$params['sitemap']	   = array_chunk($site_links,ceil(count($site_links)/5));
        #pre_r($params['sitemap']);
        /*
		$params['sitemapdata1']= $params['sitemap'][0];
		$params['sitemapdata2']= $params['sitemap'][1];
		$params['sitemapdata3']= $params['sitemap'][2];
		$params['sitemapdata4']= $params['sitemap'][3];
		$params['sitemapdata5']= $params['sitemap'][4];
	   */

        $params['article_2']            = Webshopcms::getPageByTag($params['current_webshop_id'],'home_'.$params['lang'].'_2',true);
        $params['article_3']            = Webshopcms::getPageByTag($params['current_webshop_id'],'home_'.$params['lang'].'_3',true);

        $params['content']          = parse('home',$params);
		$params['sidebar']          = parse('inc/sidebar',$params); 
       
        $params['sitemap_data']     = join(PHP_EOL,$sitemapHtml);
        
		//$params['sitemap_data']     = parse('inc/sitemap', $params);
        return $params;
    }    
        
}