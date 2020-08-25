<?php
class FixImgOrder{
    function run($params){
		query('UPDATE product_photos SET `order`=0',__METHOD__);
		
		$sql = 'SELECT * FROM product_photos';		
		$data = fetchArray($sql,__METHOD__);
		foreach($data as $row){
			$max = fetchVal(sprintf('SELECT MAX(`order`)+1 FROM product_photos WHERE product_id=%d',$row['product_id']),__METHOD__);
			
			$sql = sprintf('UPDATE product_photos 
							SET `order`=%d WHERE id=%d',$max,$row['id']);
			query($sql,__METHOD__);				
						
		}									
        exit('done');
    }
}