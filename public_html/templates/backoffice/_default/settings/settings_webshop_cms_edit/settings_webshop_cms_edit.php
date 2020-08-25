<?php
require_once('htmlawed.php');
class Settings_webshop_cms_edit{
    function  run($params){
         
        $params['edit_mode']    =   isset($params['edit_mode'])?$params['edit_mode']:'wysiwyg';
                             
        $params['webshops']     =  Webshop::getAvailable();
        
        if(!isset($params['webshop_id']))
            $params['webshop_id'] = $params['webshops'][0]['id'];
                    
        if($params['clearaftercms']=='true')
            $params['id'] = 'new';
			
        if($params['_do']=='move'){
            Webshopcms::move($params['id'], $params['direction']);
            exit(json_encode(array('status'=>'ok')));
        }                
        if($params['_do']=='del_img'){
            Webshopcms::deleteCmsImage($params['id']);
            exit(json_encode(array('status'=>'ok')));			            
        }     		
        if($params['_do']=='update_tag'){
            Webshopcms::updateCmsImageTag($params['id'], $params['alttag']);
            exit(json_encode(array('status'=>'ok')));			            
        }      		
        if($params['_do']=='store'){

            $id = Webshopcms::store($params['id'],$params['webshop_id'],$params['title'],$params['content'],$params['in_footer_menu'],$params['tag'],$params['about_txt'],1,$params['footer_col_one'],$params['footer_col_two'],$params['footer_col_three'],$params['fk_locale'],$params['url'],$params['layout_manager'],$params['weight'], $params['fk_menu_tree']);

            if($_FILES['picture']['name'])
                Webshopcms::storeCmsImage($params);	          
            
			if(isset($params['opslaan_next']))
				 redirect($params['root'].'/settings/webshop_cms.html');        
			
			if(isset($params['opslaan'])){
				$iframe	='';
				if($params['iframe'])
					$iframe = '&iframe=1';				
				redirect($params['root'].'/settings/webshop_cms_edit.html?_do=edit&id='.$id.'&webshop_id='.$params['webshop_id'].$iframe);
			}
		}          
        $params['languages'] = TranslateWebshop::getWebshopLocales($params['webshop_id']);
        if(!isset($params['fk_locale']))
        {
            $params['fk_locale'] = TranslateWebshop::getDefaultLocale($params['webshop_id']);
        }

                    
        $params['page']     =   Webshopcms::getPageById($params['id']);


        $tidy = htmLawed($params['page']['content'], array('tidy'=>'some value'));
        $params['page']['content_enc']     = preg_split("/\\r\\n|\\r|\\n/", $tidy);          
        unset($params['page']['content_enc'][0]);
        if(!empty($params['page']['content_enc']))
            foreach($params['page']['content_enc'] as $line)                
                $params['page']['js_string'] .= '"'.addslashes($line).'\n" + ';         
        $params['page']['js_string']  .= '""';

        $params['full_menu_tree'] = Webshop::getFullMenuTree();

        if(!empty($params['page']['configurable_vars']))
            $params['page']['configurable_vars_all'] = '['.join('], [',explode(',',$params['page']['configurable_vars'])).']';
        
        #_d($params['page']);
        #exit($params['page']['configurable_vars_all']);
        $params['cmsimg'] 	=   Webshopcms::getCmsImages($params['id']);
           
                                 
        $params['content']  =   parse('settings_webshop_cms_edit',$params,__FILE__);
        return $params;        
    }
}