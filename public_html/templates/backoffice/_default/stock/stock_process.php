<?php
class Stock_process{
    function  run($params){
        if($params['_do'] == 'set_location')            
            $_SESSION['from_to'][$params['fromStockId']] = self::getWarehouseConfigProps($params['toConfigurationId']);        
        if($params['_do']=='edit_transfer'){
            unset($_SESSION['from_to']);
        }

        if(in_array($params['_do'],array('set_location','edit_transfer','complete'))){
            $params['delivery']         =   PlaceDao::getDelivery($params['did']);
            foreach($params['delivery']  as $id=>$transfer)
                if(isset($_SESSION['from_to'][$transfer['stock_id']])){
                    $params['delivery'][$id] = array_merge($params['delivery'][$id],$_SESSION['from_to'][$transfer['stock_id']]);
                    //$params['delivery'][$id]['path'] = true;
                }else{
                    $tmp = WarehouseDao::getWarehouseProductLocations($transfer['product_id'],User::getLocaton(),'WC.*',true);
                    if(isset($tmp[0]['path'])){
                        $params['delivery'][$id]['path'] = $tmp[0]['path'];
                        $params['delivery'][$id]['rack'] = $tmp[0]['rack'];
                        $params['delivery'][$id]['shelf'] = $tmp[0]['shelf'];
                    }
                }
            if($params['_do'] == 'complete'){
                self::complete($params['did'],$params['delivery']);
            }
            $params['stock_place_tbl']  =   parse('inc/stock_place_tbl',$params);
        }

        if($params['ajax'] && $params['_do']!='complete')
            exit(json_encode($params));
        
        $params['open_transfers']       = self::getOpenDeliveries(User::getLocaton());
        $params['stock_process_open']   = parse('inc/stock_process_open',$params);
        if($params['ajax'])
            exit(json_encode($params));
            
        return $params;
    }
    private static function complete($did,$delivery){
        foreach($delivery as $row){
            $sql = sprintf('INSERT INTO stock (delivery_id,product_id,configuration_id,quantity)
                            VALUE(%d,%d,%d,%d)',
                        $row['delivery_id'],
                        $row['article_id'],
                        $row['configuration_id'],
                        $row['quantity']);

            query($sql,__METHOD__);
        }
       DeliveryDao::complete($did);
    }
    private static function getWarehouseConfigProps($configurationId){
        return fetchRow($sql = sprintf('SELECT
                                wc.id configuration_id,
                                wc.location_id,
                                wc.path,
                                wc.rack,
                                wc.shelf,
                                wl.name,
                                wl.id
                            FROM
                                warehouse_configuration wc,
                                warehouse_locations wl
                            WHERE
                                wc.location_id= wl.id
                            AND
                                wc.id=%d',
               $configurationId),__METHOD__);
    }

    
    private static function getOpenDeliveries($location){
        $sql = sprintf('SELECT
                            t.location_id to_locaton_id,
                            wlto.name to_location,
                            wlfrom.name from_location,
                            wlfrom.id from_location_id,
                            d.id delivery_id,
                            t.tid transfer_id,
                            u.full_name send_by,
                            d.current_time created_on
                        FROM
                            transfer t,
                            delivery d,
                            users u,
                            warehouse_locations wlto,
                            stock sfrom,
                            warehouse_configuration wcfrom,
                            warehouse_locations wlfrom
                        WHERE
                            t.did = d.id
                        AND t.location_id = %d
                        AND d.completed = 0
                        AND u.id=d.user_id
                        AND wlto.id=t.location_id
                        AND sfrom.delivery_id=d.id
                        AND sfrom.configuration_id=wcfrom.id
                        AND wlfrom.id=wcfrom.location_id
                        GROUP BY d.id',
               $location);
        return fetchArray($sql,__METHOD__);
    }
}