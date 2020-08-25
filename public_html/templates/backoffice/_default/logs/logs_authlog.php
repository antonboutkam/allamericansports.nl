<?php
class Logs_authlog{
    function  run($params){
        $users                  = User::getAll(null,true);
        $params['users']        = $users['data'];
        $params['current_page'] = isset($params['current_page'])?$params['current_page']:1;
        if($params['current_page'])
            $limit              = sprintf('LIMIT %d, %d', $params['current_page']*Cfg::get('items_pp')-Cfg::get('items_pp'),Cfg::get('items_pp'));
        
        if(isset($params['user'])&&$params['user']!=''){
            $where = sprintf("AND u.id = %d",$params['user']);
        }
        
        $sql = sprintf('SELECT 
                            SQL_CALC_FOUND_ROWS
                            *,
                            u.id user_id
                        FROM 
                            authentication_log al,
                            users u
                        WHERE
                            al.user_id=u.id     
                        %s                       
                        ORDER BY al.loggedin DESC
                         %s',$where,$limit);

        $params['data']         = fetchArray($sql,__METHOD__);
        $params['rowcount']     = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        $params['paginate']     = paginate($params['current_page'],$params['rowcount']);                            
        return $params;
    }
}