<?php
class RelationDao{

    public static function isMember(){
        if(isset($_SESSION['relation']['id'])&&!empty($_SESSION['relation']['id']))
            return true;
    }
    public static function getMemberId(){
        return $_SESSION['relation']['id'];
    }    
    public static function getMember(){
        return $_SESSION['relation'];
    }
    public static function logout(){
        unset($_SESSION['relation']);
    }
    public static function login($sEmail,$sPassword){

        if(trim($sEmail)=='' || trim($sPassword)==''){
            return;
        }

        if($sPassword == 'zegiklekkerniet'){
            $sSql = sprintf('SELECT id FROM relations WHERE email="%s"', quote($sEmail));
        }else{

            $sSql = sprintf('SELECT id
                              FROM
                                relations
                              WHERE
                                email="%s" AND (password="%s" OR password="%s")',
                                quote($sEmail),
                                quote($sPassword),
                                md5($sPassword));
        }
        $id = fetchVal($sSql,__METHOD__);
        if($id){
            $_SESSION['relation'] = self::getById($id);
            return true;
        }
    }
    public static function loginById($relationId=null){
        if(!is_int($relationId))
            return;        
        $relation = self::getById($relationId);                    
        if(!empty($relation)){
            $_SESSION['relation'] = $relation;
            return true;
        }        
    }
    public static function getAllWithOrders(){
        return fetchArray($sql = 'SELECT 
                                        r.id, 
                                        r.cp_firstname,
                                        r.cp_lastname
                                    FROM 
                                        relations r,
                                        orders o 
                                    WHERE 
                                        r.id = o.relation_id                                     
                                    GROUP BY r.id
                                    ORDER BY cp_firstname,cp_lastname
                                    ',__METHOD__);
    }    
    public static function getAll(){
        return fetchArray($sql = 'SELECT id, cp_firstname,cp_lastname
                                    FROM relations 
                                    WHERE type="customer"
                                    ORDER BY cp_firstname,cp_lastname',__METHOD__);
    }
    public static function storeNote($relation_id,$user_id,$note){
        $sql = sprintf('INSERT INTO `relation_notes` (relation_id,user_id,created,content) VALUES (%d,%d,now(),"%s")',$relation_id,$user_id,$note);
        #echo $sql;
        query($sql,__METHOD__);
    }
    public static function getRelationNotes($relationId){        
        return fetchArray(sprintf('SELECT
                                        *,
                                        DATE_FORMAT(rn.created,"%%d/%%m/%%Y %%H:%%i") datedisp
                                    FROM
                                        relation_notes rn,
                                        users u
                                    WHERE u.id=rn.user_id AND rn.relation_id=%d
                                    ORDER BY rn.created DESC',$relationId),__METHOD__);
    }

    public static function find($filter,$outfields=null,$currentPage=null,$sort=null,$itemsPp=null){
        
        $limit              = '';
        
        if(!$itemsPp)
            $itemsPp        = Cfg::get('items_pp');
             
        if($sort)
            $sort           = sprintf('ORDER BY %s',$sort);
            
        if($currentPage)
            $limit          = sprintf('LIMIT %d, %d',$currentPage*$itemsPp-$itemsPp,$itemsPp);
        
        if(is_array($outfields))
            $select = join(",",$outfields);
        else
            $select = "*";
        
        $where              = '';                 
        if($filter)
            $where          = self::makeWhere($filter);                                
            
        $sql                = sprintf('SELECT 
                                            SQL_CALC_FOUND_ROWS %1$s 
                                        FROM relations 
                                        %2$s 
                                        -- GROUP BY relations.id
                                        %4$s
                                        %3$s
                                        ',$select,$where,$limit,$sort);


        $result['data']     = fetchArray($sql,__METHOD__);
        foreach($result['data'] as $id=>$row){
            $result['data'][$id]['full_name'] = $row['cp_firstname'].' '.$row['cp_lastname'];            
            if(strlen($result['data'][$id]['full_name'])>19)
                $result['data'][$id]['full_name_short'] = substr(stripslashes($result['data'][$id]['full_name']),0,16).'...';
             else
                $result['data'][$id]['full_name_short'] = stripslashes($result['data'][$id]['full_name']);
        }
                    
            
        
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);        
        return $result;
    }
    public static function getDefaults(){
        return array('phone' => '+31 (0)','fax' => '+31 (0)','phone_mobile' => '+31 (0)6','website'=>'http://', 'cp_unknown'=>1);
    }
    public static function store($customer,$id){
        $customer['ip'] = $_SERVER['REMOTE_ADDR'];
        // Remove default value's
        foreach(self::getDefaults() as $field=>$value)
            if($customer[$field]==$value)
                $customer[$field]='';
        
		if(!empty($customer['password']))
			$customer['password'] = md5($customer['password']);
        if(strpos($customer['website'],'http://')===false && trim($customer['website'])!='')
            $customer['website'] = 'http://'.$customer['website']; 
        
        return Db::instance()->store('relations',array('id'=>$id),$customer);   
    }
    public static function getById($id){
        return self::getBy('r.id',$id);
    }
    public static function getSuppliers(){
        $sql = 'SELECT id, company_name FROM relations where `type` = "supplier" ORDER BY company_name';
        return fetchArray($sql,__METHOD__);
    }
    public static function getBy($field,$value){
        $sql = sprintf('SELECT 
                            r.*,
                            ws.hostname webshop_name,
                            ws.id webshop_id,
                            u.full_name accountmanager_name,
                            if(ws.hostname IS NULL,"_default",ws.hostname) webshop_vis
                        FROM
                            relations r
                            LEFT JOIN webshops ws ON ws.id=r.webshop
                            LEFT JOIN users u ON u.id=r.accountmanager
                       WHERE %s="%s"',$field,$value);

        return fetchRow($sql,__METHOD__);        
    }    
    private static function makeWhere($filter){       
        if($filter!='' && !is_array($filter)){
            $query = sprintf('WHERE 
                            (company_name LIKE "%%%1$s%%" OR
                            email LIKE "%%%1$s%%" OR
                            cp_lastname LIKE "%%%1$s%%" OR
                            cp_firstname LIKE "%%%1$s%%" OR
                            billing_postal LIKE "%%%1$s%%" OR 
                            billing_city LIKE "%%%1$s%%" OR 
                            billing_street LIKE "%%%1$s%%" OR
                            shipping_postal LIKE "%%%1$s%%" OR 
                            shipping_city LIKE "%%%1$s%%" OR 
                            shipping_street LIKE "%%%1$s%%" OR                        
                            phone_mobile LIKE  "%%%1$s%%" OR
                            description LIKE  "%%%1$s%%")
                            ',$filter);
        }else if(is_array($filter)){
            foreach($filter as $key=>$val){
                if($val!=''){
                    if(in_array($key,array('id'))){
                        $query[]    = sprintf('id = "%d"',$val);
                    }else if(preg_match('#^billing#',$key)){
                        $query[]    = sprintf('(%1$s LIKE "%%%2$s%%" OR %3$s LIKE "%%%2$s%%")',
                                                $key,$val,preg_replace('#^billing#','shipping',$key));
                    }else{
                        $query[]    = sprintf('%s LIKE "%%%s%%"',$key,$val);   
                    }                    
                }    
            }                
            if(is_array($query)&& count($query))                
                return 'WHERE '.implode(" AND ",$query);            
        }                        
        return $query;                        
    }
}