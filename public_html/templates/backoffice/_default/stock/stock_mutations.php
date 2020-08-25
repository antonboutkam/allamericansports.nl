<?php
class Stock_mutations{
    /**
     * Stock::run()
     * 
     * @param mixed $params
     * @return
     */
    function  run($params){
        parse_str($params['form'],$array);
        if(is_array($array))
            $params = array_merge($params,$array);   
            
            $params['location_name']        = User::getLocationName();
            $params['onlynegative']         = ($params['onlynegative'])?1:0;                     
            $params['grouping']             = ($params['grouping'])?$params['grouping']:'c.article_number';            
            $params['current_page']         = ($params['current_page'])?$params['current_page']:1;          
            $params['show']                 = (isset($params['show']))?$params['show']:'current_stock';
            $params['location']             = (isset($params['location']))?$params['location']:User::getLocaton();
            
            $params['curr_location_name']   = Location::getName($params['location']);
            
            $params['locations']            = WarehouseDao::getLocations();                
            $params['fromdate']             = ($params['fromdate'])?$params['fromdate']:date('Y-m-d',time()-604800);
            $params['todate']               = ($params['todate'])?$params['todate']:date('Y-m-d',time());             
            $params['sort']                 = ($params['sort'])?$params['sort']:'d.current_time DESC';                             
            $mutations                      = MutationDao::getMutations($params['sort'],$params['grouping'],$params['fromdate'],$params['todate'],$params['location'],$params['current_page'],$params['onlynegative']); //DeliveryDao::getMutations($params['sort'],$params['location'],$params['year'],$params['month'],$params['day'],$mutationTypes,$params['current_page'],$groupBy);
            $params['mutations']            = $mutations['data'];                        
            $params['rowcount']             = $mutations['rowcount'];
            $params['paginate']             = paginate($params['current_page'],$params['rowcount']);   
            $params['stock_mutations_tbl']  = parse('inc/stock_mutations_tbl',$params);
     
        if($params['ajaxresult']){
            print json_encode($params);
            exit(); 
        }
                       
        return $params;
    }
}