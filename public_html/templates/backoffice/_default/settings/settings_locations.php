<?php
class Settings_locations{
    function  run($params){             
        $params['show_editor']             = 'none';
        if($params['_do']=='set_hq'){
            query('UPDATE warehouse_locations SET country_warehouse=0',__METHOD__);
            query(sprintf('UPDATE warehouse_locations SET country_warehouse=1 WHERE id=%d',$params['location']),__METHOD__);
        }
        if($params['_do']=='delete-warehouse')
           $params['can_delete']           =   WarehouseDao::deleteLocation($params['location']);
        if($params['_do']=='delete-rack')
           $params['can_delete']           =   WarehouseDao::deleteConfiguration($params['rack'],User::getId());
                
        if($params['_do']=='store-location'){           
           $id                             =    WarehouseDao::storeLocation($params['id'],$params['location']);
           if(!$params['id'])
                $params['id']              =   $id;           
           $params['show_editor']  = 'block';
        }
                                                        
        $params['locations']               =   WarehouseDao::getLocations();          
        $params['warehouses_tbl']          =   parse('inc/warehouses_tbl',$params);

        if($params['_do']=='store-configuration'){
            WarehouseDao::storeConfiguration($params['id'],$params['warehouse_configuration']);
            $params['show_editor'] = 'block';
        }
       
        if($params['_do']=='delete-location')
            WarehouseDao::deleteLocation($params['id']);
              
        if($params['id']){
            $params['location']             =   WarehouseDao::getLocation($params['id']);
            $params['configuration']        =   WarehouseDao::getConfiguration($params['id'],'path,rack,shelf',$where);
            $params['warehouse_config_tbl'] =   parse('inc/warehouse_config_tbl',$params);
        }
                                            
        if($params['ajax'])
            exit(json_encode($params));
                                                                           
        return $params;
    }
}