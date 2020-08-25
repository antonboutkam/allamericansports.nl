<?php
class OnlineusersWidget{
    function getDescription($lang){
        if($lang=='nl')
            return 'Met deze widget kunt u zien hoeveel gebruikers er op het moment online zijn.';
        return 'With this widget you can see how many users are logged in at the moment';
        
    }
    function getDefaultWidth(){
        return 'quarter';
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
            return 'Online gebruikers';
        return 'Online users';        
        
    }
        
    function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){
        if(function_exists('sessionCount'))
        {
            $params['current_view']     = self::getWidth();
            $params['online_users']     = sessionCount();
            $params['session_info']     = sessionInfo();
            $params['widget_name']      = strtolower(__CLASS__);
            $params['widget_title']     = self::getTitle($params['lang']);
        }
        else
        {
            $params['current_view']     = 0;
            $params['online_users']     = 0;
            $params['session_info']     = 0;
            $params['widget_name']      = 0;
            $params['widget_title']     = 0;
        }
        return parse('widgets/onlineusers/onlineusers',$params);
    }
}