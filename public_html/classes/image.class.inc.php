<?php
// classes/thumb_plugins/
require_once('ThumbBase.inc.php');        
require_once('GdThumb.inc.php');
require_once('PhpThumb.inc.php');
require_once('ThumbLib.inc.php');  
class Image{    
    public static function updateFromUrl($productId,$url){
		$image = file_get_contents($url);
		file_put_contents('./img/upload/'.$productId.'.jpg',$image);
	}
    public static function store($productId){        
        $file = './img/upload/'.$productId.'.jpg';        
        query($sql = sprintf("UPDATE catalogue SET photo=1 WHERE id=%s",$productId),__METHOD__);
		move_uploaded_file($_FILES['image']['tmp_name'], './img/upload/'.$productId.'.jpg');
        return;        
    }
    public static function copyUploaded($srcId,$dstId){
        $src = './img/upload/'.$srcId.'.jpg';     
        $dst = './img/upload/'.$dstId.'.jpg'; 
        if(file_exists($src))
            copy($src,$dst);
    }
    public static function copyProduct($srcId,$dstId){
        $photos = fetchArray(sprintf('SELECT * FROM product_photos WHERE product_id = %d ORDER BY `order`',$srcId),__METHOD__);
        $order = 0;
        foreach($photos as $photo){                        
            $id = query($sql = sprintf('INSERT INTO 
                                            product_photos (product_id,`order`,name)
                                        VALUE 
                                            (%1$d,%2$s,"%3$s")',
                                            $dstId,$order,$photo['name']),__METHOD__);                
            $order++;            
            #echo "<br><br>".nl2br($sql)."<br><br>";                                    
            $srcFile = './img/product/'.$photo['id'].'_'.$photo['name'];                
            $dstFile = './img/product/'.$id.'_'.$photo['name'];            
                        
            if(file_exists($srcFile)){
                copy($srcFile,$dstFile);
            }
                
        }
    }
        
