<?php
require_once('./classes/exactonline/exactbase.class.inc.php');
class Relations_suppliersoverview{
    function  run($params){    
        ExactSupplier::getAll();
         
        
        
		if(isset($params['items_pp']))
			User::setSetting(User::getId(),'items_pp_suppliers_overview',$params['items_pp']);
			
        $params['current_page']     = (isset($params['current_page']))?$params['current_page']:1;
        
        if($params['ajaxresult'] && $params['type']!='advanced')
            $params['query']        = ($params['query']==$params['defaultquery'])?'':$params['query'];
        else if($params['ajaxresult'] && $params['type']=='advanced')
            parse_str($params['query'],$params['query']);
        
        $params['sort']             = ($params['sort'])?$params['sort']:'company_name';       
		$params['items_pp']			= (int)User::getSetting(User::getId(),'items_pp_relations_overview');	
	
		if($params['items_pp']==0)		
			$params['items_pp']		= 25;			
        $relations                  = RelationDao::find($params['query'],null,$params['current_page'],$params['sort'],$params['items_pp']);
            
        $params['relations']        = $relations['data'];
        $params['rowcount']         = $relations['rowcount']; 
        $params['paginate']         = paginate($params['current_page'],$params['rowcount'],$params['items_pp']);

        $params['relations_tbl']    = parse('relations_tbl',$params,__FILE__);

        if($params['ajaxresult']){
            print $params['relations_tbl'];
            exit(); 
        }
        
        return $params;
    }
}