<?php
class Reports{
    function  run($params){
        $params['years']                    = Time::getYears();
        $params['months']                   = Time::getMonths();
        $params['year']                     = (isset($params['year']))?$params['year']:date('Y');
        $params['month']                    = (isset($params['month']))?$params['month']:date('n'); 
        //$params['title']                    = 'reports';   
        $params['disp_type_filter']         = '0';     
        //$params['disp_time_group_filter']   = '1';              
        $params['report_box']               = parse('inc/report_box',$params);              
        return $params;
    }
}