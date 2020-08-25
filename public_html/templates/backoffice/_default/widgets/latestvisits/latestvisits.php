<?php

class Latestvisitswidget{
    function getDescription($lang){
        if($lang=='nl')
            return 'Met deze widget kunt u zien wie de laatste bezoekers zijn geweest.';
        return 'With this widget you can see your latest visitors.';
        
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
            return 'Laatste bezoekers';
        return 'Latest visitors';        
    }
    
    function getLatestVisits(){
        $sql = '
            SELECT 
                    *,
                    DATE_FORMAT(start,"%e %b %H:%i") start_visible
            FROM
                conversion
            GROUP BY ip
            ORDER BY id DESC
                LIMIT 9';

        $array =  fetchArray($sql,__METHOD__);
        $out = array();
        
        foreach ($array as $i => $single){
            $out[$i]['ip']                  = $single['ip'];
            $out[$i]['browser']             = getBrowser($single['browser']);
            $out[$i]['browser_lc']          = strtolower($out[$i]['browser']);
            $out[$i]['browser_lc']          = preg_replace('/[^a-z]+/','',$out[$i]['browser_lc']);
            
            $out[$i]['landingpage']         = $single['landingpage'];
            
             if((strlen($single['landingpage']))>35){$out[$i]['landingpage_short'] = substr($single['landingpage'],0,35).'...'; }
                else {$out[$i]['landingpage_short']   = $single['landingpage'];}      
             if((strlen($single['landingpage']))>130){$out[$i]['landingpage'] = substr($single['landingpage'],0,130).'...'; }
                else {$out[$i]['landingpage']   = $single['landingpage'];}  
            $out[$i]['start_time']          = $single['start_visible'];
        }
        return $out;
    }
    
    function _do($params){
        // Doet niets                
    }
    
    function  getContents($params){ 
        $params['latest_visits']            = self::getLatestVisits();
        $params['current_view']             = $this->getWidth();
        $params['widget_name']              = strtolower(__CLASS__);
        $params['latest_searches']          = Statistics::getLatestQueries();
        $params['widget_title']             = self::getTitle($params['lang']);
        return parse('widgets/latestvisits/latestvisits',$params);               
    }
}