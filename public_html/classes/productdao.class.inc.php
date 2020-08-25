<?php
class ProductDao{
    public static function articleNumExists($article_number){
        $product = self::getBy('article_number',$article_number);
        return ($product['id'])?1:0;          
    }
    public static function eanExists($ean,$id){
        $sql = sprintf('SELECT id FROM catalogue WHERE ean="%s" AND id!=%d',quote($ean),$id);
        
        return fetchVal($sql,__METHOD__);
    }    
    public static function duplicate($new_article_number,$src_id,$copy_photos=false){
        $product = self::getById($src_id);
        unset($product['id']);
        unset($product['deleted']);
        unset($product['oldid']);
        if(!$copy_photos)
            $product['photo'] = '0';
        $product['article_number'] = $new_article_number;
        
        $productId = self::store($product,null);
        return $productId; 
    }    
    
    public static function remove($productId){
        query($sql = sprintf('UPDATE catalogue SET 
                                deleted = NOW(), 
                                deleted_by=%d, 
                                in_spotlight=0
                              WHERE id=%d',User::getId(),$productId),__METHOD__);
    }

    public static function find($filter,$sort,$outfields=null,$currentPage=null,$itemsPP=null,$incWoStock=true,$groupBy='c.id'){        
        if(!$itemsPP)
            $itemsPP = Cfg::get('items_pp');     
		
        $limit              = '';        
        if($sort)
            $sort           = sprintf('ORDER BY %s',$sort);            
        if($currentPage)
            $limit          = sprintf('LIMIT %d, %d',$currentPage*$itemsPP-$itemsPP,$itemsPP);
        if(is_array($outfields))
            $select = join(",",$outfields);
        else
            $select = "c.*, ROUND(c.sale_price / c.purchase_price,2) margin ";        
        $where              = '';

        $stock = '';

        if($filter)
            $where          = self::makeWhere($filter);             
                                                          
        if(!$incWoStock){		
           # $where .= "\nAND c.global_stock >= 1";
		   #$where .= "\nAND (SELECT SUM(quantity) FROM stock WHERE product_id=c.id) >= 1";
        }
				
		$extraJoin = ',';

        if(!isset($having)){
            $having = '';
        }

		if(strpos($where,'cm.')||strpos($where,'wm.')){
			$extraJoin = sprintf('		LEFT JOIN catalogue_menu cm ON c.id=cm.fk_catalogue
                                        LEFT JOIN webshop_menu wm ON wm.id=cm.fk_webshop_menu,');
		}
          
        $sql                = sprintf('SELECT SQL_CALC_FOUND_ROWS %1$s,
                                        pt.type type_vis,
                                        pt.id type_id,  
                                        ps.`type` size_name,
                                        LOWER(pt.type) type_vis_lower, 
                                        c.sale_price * 1.'.Cfg::getPref('btw').' sale_price_vat,                
                                        c.advice_price * 1.'.Cfg::getPref('btw').' advice_price_vat,
                                        dt.label delivery_time_vis,
                                        (SELECT SUM(quantity) FROM stock WHERE product_id=c.id) total_stock_calc, pg.fk_product_group as group_id,
                                        pcg.fk_product_group pgid,
                                        AVG(pr.rating_stars) average_rating                                        
                                        FROM catalogue c 
                                        LEFT JOIN stock ls ON ls.product_id = c.id 
                                        LEFT JOIN warehouse_configuration lwc ON lwc.id=ls.configuration_id
                                        LEFT JOIN product_type pt ON pt.id=c.type
                                        LEFT JOIN delivery_time dt ON dt.id=c.delivery_time
                                        LEFT JOIN catalogue_product_group pg ON c.id=pg.fk_catalogue
                                        LEFT JOIN product_size ps ON ps.id=c.fk_size 
                                        %7$s
                                        catalogue c2                                         
                                        LEFT JOIN catalogue_product_group pcg ON pcg.fk_catalogue=c2.id AND pcg.fk_product_group!=0,
                                        catalogue c3
                                        LEFT JOIN product_reviews pr ON pr.fk_catalogue = c3.id AND pr.review_approved IS NOT NULL                                                                                     
                                        WHERE
                                        c.deleted IS NULL
                                        AND c.id= c2.id
                                        AND c.id= c3.id                                         
                                        %5$s
                                        %2$s 
                                        GROUP BY '.$groupBy.'
                                        %6$s
                                        %4$s
                                        %3$s                                        
                                        ',$select,($where!='')?$where:'',$limit,$sort,$stock,$having,$extraJoin);
        //echo $sql;
        // echo nl2br($sql);
        // exit();
        #echo $sql;
        #mail('robert@nuicart.nl','filtered query',$sql);
        $result['data']     = fetchArray($sql,__METHOD__.$_SERVER['REQUEST_URI']);
        #pre_r($result['data']);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        
        $result['pages']        = paginate($currentPage,$result['rowcount'],$itemsPP);
        $result['prev_next']    = paginatePrevNextOnly($currentPage,$result['rowcount'],$itemsPP);
        
        
        
        if(is_array($result['data']))
            foreach($result['data'] as $key=>$val){                
               $result['data'][$key]['average_rating']          = round($val['average_rating'],0);
               $result['data'][$key]['article_name_short']      = substr($result['data'][$key]['article_name'],0,50);        
               $result['data'][$key]['sale_price_vat_vis']      = number_format($result['data'][$key]['sale_price_vat'], 2,",",".");
               $result['data'][$key]['advice_price_vat_vis']    = number_format($result['data'][$key]['advice_price_vat'], 2,",",".");
               $result['data'][$key]['title_encoded']           = stripSpecial($result['data'][$key]['title']);
               
                if($key+1==count($result['data'])){$result['data'][$key]['last']=1; }
                $result['data'][$key]['spotlight_title'] =  readMore($result['data'][$key]['article_name'],10,18);                            
            }    
        return $result;
    }

    public static function getProductLocations($productId,$warehouseId){
        $sql = sprintf('SELECT 
                            s.id stock_id,
                            s.product_id product_id,
                            s.configuration_id,
                            SUM(s.quantity) quantity
                        FROM
                            stock s,
                            warehouse_configuration wc
                        WHERE
                            s.configuration_id = wc.id
                        AND wc.location_id=%d
                        AND s.product_id=%s
                        GROUP BY wc.id
                        HAVING SUM(s.quantity)>=1',
                $warehouseId,$productId);
         return fetchArray($sql,__METHOD__);
    }
    public static function getDefaults(){
        return array(   'purchase_price'        => '00',
                        'purchase_price_ct'     => '00',
                        'sale_price'            => '00',
                        'sale_price_ct'         => '00',
                        'advice_price'          => '00',
                        'advice_price_ct'       => '00',
                        'discount'              => '0.00');
    }
    public static function setVal($field,$val,$product_id){
        $sql = sprintf('UPDATE catalogue SET %s="%s" WHERE id=%d',quote($field),quote($val),$product_id);
        #echo nl2br($sql);
        query($sql,__METHOD__);
    }
    public static function store($product,$id){                
        if($product['type']=='')
            unset($product['type']);
        $product['purchase_price']  .= '.'.$product['purchase_price_ct'];
        $product['sale_price']      .= '.'.$product['sale_price_ct'];
        $product['advice_price']    .= '.'.$product['advice_price_ct'];


        $product['description']     = nl2br(trim(strip_tags($product['description'])));
        unset($product['purchase_price_ct'],$product['sale_price_ct'],$product['advice_price_ct']);
        
        // Remove default value's
        foreach(self::getDefaults() as $sField => $sValue){

            if(isset($product[$sField]) && $product[$sField] == $sValue){
                $product[$sField] = '';
            }
        }
                           



        foreach($product as $field=>$val){
            $product[$field] = stripslashes($val);
            $product[$field] = quote($product[$field]);
        }                
        $newId = store('catalogue',array('id'=>$id),$product);
        
        
        if($newId)// for inserts
            return $newId;
        return $id;// for updates                                        
           
    }
    public static function getIdBy($fieldName,$fieldValue){
        return fetchVal($sql = sprintf('SELECT id FROM catalogue WHERE %s="%s"',$fieldName,$fieldValue),__METHOD__);
    }
    public static function getProductPropBy($field,$value,$outfield){
        $sql = sprintf('SELECT %s FROM catalogue WHERE %s=%s',quote($outfield),$field,$value);
        return fetchVal($sql,__METHOD__);
    }
    public static function getBy($fieldName,$fieldValue,$incDeleted=true){
        $extraWhere = '';

        if(!strpos($fieldName,'.')){
            $fieldName = 'c.'.$fieldName;
        }
		if(!$incDeleted){
			$extraWhere = 'AND c.deleted IS NULL ';
		}

        $btw    = Cfg::getPref('btw');
        $sql    = sprintf('SELECT 
                                c.*,
                                pt.type product_type,
                                c.sale_price * 1.'.$btw.' sale_price_vat,
                                c.purchase_price * 1.'.$btw.' purchase_price_vat,
                                c.advice_price * 1.'.$btw.' advice_price_vat,
                                dt.id  delivery_time_id,
                                dt.label delivery_time_vis,
                                dt2.id delivery_time_nostock_id,
                                dt2.label delivery_time_notinstock_vis,
                                ps.`type` product_size_tag,
                                (SELECT type FROM product_sport ps WHERE ps.id=c.fk_sport) sport_label,
                                (SELECT l.`value` FROM lookups l WHERE l.`group`="ledger" AND l.id=c.ledger) ledger_label
                            FROM 
                                catalogue c
                                LEFT JOIN product_type pt ON pt.id = c.type
                                LEFT JOIN delivery_time dt ON dt.id=c.delivery_time,                                                                
                                catalogue c2
                                LEFT JOIN product_size ps ON ps.id=c2.fk_size,
                                catalogue c3
                                LEFT JOIN delivery_time dt2 ON dt2.id=c3.delivery_time_nostock
                            WHERE			
                            c.id=c2.id				
                            AND c.id=c3.id	
                            AND %s="%s"
							%s',                            
							$fieldName,
                            quote($fieldValue),
							$extraWhere);

        $data   =  fetchRow($sql,__METHOD__);
        if($data['id']){                        
            $data['title_encoded']                                              = stripSpecial($data['title']);
            $data['sale_price_vat_vis']                                         = number_format(round($data['sale_price_vat'],2),2,",",".");
            
            list($data['purchase_price'],$data['purchase_price_ct'])            = explode(".",$data['purchase_price']);
            list($data['sale_price'],$data['sale_price_ct'])                    = explode(".",$data['sale_price']);
            $data['has_adviceprice']                                          = ($data['advice_price']>0)?1:0;
            
                
            list($data['advice_price'],$data['advice_price_ct'])                = explode(".",$data['advice_price']);

            list($data['purchase_price_vat'],$data['purchase_price_vat_ct'])    = explode(".",$data['purchase_price_vat']);
            list($data['sale_price_vat'],$data['sale_price_vat_ct'])            = explode(",",$data['sale_price_vat_vis']);
            list($data['advice_price_vat'],$data['advice_price_vat_ct'])        = explode(".",$data['advice_price_vat']);            
            
            // "afronden"
            $data['purchase_price_vat_ct']      = substr($data['purchase_price_vat_ct'],0,2);
            $data['sale_price_vat_ct']          = substr($data['sale_price_vat_ct'],0,2);
            $data['advice_price_vat_ct']        = substr($data['advice_price_vat_ct'],0,2);
        }
        
        
        return $data;        
    }
    public static function getById($id){
        return self::getBy('c.id',$id);
    }
    private static function makeWhere($filter){
        
        if($filter!='' && !is_array($filter)){
            $query = sprintf('AND (     c.article_number LIKE "%%%1$s%%" OR
                                        c.article_name LIKE "%%%1$s%%" OR
                                        c.description LIKE "%%%1$s%%" OR
                                        c.title LIKE "%%%1$s%%" OR
                                        c.ean LIKE "%%%1$s%%" OR
                                        c.id = "%1$s"
                                )
                            ',$filter);
        }else if(is_array($filter)){            
            foreach($filter as $key=>$val){
                if($val!=''){
                    if(preg_match('#^billing#',$key)){
                        $query[]    = sprintf('(c.%1$s LIKE "%%%2$s%%" OR %3$s LIKE "%%%2$s%%")',
                                                $key,$val,preg_replace('#^billing#','shipping',$key));
                    }else{
                        
                        
                        $key = preg_replace('/^np_/','np.',$key);
                        $key = str_replace('c_article_number','c.article_number',$key);
                        $key = str_replace('c_ledger','c.ledger',$key);
                        $key = str_replace('c_id','c.id',$key);
                        $key = str_replace('c_article_name','c.article_name',$key);
                        $key = str_replace('c_type','c.type',$key);
                        $key = str_replace('c_in_webshop','c.in_webshop',$key);
                        
                        
                        if($key == 'in_webshop'){
                            $query[]    = 'c.in_webshop=1';
                        }else if($key == 'has_exact_stock'){
                            $query[]    = '(c.exact_stock > 1 OR c.delivery_time_nostock!=22 OR c.delivery_time_nostock IS NULL)';
                        }else if($key=='sale_price_min'){
                            $query[]    = sprintf('(c.sale_price * 1.'.Cfg::getPref('btw').') > %d',quote($val));
                        }else if($key=='np.screendiameter'){
                            foreach($val as $x => $diamrange){
                                list($tmpMin,$tmpMax) =  explode('-',$diamrange);
                                if($tmpMin < $min || !isset($min))
                                    $min = $tmpMin;
                                if($tmpMax > $max || !isset($max))
                                    $max = $tmpMax;
                            }
                            $query[] = sprintf('np.screendiameter BETWEEN %s AND %s ',$min,$max);
                        }else if($key=='sale_price_max'){
                            $query[]    = sprintf('(c.sale_price * 1.'.Cfg::getPref('btw').') < %d',quote($val));
                        }else if($key=='wm.menu_item'){                            
                            $query[]    = sprintf('LOWER(wm.menu_item)="%s"',quote(strtolower($val)));
                        }else if($key=='pt.type'){     
                            if(is_array($val)){                                                            
                                foreach($val as $id=>$item)
                                    $val[$id] = quote(strtolower($item));
                                $query[]    = sprintf('LOWER(pt.type) IN("%s")',join("\",\"",$val));                                
                            }else{
                                $query[]    = sprintf('LOWER(pt.type)="%s"',quote(strtolower($val)));
                            }                            
                        }elseif(in_array($key,array('c.in_webshop','c.ledger','c.type','c.id','wm.id','c.in_spotlight','c.brand'))||preg_match('/^np\./',$key)){
                            $query[]    = sprintf('%s = "%d"',$key,$val);
                        }else if(in_array($key,array('brand','condition'))){
                            $query[]    = sprintf('`%s` IN("%s")',$key,join('","',array_keys($val)));
                        }else{
                            if(is_array($val)){
                                $query[]    = self::arrayQueryParts($key,$val);
                            }else{
                                $query[]    = sprintf('%s LIKE "%s%%"',$key,quote($val));
                            }                                                                                    
                        }
                    }                    
                }    
            }

            if(is_array($query)&& count($query))
                return ' AND '.implode(" AND ",$query);            
        }
        
        return $query;                        
    }

    private static function arrayQueryParts($fieldName,$fields,$operator = ' AND '){
        $tmpQuery = array();
        foreach($fields as $fieldVal){
            if(is_array($fieldVal)){
                $tmpQuery[] = self::arrayQueryParts($fieldName,$fieldVal,' OR ');
            }else{
                if(strpos($fieldVal,"|")){
                    $parts = explode("|",$fieldVal);
                    foreach($parts as $part){
                        $tmpQuery[] =  sprintf('%s LIKE "%%%s%%"',$fieldName,$part);
                    }
                }else{
                    $tmpQuery[] =  sprintf('%s LIKE "%%%s%%"',$fieldName,$fieldVal);
                }
            }
        }
        $query = '('.join(' '.$operator.' ',$tmpQuery).')';
        return $query;
    }
    public static function relateProduct($product_id,$related_product_id){
        $sql = sprintf('
            INSERT INTO related_products
                (product_id,related_product,related_by,related_on)
            VALUE(%d,%d,%d,now())',
            $product_id,$related_product_id,User::getId());
        query($sql,__METHOD__);
    }
    public static function getRelatedProducts($product_id){
        $btw = Cfg::getPref('btw');
        $out = fetchArray(sprintf('SELECT 
                                        rp.*,
                                        c.*,
                                        c.id product_id,
                                        REPLACE(ROUND(c.sale_price,2),".",",") sale_price_vis,
                                        c.sale_price * 1.'.$btw.' sale_price_vat, 
                                        cpg.fk_product_group pgid
                                    FROM 
                                        related_products rp,
                                        catalogue c
                                        LEFT JOIN catalogue_product_group cpg ON cpg.fk_catalogue=c.id                                         
                                    WHERE c.id=rp.related_product 
                                    AND rp.product_id=%d',$product_id),__METHOD__);
        
        if(!empty($out)){
            foreach($out as $id=>$val){
                $out[$id]['sale_price_vat_vis']     = number_format(round($val['sale_price_vat'],2),2,",",".");
                $out[$id]['title_encoded']          = stripSpecial($val['title']);
                if($val['pgid']){
                    $out[$id]['pgid_lnk']           = $val['pgid'].'-';
                }  
                      
            }
        }
                
        return $out;
    }
    public static function unRelateProduct($id,$related){
        $sql = sprintf('DELETE FROM related_products 
                        WHERE product_id=%d 
                        AND related_product=%d',$id,$related);
        query($sql,__METHOD__);
    }
	
	public static function getProductSizes($product_id, $group_id, $color){
		
		if($group_id !=0){
        	$sql = sprintf('SELECT
								c.size,
								c.id
								FROM
								catalogue c,
								catalogue_product_group pg
								WHERE
								pg.fk_product_group=%d
								AND c.color="%s"
								AND pg.fk_catalogue = c.id ORDER BY c.size',$group_id, $color);
		}else{
			$sql = sprintf('SELECT c.size, c.id from catalogue c  where c.id=%d and c.color="%s"',$product_id, $color);
		}
        echo nl2br($sql);                        
        return fetchArray($sql,__METHOD__);                        
    }
	
	public static function getProductColor($product_id, $group_id){
		
		if($group_id !=0){
        	$sql = sprintf('SELECT
								cc.*,c.*
								FROM
								catalogue_color cc, 
								catalogue_product_group pg, 
								colors c
								WHERE
								pg.fk_product_group=%d 
								AND pg.fk_catalogue = cc.fk_catalogue and cc.fk_color=c.id  GROUP BY cc.fk_color',$group_id);
		}else{
			#$sql = sprintf('SELECT *,c.id as fk_catalogue,0 as fk_product_group from catalogue c  where c.id=%d GROUP BY c.color',$product_id);
			$sql = sprintf('SELECT * FROM catalogue_color cc, colors c  where cc.fk_color=c.id and cc.fk_catalogue=%d GROUP BY cc.fk_color',$product_id);
		}
       #echo nl2br($sql);                        
        return fetchArray($sql,__METHOD__);                        
    }
	 public static function storeProductPdf($productId){        
        $file = './img/product-pdf/'.$productId.'.pdf';
		query($sql = sprintf("UPDATE catalogue SET product_pdf=1 WHERE id=%s",$productId),__METHOD__);        
        move_uploaded_file($_FILES['pdf']['tmp_name'], './img/product-pdf/'.$productId.'.pdf');
        return;        
    }
}    
