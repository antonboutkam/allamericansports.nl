<?php
class Logs_mail{
    function  run($params){                
        $params['current_page'] = isset($params['current_page'])?$params['current_page']:1;                        
        if($params['current_page'])
            $limit              = sprintf('LIMIT %d, %d', $params['current_page']*Cfg::get('items_pp')-Cfg::get('items_pp'),Cfg::get('items_pp'));

        if($params['_do']=='filter'){
            $where = sprintf('WHERE ml.tomail LIKE "%%%1$s%%" OR ml.frommail LIKE "%%%1$s%%" OR ml.subject LIKE "%%%1$s%%"',$params['filter']);
        }
        $where =                                 
        $sql = sprintf('SELECT
                            SQL_CALC_FOUND_ROWS                 
                            ml.id,
                            ml.tomail,
                            ml.frommail,
                            ml.subject,
                            ml.mailtime
                        FROM 
                            mail_log ml
                        %s
                        ORDER BY 
                            ml.mailtime DESC                                                                                       
                         %s',$where,$limit);
        $params['data']         = fetchArray($sql,__METHOD__);
        $params['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        $params['paginate']     = paginate($params['current_page'],$params['rowcount']);                    
        
        $params['logs_mail_data']   =   parse('logs/logs_mail_data',$params);
        $params['content']          =   parse('logs/logs_mail',$params);
        
        return $params;
    }
}