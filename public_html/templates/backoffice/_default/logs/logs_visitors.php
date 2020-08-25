<?php
class Logs_visitors{
    function  run($params){                
        $params['current_page'] = isset($params['current_page'])?$params['current_page']:1;
        if($params['current_page'])
            $limit              = sprintf('LIMIT %d, %d', $params['current_page']*Cfg::get('items_pp')-Cfg::get('items_pp'),Cfg::get('items_pp'));
                                
        $sql = sprintf('SELECT 
                            SQL_CALC_FOUND_ROWS
                            c.*        
                        FROM 
                            conversion c
                        ORDER BY 
                            c.start                                                              
                         %s',$where,$limit);

        $params['data']         = fetchArray($sql,__METHOD__);
        $params['rowcount']     = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        $params['paginate']     = paginate($params['current_page'],$params['rowcount']);                            
        return $params;
    }
}