    /**
     * Image::makeName()
     * Generates a file name by checking if there is already a file with the same name in $folder
     * Als make the filename lowercase and removes all spaces 
     * @param mixed $originalName
     * @param mixed $folder 
     * @return void
     */
    public static function makeName($originalName,$folder){
        // Add trailing slash is not available
        $folder         = addTrailingSlashIfNotThere($folder);
        $lowername      = strtolower($originalName);
        $strippedName   = preg_replace('/[^a-z0-9.]+/','-',$lowername);        
        $withoutExt     = preg_replace('/.[a-z0-9]{2,4}$/','',$strippedName);
        $ext            = end((explode('.',$strippedName)));
        if(strlen($ext)<2)
            trigger_error('makeName in utils, no extention detected',E_USER_ERROR);
        
        $fullfile       = $folder.$strippedName;
        /*
        echo 'WithoutExt:'.$withoutExt."\n";
        echo 'Ext:'.$ext."\n";        
        echo 'Folder:|'.$folder."|\n";
        echo 'Strippedname:|'.$strippedName."|\n";
        echo 'Fullname:|'.$fullfile."|\n";
        */
        $name       = preg_replace('/[^a-z0-9.]+/','-',strtolower($originalName));
        $fullfile   = $folder.$strippedName;
        #echo $fullfile.PHP_EOL;
        preg_match('/[0-9]+$/',$withoutExt,$matches);
        if($matches[0]){
            $withoutExt = preg_replace('/'.$matches[0].'$/','',$withoutExt);
            $i = $matches[0];
        }else{
            $i = 1;    
        }                        
        while(file_exists($fullfile)){
            $i++;            
            $fullfile = $folder.$withoutExt.$i.'.'.$ext;    
        }
        return basename($fullfile);
    }
    /**
     * Image::mimeIsImage($_FILES['mage']['mime'])
     * Checks if the mime type of an uploaded file is actully an image
     * @param mixed $mimeType
     * @return
     */
    public static function mimeIsImage($mimeType){
        if(substr($mimeType, 0, 5) == 'image')
            return true; //array('image/jpeg','image/pjpeg','image/png','image/gif');
            
        #$imageMimeTypes = array('image/jpeg','image/pjpeg','image/png','image/gif');
        #return in_array($mimeType,$imageMimeTypes);
    }
    public static function storeExtra($productId){

        $photos = self::getExtraImages($productId);
        if(count($photos)>=10){
            $product = fetchRow(sprintf('SELECT * FROM product_photos WHERE product_id = %d AND `order`=1',$productId),__METHOD__);
            $oldImgName = './img/product/'.$product['id'].'_'.$product['name'].'.jpg';
            if(file_exists($oldImgName))
                unlink($oldImgName);
            query(sprintf('DELETE FROM product_photos WHERE product_id = %d AND `order`=1',$productId),__METHOD__);
            query(sprintf('UPDATE product_photos SET `order`=`order`-1 WHERE product_id = %d',$productId),__METHOD__);
        }
        $max = fetchVal(sprintf('SELECT MAX(`order`) FROM product_photos WHERE product_id = %1$d',$productId),__METHOD__);
        
        $id = query(sprintf('INSERT INTO product_photos (product_id,`order`,name)
                VALUE (%1$d,%2$d,"%3$s")',
                $productId,
                $max+1,
                $_FILES['extraimage']['name']),__METHOD__);                
        $file = './img/product/'.$id.'_'.$_FILES['extraimage']['name'];                
        move_uploaded_file($_FILES['extraimage']['tmp_name'], $file);
    }
    public static function getExtraImageById($imgId){        
        $dat = fetchRow($sql = sprintf('SELECT * FROM product_photos WHERE id = %d',$imgId),__METHOD__);
        // echo $sql."\n";
        return $dat;
    }
    public static function removeExtraImage($imgId){
            $product = self::getExtraImageById($imgId);
            $oldImgName = './img/product/'.$product['id'].'_'.$product['name'].'.jpg';
            if(file_exists($oldImgName))
                unlink($oldImgName);
            query(sprintf('DELETE FROM product_photos WHERE id = %d',$imgId),__METHOD__);
            query(sprintf('UPDATE product_photos SET `order`=`order`-1 WHERE product_id = %d AND `order` >=%d',$product['product_id'],$product['order']),__METHOD__);
    }

    public static function moveExtraImage($imgId,$direction){
        $image  = self::getExtraImageById($imgId);
        
        if($direction == 'left'){
            $dest    = ($image['order']-1);
        }if($direction=='right'){
            $dest    = ($image['order']+1);
        }
        // Ander verplaatsen
        query($sql1 = sprintf('UPDATE product_photos SET `order`=%d WHERE `order`=%d AND product_id=%d',$image['order'],$dest,$image['product_id']),__METHOD__);
        query($sql2 = sprintf('UPDATE product_photos SET `order`=%d WHERE id=%d',$dest,$image['id']),__METHOD__);
        //echo $sql1."\n";
        //echo $sql2."\n";
    }
    public static function getExtraImages($productId){
       return fetchArray(sprintf('SELECT pp.*,CONCAT(pp.id,"_",pp.name) filename FROM product_photos pp WHERE pp.product_id = %d ORDER BY pp.`order`',$productId),__METHOD__);
    }
    public static function storeUserImage($userId){
        $thumb = './img/staff/thumb/'.$userId.'.jpg';
        $large = './img/staff/large/'.$userId.'.jpg';
        if(file_exists($thumb)){
            unlink($thumb);
            unlink($large);
        }
        query(sprintf("UPDATE users SET image=1 WHERE id=%s",$userId),__METHOD__);
        self::makeThumb($_FILES['myimage']['tmp_name'], $thumb,150,200);
        self::makeThumb($_FILES['myimage']['tmp_name'], $large,600,800);
        return;
    }
    public static function makeThumb($img,$newName,$newWidht, $newHeight){          
        $thumb      = PhpThumbFactory::create($img);            
        $thumb->adaptiveResize($newWidht, $newHeight);
        $thumb->save($newName);        
    }
}