<?php
class Topexitpageswidget{

    function getDescription($lang){
        if($lang=='nl')
            return 'Met deze widget kunt u zien waar bezoekers de site verlieten.';
        return 'With this widget you can see from where visitors left the site.';
        
    }
    function getDefaultWidth(){
        return 'half';
    }           
    function getAcceptedSizes(){
        return array('half','full');
    }  
    function getWidth(){
        $width = BackofficeWidget::getCurrentWidgetWidth(__CLASS__);
        if(empty($width))
            $width = $this->getWidth();
        return $width;
    }    
    function getTitle($lang){
        if($lang=='nl')
            return 'Top exit pages';
        return 'Top exit pages';        
        
    }
    function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){       
        $params['widget_name']              = strtolower(__CLASS__);
        $params['top_exit_pages']    = Statistics::getTopExitPagesLastXDays(7,10);      
        $params['widget_title']     		= self::getTitle($params['lang']);
        return parse('widgets/topexitpages/topexitpages',$params);                        
    }
}