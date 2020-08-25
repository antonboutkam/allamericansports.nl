<?php
class Orders_overview{
    function  run($params){
		if($params['_do']=='resend_orderemail'){
			Mailer::sendOrderMail($params,$params['id']);	
			//Mailer::sendPaymentMail($params,$params['id']);
		}
		if($params['_do']=='resend_paymentemail'){
			Mailer::sendPaymentMail($params,$params['id']);
		}
		
		if($params['_do']=='mark_paid'){
			OrderDao::markPaid(User::getId(),$params['id']);
            Mailer::sendPaymentMail($params,$params['id']);
        }	
        $params['current_page']     =   ($params['current_page'])?$params['current_page']:1;
        $params['sort']             =   ($params['sort'])?$params['sort']:'o.id DESC';
        
        
        if($params['_do']=='undelete_order'){
            OrderDao::setProp($params['id'],'is_deleted','0');
        }
        if($params['_do']=='delete_order'){
            OrderDao::removeOrder($params['id']);
			OrderDao::setProp($params['id'],'montapacking_action','delete');			
			OrderDao::setProp($params['id'],'montapacking_stat','unknown');			
			
		}		
        if($params['view']=='picker'){
            $conditions[]           =   '((o.accepted IS NOT NULL AND o.paid IS NOT NULL) OR r.buyoncredit=1 OR LOWER(pm.name)="rembours")';
            $conditions[]           =   'o.picked IS NULL';
        }else{
            // defaults            
            /*
            if(!isset($params['picked']))
                $params['picked'] = '0';
            /*
            if(!isset($params['accepted']))
                $params['accepted'] = '0';             
             */
             
            if(!isset($params['year']) && !isset($_SESSION['orders_overview']['year']))
                $params['year'] = date('Y');
            else if(isset($_SESSION['orders_overview']['year']) && !isset($params['year'])){
                $params['year'] = $_SESSION['orders_overview']['year']; 
            }
            $_SESSION['orders_overview']['year'] = $params['year'];
            
            
     
                
            // filters                                            
            foreach(array('paid','picked','accepted') as $field)
                if($params[$field]!='')
                    $conditions[]       =   sprintf('o.%s %s',$field,($params[$field]==1)?'IS NOT NULL':'IS NULL');
            foreach(array('user_id','relation_id','location_id') as $field)
                if($params[$field]!='')
                    $conditions[]       =   sprintf('o.%s =%d',$field,$params[$field]); 
            foreach(array('year','month','day') as $field)
                if($params[$field]!='')
                    $conditions[]       =   sprintf('%s(o.order_date) = %d',strtoupper($field),$params[$field]);                                                                                            
        }
        
        $users                      =   User::getAll();
        $params['users']            =   $users['data'];
        $params['customers']        =   RelationDao::getAllWithOrders();
        
        
        
        $params['locations']        =   WarehouseDao::getLocations();     
        $params['years']            =   range(2010,date('Y'));        
        $params['days']             =   range(1,31);                     
        $data                       =   OrderDao::find($conditions,$params['current_page'],$params['sort'],false,$params['show_deleted']);
        
        $params['orders']           =   $data['data'];
        
        foreach($params['orders'] as $key => &$val){           
            $val['total_vis'] = number_format(Billgen::run(array('return_totals_no_pdf'=>1,'orderid'=>$val['id'])),2,",",".");    ;                              
        }
        
        $params['rowcount']         =   $data['rowcount'];
        $params['paginate']         =   paginate($params['current_page'],$params['rowcount']);        
        $params['rand']             =   rand(0,999999);
        if($params['orders'])
            $params['orders_tbl']   =   parse('orders_tbl',$params,__FILE__);
                    
        if($params['ajaxresult']){
            exit(json_encode($params));
        }                       
        return $params;
    }
}