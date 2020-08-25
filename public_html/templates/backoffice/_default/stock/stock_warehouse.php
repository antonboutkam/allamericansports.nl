<?php
class Stock_warehouse{
    function  run($params){
        // locationId   -- actual warehouse_location
        // location     -- id of rack/shelf/path
        if(!isset($params['location']))
            $params['location']                     = User::getLocaton();
                
        if(!isset($params['oldLocation']))
            $params['oldLocation'] = $params['location'];
        
        // Get the full warehouse config. (all racks + shelfs)        
        $params['configuration']                    =   WarehouseDao::getConfiguration($params['location'],'path,rack,shelf');        
        $params['locations']                        =   WarehouseDao::getLocations();

        $params['warehouse_view_tbl']               =   parse('inc/warehouse_view_tbl',$params);
                       
        if(in_array($params['view'],array('mover','picker'))){     

            $params['location_name']                =   Location::getName($params['location']);      
            $product                                =   ProductDao::getById($params['productId']);
            $params                                 =   array_merge($product,$params);                    
            $oldLocationProps                       =   WarehouseDao::getLocationProductProps($params['locationId'],$params['productId']);
            
            if($oldLocationProps['quantity'])
                for($x=1;$x<=$oldLocationProps['quantity'];$x++)
                    $params['range'][]              =   array('item'=>$x);

            $locations                              =   WarehouseDao::getWarehouseProductConfigurations($params['productId'],$params['location']);
      
            $prevView                               =   $params['view'];
            if(!empty($locations)){                
                foreach($locations as $tmp)
                    $locs[]                         =   $tmp['id'];                                                                 
                $filters[]                          =   "wc.id IN (".join(',',$locs).")";
                $filters[]                          =   sprintf("s.product_id=%d",$params['productId']);                
                $params['view']                     =   'other_locations';                          
                $params['configuration']            =   WarehouseDao::getConfiguration($params['location'],'path,rack,shelf',$filters);                            
            }else
                unset($params['configuration']);            
            $params['view']                         =   $prevView;
            
            $params['warehouse_standard_locations'] =   parse('inc/warehouse_view_tbl',$params);
            
        }                        
        return $params;
    }
}