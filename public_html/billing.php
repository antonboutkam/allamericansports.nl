<?php
class Billing{
    function  run($params){         
        // only admins and commercial
        if(strpos('AC',User::getLevel())===false){
        //    redirect($params['root'].'/');
        }
       
        $params['current_page']     = ($params['current_page'])?$params['current_page']:1;          
        $params['sort']             = ($params['sort'])?$params['sort']:'o.accepted DESC';   
        $params['paid']             = isset($params['paid'])?$params['paid']:'false';
        $params['unpaid']           = isset($params['unpaid'])?$params['unpaid']:'true';
        
        // Als beide aanstaan, geen whereclause, als beide uitstaan ook niet, anders wel.
        if($params['paid']!=$params['unpaid'])
            $where[] = sprintf('o.paid %s NULL',($params['paid']=='true')?'IS NOT':'IS');

        if(User::getLevel()=='C'){
            $where[] = sprintf('o.user_id=%d',User::getId());
            $where[] = sprintf('o.send_by=%d',User::getId()); 	
            $where[] = sprintf('o.payment_approved_by=%d',User::getId());
        }
        if($params['query']!=''){            
            $where[] = sprintf('o.id LIKE "%s%%"',preg_replace('/^[0]+/','',$params['query']));
        }
                
        $data                       = Orderdao::find($where,$params['current_page'],$params['sort']);
        $params['bills']            = $data['data'];
        $params['rowcount']         = $data['rowcount'];
        
                
        $params['paginate']         = paginate($params['current_page'],$params['rowcount']);
        
        
        $params['billing_tbl']      = parse('inc/billing_tbl',$params);
        
        if($params['ajaxresult'])
            exit(json_encode($params));                                       
        return $params;
    }
}
