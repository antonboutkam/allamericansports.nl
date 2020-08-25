<?php
class RecentsalesWidget{
    
      function getDescription($lang){
        if($lang=='nl')
            return 'Met de recente verkopen widget kunt u zien welke producten het laatst zijn verkocht';
        return 'With the recent sales widget you can see which products has been sold lately.';
        
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
            return 'Verkoop statistieken';
        return 'Sales statistics';        
        
    }
    
      function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){ 
        $params['widget_name']      = strtolower(__CLASS__);
        $params['widget_title']     = self::getTitle($params['lang']);
        return parse('widgets/recentsales/recentsales',$params);    
    }
}