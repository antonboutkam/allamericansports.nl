<?php
class Settings_sizetable{
    function  run($params){
        
        if($params['_do']=='delete-sizetable')
            Sizetables::deleteById($params['id']);        
                
        if($params['_do']=='addsizetable'){    
			$params['size'] =  array('title'=> $params['title'], 'filename' =>$_FILES['image']['name']);
            $id             = Sizetables::store($params['size'],$params['id']);
            $newFileName    = $id.'_'.$_FILES['image']['name'];
			Sizetables::storeSizeFile($newFileName);
            Sizetables::setVal($id,'filename',$newFileName);            
        }
                                                        
        $params['locations']                =   Sizetables::getAll(); #pre_r($params['locations']);   		
		$params['sizetable_tbl']            =   parse('sizetable_tbl',$params,__FILE__);
        $params['content']                  =   parse('settings_sizetable',$params,__FILE__);
                                            
        if($params['ajax'])
            exit(json_encode($params));
                                                                           
        return $params;
    }
}