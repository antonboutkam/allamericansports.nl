<?php
class Brandboxdao{    
	public static function getAll($fk_webshop,$fk_locale){
        $cacheKey = 'Brandboxdao_getAll'.$fk_webshop.'_'.$fk_locale; 
        $out = GlobalCache::isCached($cacheKey);        
        if(empty($out)){            
            $tpl = 'SELECT 
                        *,
                        CONCAT("/img/brandbox/",id,"_",file_name) brandbox_img_path 
                    FROM webshop_brandbox 
                    WHERE 
                        fk_webshop=%d 
                    AND fk_locale=%d 
                    ORDER BY position';
                    
            $sql = sprintf($tpl,$fk_webshop,$fk_locale);        
            $out = fetchArray($sql,__METHOD__);
            GlobalCache::store($cacheKey,$out);
        }
        return $out; 		
	}    
    public static function deleteById($id){
        $params['brandbox'] = Brandboxdao::getById($params['id']);
        if($params['brandbox']['file_name']){
            $fileName = './img/brandbox/'.$id.'_'.$params['brandbox']['file_name'];            
            unlink($fileName);
        }                                                
        $sql = sprintf('DELETE FROM webshop_brandbox WHERE id=%d',quote($id));
        query($sql,__METHOD__);            				 
    }    
	public static function store($id,$data){    
	    $tpl         = 'SELECT id FROM webshop_brandbox WHERE fk_webshop=%d AND fk_locale=%d AND id !=%d AND position="%s"';        
        $sql         = sprintf($tpl,$data['fk_webshop'],$data['fk_locale'],$id,$data['position']);
        
        $deleteItems = fetchArray($sql,__METHOD__);
        if(!empty($deleteItems))
            foreach($deleteItems as $item)
                self::deleteById($item['id']);
        
        if(isset($_FILES['file']) && isset($_FILES['file']['name'])){	   
            $data['file_name'] = $_FILES['file']['name'];
        }
        if(empty($data['file_name']))
            unset($data['file_name']);
                        
        $id = store('webshop_brandbox',array('id'=>$id),$data);
        
        if(isset($_FILES['file']) && isset($_FILES['file']['name'])){        
            move_uploaded_file($_FILES['file']['tmp_name'],'./img/brandbox/'.$id.'_'.$_FILES['file']['name']);
        }
        return $id;
	}
	public static function getById($id){
		$sql = sprintf('SELECT * FROM webshop_brandbox WHERE id=%d LIMIT 1',
			quote($id));
		return fetchRow($sql,__METHOD__);		
	}        
//    public static function markDeleted($id){
//        $sql = sprintf('UPDATE webshop_brandbox SET is_deleted WHERE id=%d',$id);
//        query($sql,__METHOD__);                   
//    }
//    public static function setVal($id,$field,$value){        
//        $sql = sprintf('UPDATE webshop_brandbox SET %s=%s WHERE id=%d',
//			             quote($field),quote($value),$id);
//        query($sql,__METHOD__);            		        
//    }

//  
//    public static function deleteBy($field,$val){
//        query(sprintf('DELETE FROM webshop_brandbox WHERE %s=%s',
//            quote($field),quote($val)),__METHOD__);   
//    }    	
//	public static function getBy($field,$val){
//		$sql = sprintf('SELECT * 
//						FROM webshop_brandbox 
//						WHERE %s=%s LIMIT 1',
//			quote($field),
//			quote($val));
//		return fetchRow($sql,__METHOD__);		
//	}
//	
//    
//	/**
//	 * Brandboxdao::find()
//	 * Zoek in de database en geef een array met data, paginering en rowcount terug.
//     * In de filter array is de default operator een =, alle andere denkbare operators zijn uiteraard mogelijk.
//	 * @param mixed $filters array(1=>array(key'=>'searchfield','value'=>'searchdata','operator'=>'='))
//	 * @param mixed $orderBy
//	 * @param mixed $page
//	 * @param mixed $itemspp
//	 * @return
//	 */
//	public static function find($page=1,$itemspp=20,$filters=null,$orderBy=null,$outfields='*',$addPaginate=true){
//        if($orderBy)
//            $orderBy = sprintf('ORDER BY %s',$orderBy).PHP_EOL;            
//        
//        if($page)
//            $limit = sprintf('LIMIT %d, %d',$page*$itemspp-$itemspp,$itemspp).PHP_EOL;
//        
//        $where = '';
//        if(!empty($filters)){
//            $where = 'WHERE ';
//            foreach($filters as $filter){
//                $clauses[] = sprintf('%s%s%s',
//                                $filter['key'],
//                                ($filter['operator']?$filter['operator']:'='),
//                                $filter['value']); 
//            }
//            $where = $where.join(PHP_EOL.'AND ',$clauses);
//        }            	   
//              
//		$sql = sprintf('SELECT SQL_CALC_FOUND_ROWS 
//						%s
//						FROM
//							webshop_brandbox
//                        %s                            
//                        %s
//                        %s',
//                        $outfields,$where,$orderBy,$limit);
//          
//        $result['data']     = fetchArray($sql,__METHOD__);
//        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
//        if($addPaginate){
//            //echo $page.', '.$result['rowcount'].', '.$itemspp.'<br>';
//            $result['paginate'] = paginate($page,$result['rowcount'],$itemspp);
//        }
//		return $result;
//	}		
//        			
}