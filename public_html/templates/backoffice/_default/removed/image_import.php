<?php
class Image_import {
    function run($params){
        $sql = sprintf('SELECT 
                            id,
                            image
                            FROM 
                            catalogue,
                            product_import
                            WHERE
                            article_number=sku');
        $data = fetchArray($sql,__METHOD);

        foreach($data as $row){
            if(trim($row['image'])!=''){
                $image = sprintf('http://www.viadennis.nl/media/catalog/product%s',$row['image']);
                @copy($image,'./img/upload/'.$row['id'].'.jpg');
                query($sql = sprintf('UPDATE catalogue SET photo=1 WHERE id=%d',$row['id']),__METHOD__);
            }
            echo 'Copy '.$image."<br>";
        }    
        exit();
    }
}
