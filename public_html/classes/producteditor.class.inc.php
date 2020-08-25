<?php
class ProductEditor{
    /**
     * ProductEditor::store()
     * 
     * @param mixed $params
     * @param mixed $product_id
     * @param bool $otherItemInBatch if true we remove unique fields like ean code, article_number etc
     * @return
     */
    public static function store($params,$product_id,$skipUniqueFields=false,$plugins){        
       
        if($skipUniqueFields){
            unset($params['product']['article_number']);
            unset($params['product']['ean']);
            unset($params['product']['article_name']);            
            unset($params['product']['fk_size']);
        }                
        $params         = BackofficePlugin::trigger('_doBeforeSave',$plugins,$params);                      
        $id             = ProductDao::store($params['product'],$product_id);
                       
        $params['id']   = $id;                                             
        $params         = BackofficePlugin::trigger('_doAfterSave',$plugins,$params);
                                                                         
        TranslateWebshop::store($id,$params['catalogue_translation']);
        
        if($params['modules']['module_barcodes'])         
            BarcodeDao::storeProductProps($params['barcodes'],$id);
        if($_FILES['image']['name']){
            Image::store($id);
        }                                                        
		if($_FILES['pdf']['name'])
            ProductDao::storeProductPdf($id);	
        return $id;         
    }
    
}