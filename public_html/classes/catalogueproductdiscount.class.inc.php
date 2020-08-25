<?php
class Catalogueproductdiscount{    
	public static function store($data,$id){	       
		return store('catalogue_product_discountgroup',array('id'=>$id),$data);
	}
    public static function markDeleted($id){
        $sql = sprintf('UPDATE catalogue_product_discountgroup SET is_deleted WHERE id=%d',$id);
        query($sql,__METHOD__);                   
    }
    public static function setVal($id,$field,$value){        
        $sql = sprintf('UPDATE catalogue_product_discountgroup SET %s=%s WHERE id=%d',
			             quote($field),quote($value),$id);
        query($sql,__METHOD__);            		        
    }
    public static function deleteById($id){
        $sql = sprintf('DELETE FROM catalogue_product_discountgroup WHERE %s=%s',
			quote($field),
			quote($val));
        query($sql,__METHOD__);            				 
    }
  
    public static function deleteBy($field,$val){
        query(sprintf('DELETE FROM catalogue_product_discountgroup WHERE %s=%s',
            quote($field),quote($val)),__METHOD__);   
    }    	
	public static function getBy($field,$val){
		$sql = sprintf('SELECT * 
						FROM catalogue_product_discountgroup 
						WHERE %s=%s LIMIT 1',
			quote($field),
			quote($val));
		return fetchRow($sql,__METHOD__);		
	}
	
	public static function getById($id){
		$sql = sprintf('SELECT * FROM catalogue_product_discountgroup WHERE id=%d LIMIT 1',
			quote($id));
		return fetchRow($sql,__METHOD__);		
	}    
    
	/**
	 * Catalogueproductdiscount::find()
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
							catalogue_product_discountgroup
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
    public static function link($fk_catalogue,$fk_product_discountgroup){
        $cpg =  array('fk_catalogue'=>$fk_catalogue,'fk_product_discountgroup'=>$fk_product_discountgroup);
        store('catalogue_product_discountgroup',array('id'=>'new'),$cpg);
    }
	
	public static function unLink($link_id){
        $sql = sprintf('DELETE FROM catalogue_product_discountgroup WHERE id=%d',$link_id);
        query($sql,__METHOD__);
    }      			
	
	public static function findLinks($page=1,$itemspp=20,$filters=null,$orderBy=null,$outfields='*',$addPaginate=true){
        if($orderBy)
            $orderBy = sprintf('ORDER BY %s',$orderBy).PHP_EOL;            
        
        if($page)
            $limit = sprintf('LIMIT %d, %d',$page*$itemspp-$itemspp,$itemspp).PHP_EOL;
        
        $where = '';
        $filters[] = array('key'=>'pg.id','value'=>'cpg.fk_product_discountgroup');
        $filters[] = array('key'=>'cpg.fk_catalogue','value'=>'c.id');
                
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
							product_discountgroup pg,
                            catalogue_product_discountgroup cpg,
                            catalogue c
                            LEFT JOIN catalogue_color cc ON cc.fk_catalogue = c.id
                            LEFT JOIN colors co ON cc.fk_color = co.id                           
                        %s                            
                        %s
                        GROUP BY c.id
                        %s',
                        $outfields,$where,$orderBy,$limit);
        #echo nl2br($sql); 
        $result['data']     = fetchArray($sql,__METHOD__);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        if($addPaginate){
            //echo $page.', '.$result['rowcount'].', '.$itemspp.'<br>';
            $result['paginate'] = paginate($page,$result['rowcount'],$itemspp);
        }
        #pre_r($result);
		return $result;
	}
}