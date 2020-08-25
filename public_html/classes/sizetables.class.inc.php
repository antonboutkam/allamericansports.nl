<?php
class Sizetables{    
	public static function store($data,$id){	       
		return store('size_table',array('id'=>$id),$data);
	}
    public static function markDeleted($id){
        $sql = sprintf('UPDATE size_table SET is_deleted WHERE id=%d',$id);
        query($sql,__METHOD__);                   
    }
    public static function setVal($id,$field,$value){        
        $sql = sprintf('UPDATE size_table SET %s="%s" WHERE id=%d',
			             quote($field),quote($value),$id);
        query($sql,__METHOD__);            		        
    }
    public static function deleteById($id){
        $sql = sprintf('DELETE FROM size_table WHERE id=%d',$id);
        query($sql,__METHOD__);      				 
    }
  
    public static function deleteBy($field,$val){
        query(sprintf('DELETE FROM size_table WHERE %s=%s',
            quote($field),quote($val)),__METHOD__);   
    }    	
	public static function getBy($field,$val){
		$sql = sprintf('SELECT * 
						FROM size_table 
						WHERE %s=%s LIMIT 1',
			quote($field),
			quote($val)); #echo $sql;
		return fetchRow($sql,__METHOD__);		
	}
	
	public static function getById($id){
		$sql = sprintf('SELECT * FROM size_table WHERE id=%d LIMIT 1',
			quote($id));
		return fetchRow($sql,__METHOD__);		
		#return fetchRow(sprintf('SELECT * FROM users WHERE id=%d',$id),__METHOD__);
	}    
    
	/**
	 * size_table::find()
	 * Zoek in de database en geef een array met data, paginering en rowcount terug.
     * In de filter array is de default operator een =, alle andere denkbare operators zijn uiteraard mogelijk.
	 * @param mixed $filters array(1=>array(key'=>'searchfield','value'=>'searchdata','operator'=>'='))
	 * @param mixed $orderBy
	 * @param mixed $page
	 * @param mixed $itemspp
	 * @return
	 */
	public static function find($page=1,$itemspp=20,$filters=null,$orderBy=null,$outfields='*',$addPaginate=true){
        if($orderBy)
            $orderBy = sprintf('ORDER BY %s',$orderBy).PHP_EOL;            
        
        if($page)
            $limit = sprintf('LIMIT %d, %d',$page*$itemspp-$itemspp,$itemspp).PHP_EOL;
        
        $where = '';
        if(!empty($filters)){
            $where = 'WHERE ';
            foreach($filters as $filter){
                $clauses[] = sprintf('%s%s%s',
                                $filter['key'],
                                ($filter['operator']?$filter['operator']:'='),
                                $filter['value']); 
            }
            $where = $where.join(PHP_EOL.'AND ',$clauses);
        }            	   
              
		$sql = sprintf('SELECT SQL_CALC_FOUND_ROWS 
						%s
						FROM
							size_table
                        %s                            
                        %s
                        %s',
                        $outfields,$where,$orderBy,$limit);
          
        $result['data']     = fetchArray($sql,__METHOD__);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        if($addPaginate){
            //echo $page.', '.$result['rowcount'].', '.$itemspp.'<br>';
            $result['paginate'] = paginate($page,$result['rowcount'],$itemspp);
        }
		return $result;
	}		
   public static function getAll($sort=null,$incDeleted=false){
        if(!$incDeleted){
              # $where = 'WHERE is_deleted=0';
			  $where="";
        }
        if($sort)
            $sort = sprintf("ORDER BY %s",$sort);
            
        $sql = sprintf('SELECT SQL_CALC_FOUND_ROWS * 
                                       FROM size_table 
                                       %s 
                                       %s',
                                       $where,$sort); #echo $sql;
		$result['data'] = fetchArray($sql,__METHOD__);							   
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        return $result;             
    }
	
	public static function storeSizeFile($filename){        
        $imgDir         = './img/sizetable/';
        
        if(!is_dir($imgDir)){
            mkdir($imgDir);
            chmod($imgDir,0777);
        }                    
        move_uploaded_file($_FILES['image']['tmp_name'],$imgDir.'/'.$filename);                                
    }     			
}