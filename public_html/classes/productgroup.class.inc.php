<?php
class ProductGroup{

    public static function getOthersInGroup($productGroupId,$excludeProductId){

        $sQuery = "SELECT                     
                        c.*,
                        colors.id color_id
                    FROM 
                        catalogue_product_group cpg, 
                        catalogue c,
                        catalogue_color cc,
                        colors
                    WHERE 
                        cpg.fk_product_group = $productGroupId
                        AND c.id=cpg.fk_catalogue
					    AND c.in_webshop = 1
					    AND cc.fk_catalogue = c.id
					    AND colors.id = cc.fk_color
					    AND c.deleted IS NULL					                    
                        AND c.id != $excludeProductId
                    GROUP BY 
                        colors.id
                    ";


        $aData = fetchArray($sQuery,__METHOD__);

        if(!empty($aData))
        {
            foreach($aData as $id=>$row)
            {
                $aData[$id]['title_encoded'] = stripSpecial($row['title']);
            }

        }
        return $aData;
    }

    public static function getOthersWithSameSize($productGroupId,$fkSize,$excludeProductId){
        
        
        $sql = sprintf('SELECT                     
                    c.*                                         
                    FROM 
                    catalogue_product_group cpg, 
                    catalogue c
                    WHERE 
                    cpg.fk_product_group=%d
                    AND c.id=cpg.fk_catalogue
					AND c.in_webshop = 1
					AND c.deleted IS NULL					
                    AND c.fk_size=%d
                    AND c.id!=%d',$productGroupId,$fkSize,$excludeProductId);
       // echo nl2br($sql);
        $data = fetchArray($sql,__METHOD__);

        if(!empty($data))
            foreach($data as $id=>$row)
                $data[$id]['title_encoded'] = stripSpecial($row['title']);
                 
        return $data;
        #$data['title_encoded']                                              
        #echo nl2br($sql);
        #pre_r($data);                                
        #exit();
    }
        
    /**
     * ProductGroup::getColorCominationsInProductgroup()
     * Geeft alle kleur varianten van een productgroup + maat combinatie terug 
     * 
     * @param mixed $pgid
     * @return void
     */
    public static function getColorCominationsInProductgroup($productId,$productGroupId,$returnFalseOnSingleResult=false){
        $fk_size = ProductDao::getProductPropBy('id',$productId,'fk_size');
        
        $sql = sprintf('SELECT
                            ca.id catalogue_id,
                            ca.title,
                            cpg.fk_product_group,
                            co.*,
                            co.id color_id,
                            GROUP_CONCAT(co.color) color_combination 
                        FROM
                            catalogue_product_group cpg,
                            catalogue ca,
                            catalogue_color cc,
                            colors co,
                            product_size ps
                        WHERE                        
                            ca.id = cpg.fk_catalogue
                        AND cc.fk_catalogue=ca.id
                        AND co.id = cc.fk_color
                        AND ps.id = ca.fk_size
                        AND cpg.fk_product_group=%d                        
                        AND ca.fk_size=%d
                        GROUP BY cc.fk_catalogue',$productGroupId,$fk_size);
        $data = fetchArray($sql,__METHOD__);
        if($returnFalseOnSingleResult && count($data)<=1){
            return;
        }
        
        if(!empty($data))
            foreach($data as $id=>$row)
                $data[$id]['title_encoded'] = stripSpecial($row['title']);
                 
        return $data;
        #$data['title_encoded']                                              
        #echo nl2br($sql);
        #pre_r($data);                                
        #exit();
    }
    
        
    public static function copyGroupMembership($productSrcId,$productDstId){
        query($sql = 'CREATE TEMPORARY TABLE catalogue_product_group_copy LIKE catalogue_product_group;',__METHOD__);
        
        query($sql = 'INSERT INTO catalogue_product_group_copy 
                SELECT * FROM catalogue_product_group
                WHERE fk_catalogue='.$productSrcId,__METHOD__);
        
        query($sql = 'INSERT INTO catalogue_product_group (fk_catalogue,fk_product_group)
                SELECT '.$productDstId.',fk_product_group 
                    FROM catalogue_product_group_copy',
                __METHOD__);        
    }       
    public static function getProductMemberOf($productId){
        $sql = sprintf('SELECT 
                        pg.* 
                        FROM
                            product_group pg,
                            catalogue_product_group cpg
                        WHERE
                            pg.id = cpg.fk_product_group
                        AND cpg.fk_catalogue = %d',$productId);
        return fetchArray($sql,__METHOD__);
    }
    public static function store($data,$id){	       
		return store('product_group',array('id'=>$id),$data);
    }
    public static function markDeleted($id){
        $sql = sprintf('UPDATE product_group SET is_deleted=1 WHERE id=%d',$id);
        query($sql,__METHOD__);                   
    }
    public static function setVal($id,$field,$value){        
        $sql = sprintf('UPDATE product_group SET %s=%s WHERE id=%d',
			             quote($field),quote($value),$id);
        query($sql,__METHOD__);            		        
    }
    public static function link($fk_catalogue,$fk_product_group){
        $cpg =  array('fk_catalogue'=>$fk_catalogue,'fk_product_group'=>$fk_product_group);
        store('catalogue_product_group',array('id'=>'new'),$cpg);
    }
    public static function unLink($link_id){
        $sql = sprintf('DELETE FROM catalogue_product_group WHERE id=%d',$link_id);
        query($sql,__METHOD__);
    }

    /*
    public static function deleteById($id){
        $sql = sprintf('DELETE FROM product_group WHERE %s=%s',
			quote($field),
			quote($val));
        query($sql,__METHOD__);            				 
    }
  
    public static function deleteBy($field,$val){
        query(sprintf('DELETE FROM product_group WHERE %s=%s',
            quote($field),quote($val)),__METHOD__);   
    } 
    */   	
	public static function getBy($field,$val){
		$sql = sprintf('SELECT * 
						FROM product_group 
						WHERE %s="%s" LIMIT 1',
			quote($field),
			quote($val));
        #echo $sql."<br>";            
		return fetchRow($sql,__METHOD__);		
	}
	
	public static function getById($id){
		$sql = sprintf('SELECT * FROM product_group WHERE id=%d LIMIT 1',
			quote($id));
		return fetchRow($sql,__METHOD__);		
	}    
    
	/**
	 * Productgroup::find()
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
                                %s,
                                IF(pg.group_name="" OR pg.group_name IS NULL,"Naamloos",pg.group_name) group_name,
                                COUNT(c.id) products_in_group,
                                pg.id pgid
						FROM
                            product_group pg
                            LEFT JOIN catalogue_product_group cpg ON pg.id = cpg.fk_product_group
                            LEFT JOIN catalogue c ON cpg.fk_catalogue = c.id
                        %s
                        GROUP BY pg.id                                                    
                        %s %s',
                        $outfields,$where,$orderBy,$limit);
        
        
        $result['data']     = fetchArray($sql,__METHOD__);
        #echo nl2br($sql)."<br><br>";
        
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        if($addPaginate){
            //echo $page.', '.$result['rowcount'].', '.$itemspp.'<br>';
            $result['paginate'] = paginate($page,$result['rowcount'],$itemspp);
        }
		return $result;
	}		
    public static function getAllSiblings($productId){
       $sql = sprintf('SELECT fk_catalogue 
                        FROM catalogue_product_group 
                        WHERE fk_product_group IN (select fk_product_group from catalogue_product_group where fk_catalogue=%d)',$productId);
       $products =fetchArray($sql,__METHOD__);
       if(!empty($products)){
            foreach($products as $product){
                $out[] = $product['fk_catalogue'];
            }
       }
       return $out;
       
    }
	public static function findLinks($page=1,$itemspp=20,$filters=null,$orderBy=null,$outfields='*',$addPaginate=true){
        if($orderBy)
            $orderBy = sprintf('ORDER BY %s',$orderBy).PHP_EOL;            
        
        if($page)
            $limit = sprintf('LIMIT %d, %d',$page*$itemspp-$itemspp,$itemspp).PHP_EOL;
        
        $where = '';
        $filters[] = array('key'=>'pg.id','value'=>'cpg.fk_product_group');
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
						%s,c.id 
                        product_id,
                        c.ean
						FROM
							product_group pg,
                            catalogue_product_group cpg,
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
