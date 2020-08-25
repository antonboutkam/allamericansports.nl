<?php
class StockDao{
    public static function find($sort,$currentPage=null,$filter=array(),$manualWhere=null){
        $limit = '';
        
        foreach($filter as $key=>$val){
            if(is_array($val)){
                $where[]    = sprintf(' %s %s %s',$key,$val['operator'],$val['value']);
            }else if($val!='')
                $where[]    = sprintf('%s="%s"',$key,$val);
        }                         
                         
        if($sort)
            $sort           = sprintf('ORDER BY %s',$sort);                                    
        if($currentPage)
            $limit          = sprintf('LIMIT %d, %d',$currentPage*Cfg::get('items_pp')-Cfg::get('items_pp'),Cfg::get('items_pp'));                                                  

        $sql                = sprintf('SELECT 
                                        SQL_CALC_FOUND_ROWS 
                                        c.*,
                                        c.id product_id,
                                        wl.name location,
                                        wl.id wlocid,
                                        wc.id configuration_id,
                                        s.id stock_id,
                                        wc.path,
                                        wc.rack,
                                        wc.shelf,
                                        SUM(s.quantity) as quantity,
                                        (SUM(quantity) * c.purchase_price) as purchase_price,
                                        (SUM(quantity) * c.sale_price) as sale_price                                                                                     
                                        FROM 
                                            catalogue c,
                                            stock s,
                                            warehouse_configuration wc,
                                            warehouse_locations wl
                                        WHERE
                                            s.product_id = c.id
                                        AND wc.id = s.configuration_id                                               
                                        AND wl.id = wc.location_id

                                        %3$s
                                        %4$s                                                                                                                                                                                                                                                                                                      	 
                                        GROUP BY c.id
                                        HAVING quantity > 0
                                        %2$s                                        
                                        %1$s
                                        ',$limit,
                                        $sort,
                                        !empty($where)?' AND '.join("\n AND",$where):'',
                                        $manualWhere);


        $result['data']     = fetchArray($sql,__METHOD__);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        if($filter['wl.id'] && !empty($result['data'])){
            foreach($result['data'] as $id=>$row){
                $productIds[] = $row['product_id'];    
            }               
            $sql = sprintf('SELECT 
                            CONCAT(wc.path,"-",wc.rack,"-",wc.shelf," (",SUM(s.quantity),"pcs)") prsq,
                            s.product_id
                            FROM stock s,
                            warehouse_configuration wc
                            WHERE 
                            s.configuration_id = wc.id
                            AND wc.location_id=%d
                            AND s.product_id IN(%s)
                            GROUP BY s.configuration_id,s.product_id
                            HAVING SUM(s.quantity) > 0
                            ',$filter['wl.id'],join(',',$productIds));
            #echo nl2br($sql);                                
            $locations = fetchArray($sql,__METHOD__);
            if(!empty($locations))
                foreach($locations as $location)
                    $productLocations[$location['product_id']][] = str_replace('-','',$location['prsq']);
                                                    
            if(!empty($result['data']))                
                foreach($result['data'] as $id=>$row)
                    if(!empty($productLocations[$row['product_id']]))
                        $result['data'][$id]['pathrackshelfstock'] = join(",",$productLocations[$row['product_id']]);                    
                                             
        }            
        


        
        foreach($result['data'] as $id=>$row){
            $result['sum_purchase_price']   += $row['purchase_price'];
            $result['sum_sale_price']       += $row['sale_price'];
            $result['total_products']       += $row['quantity'];
        }
        $result['sum_purchase_price']       = number_format($result['sum_purchase_price'],2);
        $result['sum_sale_price']           = number_format($result['sum_sale_price'],2);
        return $result;
    }
    public static function getConfigIdByStockId($stockId){
        return fetchVal($sql = sprintf('SELECT configuration_id FROM stock WHERE id="%d"',$stockId),__METHOD__);
    }
    public static function getProductStock($productId,$locationId=null,$groupBy=''){
        $extraWhere = '';
        if($locationId){
            $extraWhere = sprintf('AND wl.id=%d',$locationId);
        }
        $sql = sprintf('SELECT 
                            wl.name,
                            wc.path,
                            wc.rack,
                            wc.shelf,
                            SUM(s.quantity) quantity
                        FROM 
                            stock s,
                            warehouse_configuration wc,
                            warehouse_locations wl
                        WHERE
                            s.configuration_id = wc.id
                        AND wc.location_id = wl.id 
                        %s
                        AND s.product_id = %d
                        GROUP BY wc.id
                        HAVING quantity > 0',$extraWhere,$productId);
        
        return fetchArray($sql,__METHOD__);                        
    }
    public static function getCurrentTotalStock($productId){
        $sql = sprintf('SELECT 
                            SUM(s.quantity) quantity
                        FROM 
                            stock s
                        WHERE
                        s.product_id = %d',$productId);
        return fetchVal($sql,__METHOD__);            
    }
    
    public static function getDefaults(){
        return array(   'purchase_price'        => '00',
                        'purchase_price_ct'     => '00',
                        'sale_price'            => '00',
                        'sale_price_ct'         => '00');
    }
    /**
     * Returns all product types that lay on multiple locations in the warehouse.
     * @param <type> $locationId 
     */
    public static function getSpreadProducts($locationId){
        query('DROP TEMPORARY TABLE IF EXISTS products;',__METHOD__);
        query('DROP TEMPORARY TABLE IF EXISTS products_double;',__METHOD__);
        query(sprintf('CREATE TEMPORARY  TABLE products
                            SELECT
                             s.product_id,
                             s.configuration_id,
                             sum(s.quantity) quantity
                            FROM
                             stock s,
                             warehouse_configuration wc
                            WHERE
                             s.configuration_id = wc.id
                            AND wc.location_id=%d
                            GROUP BY
                            s.configuration_id
                            HAVING  sum(s.quantity) > 0
                            ORDER BY s.product_id;',$locationId),__METHOD__);

        query('CREATE TEMPORARY TABLE products_double
               SELECT p.product_id FROM products p GROUP BY p.product_id HAVING COUNT(p.product_id) > 1;',__METHOD__);
        
        $sql = sprintf('SELECT      
                                    wc.path,
                                    wc.rack,
                                    wc.shelf,
                                    p.quantity,
                                    c.article_number,
                                    c.article_name,
                                    c.size,
                                    c.description

                            FROM
                                    products p,
                                    products_double pd,
                                    catalogue c,
                                    warehouse_configuration wc
                            WHERE
                                        pd.product_id=p.product_id
                            AND 	c.id=p.product_id
                            AND 	wc.id=p.configuration_id');
        return fetchArray($sql, __METHOD__);
    }
}