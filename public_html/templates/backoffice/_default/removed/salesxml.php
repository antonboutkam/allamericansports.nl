<?php
class SalesXml{
    function  run($params){
        $params['year'] = ($params['year'])?$params['year']:date('Y');
        // Omzet per maand                 
        if($params['month']!='' && $params['day']!='' ){
            $periodZoom = 'day';
        }else if($params['month']!=''){
            $periodZoom = 'month';
        }else{
            $periodZoom = 'year';            
        }
        if($params['year'])
            $params['period'] = $params['year']; 
        if($params['month'])
            $params['period'] .= '-'.$params['month'];
        if($params['day'])
            $params['period'] .= '-'.$params['day'];
        
        $params['data'] = Report::build($params['type'],$params['view'],$periodZoom,$params['year'],$params['month'],$params['day']);
                   
        if(is_array($params['data']))               
            foreach($params['data'] as $row=>$vals){
                if($vals['turnover']!='' || $vals['bruto_profit']!='')
                    $hasTurnoverOrasProfit = true;                
                $params['data'][$row]['turnover']     = ($vals['turnover']=='')?'0':$vals['turnover'];
                $params['data'][$row]['bruto_profit'] = ($vals['bruto_profit']=='')?'0':$vals['bruto_profit'];
            }

        if(!$hasTurnoverOrasProfit)
            $params['data'] = null;

        print parse('salesxml',$params);
        
        exit();
        
    }
}