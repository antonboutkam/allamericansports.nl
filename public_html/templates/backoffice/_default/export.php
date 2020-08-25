<?php
class Export{
    function  run($params){    
        
        header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header(sprintf("Content-Disposition: attachment; filename=%sexport-%s.csv",date('YmdHis'),$params['type']));
        
        if($params['type']=='phone'){
            $fields         = array('company_name','phone','phone_mobile');
            $data           = RelationDao::find($params['query'],$fields,null);
        }else if($params['type']=='mail'){    
            $fields         = array('company_name','email');
            $data           = RelationDao::find($params['query'],$fields,null);
        }
        else if($params['type']=='news-letter'){    
            $fields         = array('name','email');
            $data           = Newsletter::find($params['query'],$fields,null);
        }else if($params['type']=='cust-full'){
            $data           = RelationDao::find(array(),null,null);            
        }else if($params['type']=='catalog-full'){
            $articles       = array('c.id','c.article_number','c.size','c.article_name','c.sale_price','c.advice_price');
            if(User::getLevel()=='A')
                $articles[] = 'c.purchase_price';                        
            $data           = ProductDao::find(array(),'article_number',$articles,null);
        }else if($params['type']=='not-in-webshop'){ 
            $data['data'] = Webshop::getProductsNotInWebshop();
        }else if($params['type']=='no-alt-partnumber'){ 
            $data['data'] = Webshop::getWithoutAltPartnumber();                                    
        }else if($params['type']=='empty-menu-item'){ 
            $data['data'] = Webshop::getEmptyMenuItems();                                    
        }else if($params['type']=='stock'){
            $filter = array(); 
            if($params['location']){
                $filter['wl.id'] = $params['location'];
            }
            $data           = StockDao::find('article_number',null,$filter,null);
//            $requiredfields = array('id', 'article_number', 'size', 'article_name','description',
//                                        'purchase_price','sale_price','location','wlocid','stock_id','configuration_id','path',
//                                        'rack','shelf','quantity');

        }else if($params['type']=='stock_spread'){
            $data['data'] = StockDao::getSpreadProducts(User::getLocaton());
        }
        if(is_array($data) && isset($data['data'][0])){
            // header
            foreach($data['data'][0] as $fieldName=>$fieldVal)
                $out[] =  sprintf("'%s'",addslashes($fieldName));
            
            print  join(',',$out).PHP_EOL;
            
            foreach($data['data'] as $relation){
                $out = null;
                foreach($relation as $fieldName=>$fieldVal)
                    if(isset($requiredfields) && in_array($fieldName,$requiredfields))
                        $out[] = sprintf("'%s'",addslashes($fieldVal));
                    else if(!isset($requiredfields))
                        $out[] = sprintf("'%s'",addslashes($fieldVal));                                                                       
                print join(',',$out)."\r\n";
            }
        }        
        exit();
    }
}