<?php
class Time{
    function getYears(){
        $items =  range(2010,date('Y'));        
        foreach($items as $item)
            $result[$item]['num'] = $item;            
        return $result;       
    }
    function getMonths(){
        for($x=1;$x<=12;$x++){
            $months[$x]['desc'] = strftime('%B',mktime(1,1,1,$x,1,2010));
            $months[$x]['num']  = $x;
        }   
     return $months;
    }
}