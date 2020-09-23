<?php
class Products_Edit_Multiimg extends Products_edit_abstract {

	public function _doFirst($params){                                                       
        return $params;
	}
    public function _doBeforeSave($params){  
        return $params;
    }
    public function _doAfterSave($params){
//        if($_FILES['extraimage']['name'])
//            Image::storeExtra($params['id']);
        return $params;
    }
	public function addContents($params){								
        $params['extra_images'] = Image::getExtraImages($params['id']);            
        if(count($params['extra_images']))
            $params['extra_images'][count($params['extra_images'])-1]['is_last'] = true;
                        
		
        $params['multi_image_edit']     = parse('multi_image_edit',$params,__FILE__);

        echo "<h1>Template</h1>";
        echo parse('products_edit_multiimg',$params,__FILE__);;

        $params['plugins']              .= parse('products_edit_multiimg',$params,__FILE__);
        
        
		return $params;
	}

}