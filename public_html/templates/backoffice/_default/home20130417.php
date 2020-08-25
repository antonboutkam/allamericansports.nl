<?php
class Home{
    function  run($params){                        
        if($params['_do']=='delete_note')
            NotesDao::delete($params['id']);

        $params['firstname']                = User::getFirstname();    
        $params['pick_count']               = OrderDao::getPickCount();
        $params['latest_searches']          = Statistics::getLatestQueries();
        $params['latest_google_queries']    = Statistics::getLastGoogleQueries(5);
        
        #pre_r($params['latest_google_queries']);
        $params['most_popular']             = Statistics::getMostPopularQueries();              
        $params['notes']                    = NotesDao::getAll(User::getLocaton());
        $params['notes']                    = parse('inc/notes',$params);        
        $params['hosting']                  = Hosting::getDiskUsage();
				
        if($params['view']=='picker'){
            $conditions[]           =   '((o.accepted IS NOT NULL AND o.paid IS NOT NULL) OR r.buyoncredit=1 OR LOWER(pm.name)="rembours")';
            $conditions[]           =   'o.picked IS NULL';
        }
        $params['latest_orders']    =   OrderDao::find($conditions,1,'o.id DESC',10);
        
        $params['content']      = parse('home',$params);        
        return $params;
    }
}