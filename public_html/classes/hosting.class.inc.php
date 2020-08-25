<?php
class Hosting{
        
	private static function getUploadPath(){
		return SITE_ROOT."img/upload";
	}
	private static function getProductPath(){
		return SITE_ROOT."img/product";
	}        
	public static function getDiskUsage(){	
            $productPath                        = self::getProductPath();
            $uploadPath                         = self::getUploadPath();
            $productCommand                     = "du -h --bytes ".$productPath." | grep -v cached | grep -v 3d";
            $uploadCommand                      = "du -h --bytes ".$uploadPath." | grep -v cached";
            $productData                        = shell_exec($productCommand);
            $uploadData                         = shell_exec($uploadCommand);
            $out['db_size_bytes']               = self::getDatabaseSize();            
            $out['db_size']                     = humanfilesize($out['db_size_bytes']);            
            $out['product_bytes']               = str_replace($productPath,'',$productData);
            $out['upload_bytes']                = str_replace($uploadPath,'',$uploadData);			
            $out['product_deleted_bytes'] 	= self::getDeletedPhotoSize(false);
            $out['product_deleted']		= humanfilesize($out['product_deleted_bytes']);
            $out['photo_total_bytes']		= $out['product_bytes']+$out['upload_bytes']-$out['product_deleted_bytes'];			
            $out['photo_total']			= humanfilesize($out['photo_total_bytes']);
            $out['hosting_package_bytes']	= abs(filesize2bytes(Cfg::getPref('hosting_package')));     
                
            $out['hosting_package']		= humanfilesize($out['hosting_package_bytes']);
            $out['product']         		= humanfilesize($out['product_bytes']);
            $out['upload']          		= humanfilesize($out['upload_bytes']); 
            $out['total_bytes']     		= $out['db_size_bytes']+$out['product_bytes']+$out['upload_bytes'];
            $out['total']           		= humanfilesize($out['total_bytes']);
    
            
            if($out['total_bytes']>$out['hosting_package_bytes']){
                $out['color'] = 'red';
            }else{
                $out['color'] = 'green';
            }
			
            return $out;			
	}
        public static function getDatabaseSize(){
            $row = fetchRow('SELECT 
                                table_schema, 
                                sum(data_length + index_length ) dbsize 
                            FROM 
                                information_schema.TABLES 
                            WHERE 
                                table_schema="vangoolstoffenonline" 
                            GROUP BY table_schema',__METHOD__);
            
            return $row['dbsize'];
        }
        
        public static function getDeletedPhotoSize($humanReadable=false){
            $data[1] = self::getDeletedProductPhotosAndSizes('primary_photos');
            $data[2] = self::getDeletedProductPhotosAndSizes('other_photos');
            #pre_r($data);
            $totalsize = 0;
            foreach($data as $block){
                if(!empty($block)){
                    foreach($block as $row){
                        $totalsize = $totalsize+$row['bytes'];
                    }
                }
            }

            if(empty($totalsize))
                return '0k';            
                
            if($humanReadable)
                $totalsize = humanfilesize($totalsize);                        
            
            return $totalsize;
        }        

        
        public static function getDeletedProductPhotosAndSizes($phototype='primary_photos',$currentPage=null,$itemsPP=null){
            $limit = null;
            if($currentPage)
                $limit          = sprintf('LIMIT %d, %d',$currentPage*$itemsPP-$itemsPP,$itemsPP);
			
            if($phototype=='primary_photos'){
                $sql        = sprintf('SELECT CONCAT(id,".jpg") fileid,
                                        c.id, 
                                        c.id product_id 
                                        FROM catalogue c
                                        WHERE c.deleted IS NOT NULL 
                                        AND c.photo=1
                                        '.$limit);
                
                $photos     = fetchArray($sql,__METHOD__);
                $basepath = self::getUploadPath();                            
            }else{
                $sql        = sprintf("SELECT 
                                        CONCAT(pp.id,'_',pp.name) fileid,
                                        pp.id,
                                        c.id product_id
                                        FROM
                                        catalogue c,
                                        product_photos pp 
                                        WHERE c.id=pp.product_id
                                        AND c.deleted IS NOT NULL 
                                        AND c.photo=1 
                                        $limit");                
                $photos     = fetchArray($sql,__METHOD__);                
                $basepath = self::getProductPath();            
            }
            
            if(!empty($photos)){
                foreach($photos as $id=>$photo){
                    $filename = $basepath.'/'.$photo['fileid'];
                    if(!file_exists($filename) && $phototype=='primary_photos'){                                                
                        ProductDao::setVal('photo','0',$photo['id']);
                        continue;
                    }
                    if(!file_exists($filename) && $phototype!='primary_photos'){                                                
                        Image::removeExtraImage($photo['id']);
                    }
                    $size = filesize($filename);
                    $photos[$id]['humanfilesize']   = humanfilesize($size);
                    $photos[$id]['bytes']           = $size;
                    $photos[$id]['file']            = $filename;
                }
            }
            return $photos;
        }
}