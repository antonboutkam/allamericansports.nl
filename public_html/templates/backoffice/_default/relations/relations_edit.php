<?php
class Relations_edit{
    function  run($params){
        $oExactApi = ExactHandleOath::handle($_SERVER['REQUEST_URI']);

        if($params['_do']=='store'){
            $id = RelationDao::store($params['customer'],$params['id']);
            $oExactRelation = new ExactRelation($oExactApi, Cfg::get('EXACT_DIVISION'));
            $oExactRelation->upload($id);
        }
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

        $params['latest_emails'] = MailLogDao::getXlatestEmailsTo($relation['email'],20);        
        $params['content'] = parse('relations_edit',$params,__FILE__);

        return $params;
    }
}