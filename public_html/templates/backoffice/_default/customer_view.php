<?php
class Customer_view{
    function  run($params){
        if($params['_do'] == 'store_note')
            RelationDao::storeNote($params['id'],User::getId(),$params['note']);
        if($params['id'])
            $relation = RelationDao::getById($params['id']);

        if(is_array($relation))
            $params = array_merge($params,$relation);                   

        $params['notes']            = RelationDao::getRelationNotes($params['id']);
        $params['nonotes']          = (count($params['notes'])==0)?'block':'none';
        if(count($params['notes'])>0)
            $params['customer_notes'] = parse('inc/customer_notes',$params);
        
        // Quickly lower the amount of items per page.
        $before = Cfg::get('items_pp');
        Cfg::set(array('items_pp'=>5));
        $orders = OrderDao::find(array(1=>sprintf('r.id = %d',$relation['id'])),1,'o.id DESC');        
        Cfg::set(array('items_pp'=>$before));
        
        $params['orders']       = $orders['data'];
        $params['ordercount']   = $orders['rowcount'];
        if($params['ordercount']>5)
            $params['has_more_bills'] = true;
        $params['rand']         = rand(0,9999999);        
        return $params;
    }
}