<?php
class Latestsearchwidget{

    function getDescription($lang){
        if($lang=='nl')
            return 'Met deze widget kunt u zien wat uw de laatste zoekresultaten zijn.';
        return 'With this widget you can see the latest search results.';
        
    }
    function getDefaultWidth(){
        return 'half';
    }           
    function getAcceptedSizes(){
        return array('half');
    }  
    function getWidth(){
        $width = BackofficeWidget::getCurrentWidgetWidth(__CLASS__);
        if(empty($width))
            $width = $this->getWidth();
        return $width;
    }    
    function getTitle($lang){
        if($lang=='nl')
            return 'Laatste zoekresultaten';
        return 'Latest searchresults';        
        
    }
    function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){       
        $params['widget_name']              = strtolower(__CLASS__);
        $params['latest_google_queries']    = Statistics::getLastGoogleQueries(10);      
        $params['widget_title']     = self::getTitle($params['lang']);
        return parse('widgets/latestsearch/latestsearch',$params);                        
    }
}