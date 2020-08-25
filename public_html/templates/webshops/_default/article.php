<?php
class Article{
    public static function run($params){                    
        // '/article/108/verzendkosten.html'
        
        $regEx = '#\/article\/([0-9]+)\/[a-zA-Z0-9-]+.html#';
        $byId = preg_match($regEx,$params['request_uri'],$matches);
        
        if(!empty($_SESSION['article_loaded_from_cms_class'])){
            
            $params['article']      = $_SESSION['article_loaded_from_cms_class'];
            $params['cmsimg'] 	    = Webshopcms::getCmsImages($params['article']['id']);             
        }else if($byId){            
            $params['article']      = Webshopcms::getPageById($matches[1]); 
            $params['cmsimg'] 	    = Webshopcms::getCmsImages($params['id']);            
        }else{            
            $tag                    = str_replace('.html','',basename($params['request_uri']));            
            $params['article']      = Webshopcms::getPageByTag($params['current_webshop_id'],$tag,false);
            $params['cmsimg'] 	    = Webshopcms::getCmsImages($params['id']);
        }        
		         
                                         
        $config['leftup_rightup']       = array('sections'=>array(29,43,28),'template'=>'leftup_rightup');
        $config['leftdown_rightdown']   = array('sections'=>array(29,43,28),'template'=>'leftdown_rightdown');        
        $config['one_leftup']           = array('sections'=>array(25,38,37),'template'=>'one_leftup');
        $config['one_righttup']         = array('sections'=>array(38,37,25),'template'=>'one_righttup'); 
        $config['threeup']              = array('sections'=>array(33,33,33),'template'=>'threeup');
        $config['threedown']            = array('sections'=>array(33,33,33),'template'=>'threedown');
        $config['threeright']           = array('sections'=>array(50,50),'template'=>'threeright');
        $config['threeleft']            = array('sections'=>array(50,50),'template'=>'threeleft');        
        $config['three_horiz_middle']   = array('sections'=>array(16,16,16,16,16,16),'template'=>'three_horiz_middle');
        $config['oneleft_tworight']     = array('sections'=>array(17,16,50,16),'template'=>'oneleft_tworight');
        $config['twoleft_oneright']     = array('sections'=>array(17,50,16,16),'template'=>'twoleft_oneright');
        $config['twotop_one_bottom']    = array('sections'=>array(34,33,33),'template'=>'twotop_one_bottom');
        $config['onetop_twobottom']     = array('sections'=>array(34,33,33),'template'=>'onetop_twobottom');
        $config['album']                = array('sections'=>'skip','template'=>'album');          
                             
        $current_config                 = $config[$params['article']['layout_manager']];        
        $params['title_override']       = $params['article']['title'];                                     
        $params                         = Webshop::doFirst($params);
        
        if(empty($current_config)){                
            $params['content']          = parse('article',$params);
        }else{
            if($current_config['sections']=='skip'){
                $params['article_content']  = $params['article']['content'];
            }else{
                $params['article_content']  = column_split($params['article']['content'],$current_config['sections']);
            }

            $params['content']          = parse('cms_layout/'.$current_config['template'], $params);
        }
        
        return $params;
    }
}
