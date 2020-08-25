<?php
class WidgetFactory{
    static function getWidget($idOrWidgetName){
            if(is_numeric($idOrWidgetName)){
                $widgetName = BackofficeWidget::getWidgetNameById($idOrWidgetName);
            }else{
                $widgetName = $idOrWidgetName;
            }
            
		    $widgetNameShort          = str_replace('widget','',$widgetName);
		    $path                    = './templates/backoffice/_default/widgets/'.$widgetNameShort.'/'.$widgetNameShort.'.php';            
            require_once($path);
                    
                                             
            $class                   = new $widgetName;
            $class->imgPath          = './templates/backoffice/_default/widgets/'.$widgetNameShort.'/icon-32x32.png';        
        return $class;           
    }
        
}
