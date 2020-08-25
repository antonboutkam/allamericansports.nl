<?php
class Customer_edit{
    function  run($params){
        if($params['_do']=='store')
            $id = RelationDao::store($params['customer'],$params['id']);
        if($id)
            $params['id'] = $id;
  
        $defaults = RelationDao::getDefaults();

        if($params['id'])
            $relation = RelationDao::getById($params['id']);

        $params['webshops'] =   Webshop::getAvailable(true);
        $users              =   User::getAll();
        $params['users']    =   $users['data'];

        if(trim($relation['cp_firstname'])=='' && trim($relation['cp_firstname'])=='' && trim($relation['cp_firstname'])==''){
            $relation['cn_unknown'] = '0';
        }else{
            $relation['cn_unknown'] = '1';
        }
        
        foreach($defaults as $field=>$value)
            if($relation[$field]=='')
                $relation[$field] = $value;
                
        if(is_array($relation))
            $params = array_merge($params,$defaults,$relation);                   
        
        $params['content'] = parse('customer_view',$params,__FILE__);
        echo "hier";
        exit();
        return $params;
    }
}