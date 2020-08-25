<?php
class Products_adwords_export {
/*    
    Campaign	Campaign Daily Budget	Languages	Geo Targeting	Proximity Targets	Ad Schedule	Ad Group	Max CPC	Display Network Max CPC	Placement Max CPC	Max CPM	CPA Bid	Keyword	Keyword Type	First Page CPC	Quality Score	Headline	Description Line 1	Description Line 2	Display URL	Destination URL	Campaign Status	AdGroup Status	Creative Status	Keyword Status	Suggested Changes	Comment
    public static function run($params){
        if($params['_do']=='create'){            
            Adwords::createCampain($params['name']);
            $_SESSION['adwords_campains'] = null;
        }
        if($params['_do']=='autocreate_addgroups'){
            $params['product_types'] = ProductTypeDao::getAll();
            foreach($params['product_types'] as $productType){                
                AdwordsCampain::createAddGroup($params['id'],$productType['type']);
            }
        }
        /*
        if($params['_do']=='autocreate_adds'){
            $filter['pt.type'] = $category;
            $products = Adwordsadds::getByProductType($params['groupname']);     
            pre_r($products);
            /*
            $params['product_types'] = ProductTypeDao::getAll();
            foreach($params['product_types'] as $productType){                
                Adwords::createAddGroup($params['id'],$productType['type']);
            }
             * 
             */        
        
        
        if($params['id'])
            $params['add_groups'] = AdwordsCampain::getAllAddGroups($params['id']);                                        
        if($params['addgroupid'])
            $params['addgroup_ads'] = AdwordsCampain::getAddgroupAdds($params['addgroupid']);
        
        
        // $_SESSION['adwords_campains'] = null;
               
           $params['campains']  = AdwordsCampain::getCampains();             
        pre_r($params['campains']);
        /*
        foreach($params['campains'] as $lalala =>$data){
            AdwordsCampain::deleteCampain($data['id']);
        } 
        */
        $params['content'] = parse('products/products_adwords_export',$params);
        return $params;
    }
}

