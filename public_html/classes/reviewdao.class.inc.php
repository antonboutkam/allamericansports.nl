<?php
class ReviewDao{
	 public static function store($customer,$id){
        $customer['review_created'] = date("Y-m-d H:i:s");
       
        
        return Db::instance()->store('product_reviews',array('id'=>$id),$customer);   
    }
	
	public static function getDefaults(){
        return array('fk_relation' => '+31 (0)','fk_catalogue' => '+31 (0)','fk_webshop' => '+31 (0)6','review_created'=>'');
    }
	public static function getById($id){
        return self::getBy('c.id',$id);
    }
	
	public static function getBy($fieldName,$fieldValue){
		
        $btw    = Cfg::getPref('btw');
        $sql    = sprintf('SELECT 
                                c.*
                            FROM 
                                product_reviews c
                            WHERE							
                            %s="%s"
							%s',                            
							$fieldName,
                            quote($fieldValue),
							$extraWhere);
        
        $data   =  fetchRow($sql,__METHOD__);

        if($data['id']){
            $data['sale_price_vat_vis'] = number_format($data['sale_price_vat'],2,",",".");
            list($data['purchase_price'],$data['purchase_price_ct'])    = explode(".",$data['purchase_price']);
            list($data['sale_price'],$data['sale_price_ct'])            = explode(".",$data['sale_price']);
            list($data['advice_price'],$data['advice_price_ct'])        = explode(".",$data['advice_price']);

            list($data['purchase_price_vat'],$data['purchase_price_vat_ct'])    = explode(".",$data['purchase_price_vat']);
            list($data['sale_price_vat'],$data['sale_price_vat_ct'])            = explode(".",$data['sale_price_vat']);
            list($data['advice_price_vat'],$data['advice_price_vat_ct'])        = explode(".",$data['advice_price_vat']);
                        
        }
        return $data;        
    }
	
	public static function find($filter,$val,$sort,$outfields=null,$currentPage=null,$itemsPP=null,$incWoStock=true){        
		
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
            $select = "pr.* ";        
        $where              = ' and fk_catalogue ='.$val;

        $stock = '';

                                    
        //if(!$incWoStock){		
        //    $where .= "\nAND c.global_stock >= 1";
        //}
		        
		//if(strpos($where,'cm.')||strpos($where,'wm.')){
		//	$extraJoin = sprintf('		LEFT JOIN product_menu cm ON c.id=cm.fk_product
        //                                LEFT JOIN webshop_menu wm ON wm.id=cm.fk_webshop_menu');
		//}
        $sql                = sprintf('SELECT SQL_CALC_FOUND_ROWS %1$s,
                                        pr.id as rid,
                                        r.cp_firstname
                                        FROM 
                                            product_reviews pr,
                                            relations r 										
                                        WHERE
                                        review_approved IS NOT NULL
                                        AND r.id= pr.fk_relation
                                        %5$s
                                        %2$s 
                                        GROUP BY pr.id
                                        %6$s
                                        %4$s
                                        %3$s                                        
                                        ',$select,($where!='')?$where:'',$limit,$sort,$stock,$having);

        $result['data']     = fetchArray($sql,__METHOD__);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        $result['review_count'] = 0;
        if(is_array($result['data'])){            
            foreach($result['data'] as $key=>$val){
               $result['review_count']++;	   
               $totalVoted = $totalVoted + $val['rating_stars'];
			   $tmp_ts = strtotime($result['data'][$key]['review_created']);
               for($i=1;$i<5;$i++){
                    $star = ($result['data'][$key]['rating_stars']<=$i)?'empty':'filled';
                    $result['data'][$key]['rating_stars_array'][$i] = array('starval'=>$star); 
               }
               
			   $result['data'][$key]['review_created'] = date('d-M-Y', $tmp_ts);
			   
            }    
            if($result['review_count']>0){                
                $result['avg_review'] = round($totalVoted/$result['review_count'],0);
            }else{                
                $result['avg_review'] = false;;
            }
        }
        return $result;
    }

	public static function getReviewBy($value){
		$sql = sprintf('SELECT fk_relation FROM product_reviews WHERE id=%d',$value); 
        $data   = fetchVal($sql,__METHOD__);
		return $data;
    }
	
	public static function checkUser($id,$password){
        if(trim($id)==''||trim($password)=='')
            return;        
        $email = fetchVal($sql = sprintf('SELECT email FROM relations WHERE id="%s" AND (password="%s" OR password="%s")',
                                quote($id),
                                quote($password),
                            md5($password)),__METHOD__);       
        if($email){
            return true;
        }
    }
	public static function approve($review_id){
		query($sql = sprintf('UPDATE product_reviews SET review_approved = NOW(), review_approved_by=%d WHERE id=%d',User::getId(),$review_id),__METHOD__);
    }
	
}