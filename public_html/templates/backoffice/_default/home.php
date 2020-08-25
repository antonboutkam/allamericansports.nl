<?php
class Home{
    function  run($params){


		// Scan de widget dir en sla ontbrekende widgets op in de database.
		if(!isset($params['ajax'])){
			BackofficeWidget::registerNew();
		}
		if(!isset($params['_do'])){
            $params['_do'] = null;
        }
        if($params['_do']=='set_widget_width'){
            BackofficeWidget::setUserWidth($params['widget_name'],$params['new_size']);
            exit(json_encode(array('ok'=>true)));
        }        
        
        if($params['_do']=='set_widget_enabled_by_name'){
            $params['widget_id'] = BackofficeWidget::getWidgetIdByName($params['widget_name']);            
        }
        
        if($params['_do']=='set_widget_enabled' || $params['_do']=='set_widget_enabled_by_name'){
            BackofficeWidget::setWidgetEnabled($params['is_member']['id'],$params['widget_id'],$params['isEnabled']);            
        }

        if($params['_do']=='set_widget_sorting'){
            BackofficeWidget::setWidgetSorting($params['is_member']['id'],$params['new_order']);            
        } 
        
       
        $widgets = BackofficeWidget::getUserWidget($params['is_member']['id']);
        if(!empty($widgets)){
            foreach($widgets as $widget){                                                
			    $widgetName  = str_replace('widget','',$widget['widget_name']);
			    $path        = './templates/backoffice/_default/widgets/'.$widgetName.'/'.$widgetName.'.php';
                require_once($path);
                $widgetClasses[$widget['widget_name']] = new $widget['widget_name'];
                
           }     
       }         
        if(!empty($widgetClasses)){
            foreach($widgetClasses as $widgetObj){
                $widgetObj->_do($params);            
            }
        }
        
        $params['firstname']                = User::getFirstname();    
        $params['pick_count']               = OrderDao::getPickCount();
		
		
		// Haal en lijst met alle panels en widgets op zodat de gebruiker widgets aan en uit kan zetten.
		$params['all_widgets'] = BackofficeWidget::currentWidgets($params['lang'],$params['is_member']['id']);
		
        if(isset($params['view']) && $params['view']=='picker'){
            $conditions[]           =   '((o.accepted IS NOT NULL AND o.paid IS NOT NULL) OR r.buyoncredit=1 OR LOWER(pm.name)="rembours")';
            $conditions[]           =   'o.picked IS NULL';
        }

         if(!empty($widgetClasses)){
            foreach($widgetClasses as $widgetObj){
                $tmp['widget_content']  = $widgetObj->getContents($params);            
                $tmp['width']           = $widgetObj->getWidth();                
                $tmp['can_grow']        = count($widgetObj->getAcceptedSizes())>1;
                $tmp['accepted_sizes']  = join(' ',$widgetObj->getAcceptedSizes());
                $tmp['widget_name']     = get_class($widgetObj);
                $tmp['widget_name_lc']  = strtolower($tmp['widget_name']);
                $tmp['widget_title']    = $widgetObj->getTitle($params['lang']);

                if(!isset($params['widget_content'])){
                    $params['widget_content'] = '';
                }
                if(method_exists($widgetObj, 'enableWidgetConfig')){
                   $tmp['enable_widgetconfig'] = $widgetObj->enableWidgetConfig($params);  
                }else{
                   $tmp['enable_widgetconfig'] = false;  
                }             
                $params['widget_content'] .= parse('inc/widget_container',array_merge($params,$tmp));
            }
        }
        
        

        $params['content']      = parse('home',$params);        
        return $params;
    }
}