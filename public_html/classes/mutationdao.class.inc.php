<?php
class MutationDao{
    public static function getMutations($sorting, $grouping,$fromdate,$todate,$location=null,$currentPage = 1,$onlynegative=0){
        $extraWhere = array();
        if($location)
            $extraWhere[] = sprintf('AND wl.id=%d',$location);


        if($currentPage)
            $limit          = sprintf('LIMIT %d, %d',$currentPage*Cfg::get('items_pp')-Cfg::get('items_pp'),Cfg::get('items_pp'));
            
        $sql = sprintf('SELECT
                            SQL_CALC_FOUND_ROWS 
                            c.article_number,
                            c.id article_id,
                            d.type mutationtype,
                            SUM(s.quantity) mutations,
                            (SELECT 
                                SUM(s2.quantity) 
                                FROM
                                    stock s2,catalogue c2,delivery d2,warehouse_configuration wc2,warehouse_locations wl2
                                WHERE
                                    s2.configuration_id = wc2.id
                                    AND s2.product_id = c2.id
                                    AND s2.delivery_id = d2.id
                                    AND wl2.id = wc2.location_id
                                    AND wl.id = wl2.id
                                    AND c2.article_number = c.article_number                                                                                              
                            ) location_stock,                                    
                            wl.name location
                        FROM
                            stock s,
                            catalogue c,
                            delivery d,
                            warehouse_configuration wc,
                            warehouse_locations wl
                        WHERE
                            s.configuration_id = wc.id
                            %1$s
                            AND s.product_id = c.id
                            AND s.delivery_id = d.id
                            AND wl.id = wc.location_id 
                            AND d.`current_time` >= "%2$s 00:00:00"
                            AND d.`current_time` <= "%3$s 23:59:59"                            
                        GROUP BY %4$s ,wl.id
                        %7$s
                        ORDER BY %5$s
                        %6$s                                
                        ',join(PHP_EOL,$extraWhere),
                        $fromdate,
                        $todate,
                        $grouping,
                        $sorting,
                        $limit,
                        ($onlynegative?'HAVING SUM(s.quantity)<1':''));
                                     
        $result['data']         =  fetchArray($sql,__METHOD__);
        $result['rowcount']     =   fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);                                       
        return $result;
    }
 
 
}