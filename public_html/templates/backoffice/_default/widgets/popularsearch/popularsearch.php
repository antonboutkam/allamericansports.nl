<?php
class Popularsearchwidget{

    function getDescription($lang){
        if($lang=='nl')
            return 'Met deze widget kunt u zien waar het meest op gezocht word.';
        return 'This widget shows the most searched results.';
        
    }
    function getDefaultWidth(){
        return 'half';
    }            
    function getAcceptedSizes(){
        return array('half','quarter');
    }  
    function getWidth(){
        $width = BackofficeWidget::getCurrentWidgetWidth(__CLASS__);
        if(empty($width))
            $width = $this->getWidth();
        return $width;
    }     
    
    function getTitle($lang){
        if($lang=='nl')
            return 'Populairste zoekresultaten';
        return 'Most popular searchresults';        
        
    }
    function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){                    
        $params['widget_name']              = strtolower(__CLASS__);       
        $params['most_popular']             = Statistics::getMostPopularQueries();    
        $params['widget_title']             = self::getTitle($params['lang']);
        return parse('widgets/popularsearch/popularsearch',$params);               
    }
}