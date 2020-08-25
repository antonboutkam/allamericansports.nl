<?php
class NewestordersWidget{
    
     function getDescription($lang){
        if($lang=='nl')
            return 'Met de nieuwste orderes widget kunt u zien wat de niewste orders zijn';
        return 'With the newest order widget you can see the newest orders.';
        
    }
    function getDefaultWidth(){
        return 'full';
    }            
    function getAcceptedSizes(){
        return array('full','half','quarter');
    }  
    function getWidth(){
        $width = BackofficeWidget::getCurrentWidgetWidth(__CLASS__);
        if(empty($width))
            $width = $this->getWidth();
        return $width;
    }      
    function getTitle($lang){
        if($lang=='nl')
            return 'Laatste orders';
        return 'Newest orders';        
        
    }
    function _do($params){
        // Doet niets                
    }
    
    
    function  getContents($params){
        $conditions = null;
        $params['widget_name']              = strtolower(__CLASS__);
        $params['latest_orders']            =   OrderDao::find($conditions,1,'o.id DESC',10);
        $params['widget_title']             = self::getTitle($params['lang']);
		 $params['current_view']             = $this->getWidth();  
        return parse('widgets/newestorders/newestorders',$params);        
    }
}