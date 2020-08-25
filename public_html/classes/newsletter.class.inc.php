<?php
class Newsletter{
    function find($currentPage,$query=''){
        $query = quote($query);
        $itemsPP = 20;
        if($currentPage)
            $limit          = sprintf('LIMIT %d, %d',$currentPage*$itemsPP-$itemsPP,$itemsPP);        
        
        $where = '';
        if(!empty($query)){
            $where = sprintf('WHERE LOWER(email) LIKE "%%%s%%"',$query); 
        }
        $out['data']        = fetchArray(sprintf('
                                         SELECT SQL_CALC_FOUND_ROWS
                                            * 
                                            FROM 
                                            newsletter
                                            %s
                                            %s ',
                                            $where,$limit),__METHOD__);
        $out['rowcount']    = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        $out['pages']       = paginate($currentPage,$out['rowcount'],$itemsPP);
        return $out;         
    }
    function signIn($hostname,$email,$name,$relation_id='null'){
        if($relation_id=='null' && isset($_SESSION['relation']['id']))
            $relation_id = $_SESSION['relation']['id'];
        
        $sql = sprintf('INSERT IGNORE INTO newsletter
                            (relation_id,name,email,custom)
                            VALUE(%s,"%s","%s","%s")',
                        quote($relation_id),
                        quote($name),
                        quote($email),
                        quote($hostname));
        query($sql,__METHOD__);
    }    
    function signOut($hostname,$email){
        $sql = sprintf('DELETE FROM newsletter WHERE email="%s" AND custom ="%s"',
            quote($email),
            quote($hostname));
        query($sql,__METHOD__);
    }
    function signOutByRelationId($relation_id){
        $sql = sprintf('DELETE FROM newsletter WHERE relation_id="%s"',
            quote($relation_id));
        query($sql,__METHOD__);
    }
}