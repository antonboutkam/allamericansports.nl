<?php
class Hostingwidget{

    function getDescription($lang){
        if($lang=='nl')
            return 'Met deze widget kunt u zien wat uw hostinggebruik is.';
        return 'With this widget you can check your hostingusage.';
        
    }
    function getDefaultWidth(){
        return 'half';
    }    
    function getAcceptedSizes(){
        return array('quarter','half');
    }               
    function getWidth(){
        $width = BackofficeWidget::getCurrentWidgetWidth(__CLASS__);
        if(empty($width))
            $width = $this->getWidth();
        return $width;
    }   
    function getTitle($lang){
        if($lang=='nl')
            return 'Hosting gebruik';
        return 'Hosting usage';        
        
    }
    function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){           
        $params['widget_name']      = strtolower(__CLASS__);
        $params['hosting']          = Hosting::getDiskUsage();        
        
        $params['widget_title']     = self::getTitle($params['lang']);
        return parse('widgets/hosting/hosting',$params);                
    }
}