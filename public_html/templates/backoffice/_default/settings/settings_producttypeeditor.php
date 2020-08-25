<?php
class Settings_producttypeeditor{
    function run($params){                              
        if($params['_do']=='delete')
            TranslatedLookup::delete($params['table'],$params['delete_id']);                   
                
        if($params['_do']=='add')
            $params['fk_product_type'] = TranslatedLookup::add($params['table'],$params['add']);            
                
        if($params['_do']=='store_changes')
            TranslatedLookup::storeChanges($params['table'],$params['translated']); 
                                
        $params['product_types']    = TranslatedLookup::getAllTranslated($params['table']);
        $params['languages']        = TranslateWebshop::getAllWebshopsLocales();

        $params['content'] = parse('settings_producttypeeditor',$params);
        return $params;
    }
}