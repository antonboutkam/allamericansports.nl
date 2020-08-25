<?php
class WarehouseDao{
    public static function getMainWarehouseId(){
        return fetchVal($sql = "SELECT id FROM warehouse_locations WHERE country_warehouse=1",__METHOD__);
    }
    public static function deleteLocation($locationId){
        # echo "has configuration is ".self::hasConfiguration($locationId);
        if(!self::hasConfiguration($locationId)){
            query($sql = sprintf('DELETE FROM warehouse_locations WHERE id=%d',$locationId),__METHOD__);            
            return true;
        }
        return false;
    }
    public static function deleteConfiguration($configurationId,$userId){        
        if(self::hasProducts($configurationId)<=0){
            query($sql = sprintf('UPDATE warehouse_configuration SET deleted_by=%d WHERE id=%d',$userId,$configurationId),__METHOD__);
            return true;
        }
        return false;        
        
    }
    public static function hasProducts($configurationId){
        $sql = sprintf('SELECT SUM(quantity) quantity FROM stock WHERE configuration_id=%d',$configurationId);
        return fetchVal($sql,__METHOD__);
    }
    public static function hasConfiguration($locationId){
        $sql = sprintf('SELECT id 
                                        FROM `warehouse_configuration` 
                                        WHERE location_id=%d 
                                        GROUP BY location_id',$locationId);        
        # echo nl2br($sql);
        return fetchVal($sql,__METHOD__);                                           
    }

    public static function getLocations(){
        return Db::instance()->getAll('warehouse_locations','name');
    }
	public static function getWarehouseConfiguration($locationId,$sort,$where=null){
        $sql = sprintf('SELECT 
                            wc.*
                        FROM 
                            warehouse_configuration wc
                            LEFT JOIN stock s ON s.configuration_id=wc.id 
                        WHERE                             
                            wc.location_id=%1$d
                            AND wc.deleted_by IS NULL
                            %2$s 
                        GROUP BY path,rack,shelf                            
                        ORDER BY %3$s',
                        $locationId,
                        ($where)?'AND '.join(" AND ",$where):'',
                        $sort);     
                                                                                   
        $data = fetchArray($sql,__METHOD__);        
        foreach($data as $id=>$row)
            if($data[$id]['product_count']=='')
                $data[$id]['product_count'] = '0';
        return $data;   		
	}
    public static function getConfiguration($locationId,$sort,$where=null){                
        $sql = sprintf('SELECT 
                            wc.*,
                            SUM(s.quantity) as stock_count,
                            SUM(s.quantity) as product_count,
                            (SELECT COUNT(DISTINCT(product_id)) FROM stock s WHERE wc.id=s.configuration_id %2$s) as product_groups 
                        FROM 
                            warehouse_configuration wc
                            LEFT JOIN stock s ON s.configuration_id=wc.id 
                        WHERE                             
                            wc.location_id=%1$d
                            AND wc.deleted_by IS NULL
                            %2$s 
                        GROUP BY path,rack,shelf                            
                        ORDER BY %3$s',
                        $locationId,
                        ($where)?'AND '.join(" AND ",$where):'',
                        $sort);     
                                                                               
        $data = fetchArray($sql,__METHOD__);        
        foreach($data as $id=>$row)
            if($data[$id]['product_count']=='')
                $data[$id]['product_count'] = '0';
        return $data;                
    }
    public static function getLocationProductProps($locationId,$productId){
        $sql = sprintf('SELECT                         
                        s.*,                        
                        wc.*,
                        SUM(quantity) quantity 
                        FROM 
                        warehouse_configuration wc,
                        stock s
                        WHERE
                        s.configuration_id=wc.id
                        AND wc.id=%d
                        AND s.product_id=%d
                        GROUP BY s.product_id',$locationId,$productId);
        return fetchRow($sql,__METHOD__);             
    }
    public static function getLocation($locationId){
        return Db::instance()->find('warehouse_locations',array('id'=>$locationId),1);
    }  
    public static function getWarehouseProductConfigurations($productId,$locationId){
        return self::getWarehouseProductLocations($productId,$locationId,'wc.id');
    }      
    public static function getWarehouseProductLocations($productId,$locationId,$outField = 'wl.id',$autoFillBulkLocation=false){
        if($autoFillBulkLocation){
            $tmp = fetchRow($sql = sprintf('SELECT
                                                COUNT(*) configuration_count,
                                                wc.*
                                             FROM `warehouse_configuration` wc
                                             WHERE wc.location_id=%d',$locationId),__METHOD__);
            #echo nl2br($sql);
            if($tmp['configuration_count']==1){
                $result[0] = $tmp;
                return $result;
            }

        }
        $sql = sprintf('SELECT %s
                        FROM 
                            warehouse_configuration wc,
                            warehouse_locations wl,
                            stock s
                        WHERE 
                            wc.location_id = wl.id
                        AND wc.id=s.configuration_id
                        AND s.product_id=%d
                        AND wl.id=%d GROUP BY s.product_id',$outField,$productId,$locationId);
             
        return fetchArray($sql,__METHOD__);                                
    }
    /**
     * WarehouseDao::storeLocation()
     * 
     * @param mixed $id
     * @param mixed $data
     * @return
     */
    public static function storeLocation($id,$data){        
        if(!$id)
            $search = Db::instance()->find('warehouse_locations',array('name'=>$data['name']),1);
               
        return Db::instance()->store('warehouse_locations', array('id'=>$id),$data);                                    
    }
    public static function storeConfiguration($locationId,$data){                        
        if($data['new'] && !empty($data['new']['path']) && !empty($data['new']['rack']) && !empty($data['new']['shelf']))                    
            Db::instance()->insert('warehouse_configuration',array_merge($data['new'],array('location_id'=>$locationId)));        
        unset($data['new']);
                   
        if(is_array($data) && !empty($data))            
           foreach($data as $rowId=>$row){
                $update = array_merge($row,array('location_id'=>$locationId));
                Db::instance()->update('warehouse_configuration',$update,array('id'=>$rowId));
           }                    
    }
                       
}