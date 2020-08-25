<?php
class Image3d{        
    private static function getDir($productId){
        return sprintf('./img/product3d/%d/',$productId);
    }
    public static function delete($productId){        
        rrmdir(self::getDir($productId));
    }
    public static function store($productId){                
        if($_FILES['3dimage']['tmp_name']){
            $zip = new ZipArchive() ;
            $dir = self::getDir($productId);
            
            // open archive
            if ($zip->open($_FILES['3dimage']['tmp_name']) !== true) {
                return 'Could not open archive';
            }
            if(!is_dir($dir)){
                mkdir($dir);
            }else{
                $rm = glob($dir.'/*');
                foreach($rm as $r){
                    unlink($r);
                }
            }            
            $zip->extractTo($dir);
            $zip->close();
            
            $files = glob($dir.'*');
            if(count($files)==1){
                echo "files0 ".basename($files[0])."<br>";
                $moveFiles = glob($dir.basename($files[0]).'/*');               
                foreach($moveFiles as $file){
                    copy($dir.basename($files[0]).'/'.basename($file),$dir.basename($file));
                    unlink($dir.basename($files[0]).'/'.basename($file));                    
                }    
                rmdir($dir.basename($files[0]));
            }                                      
            return;

        }    
    }
    public static function has3d($productId){
        $dir = self::getDir($productId);        
        if(is_dir($dir))
           $swf = glob($dir.'*.swf');        
        if(is_array($swf))
            foreach($swf as $file)
                if(!preg_match('/ZOOM/',$file))
                    return preg_replace('/^\./','',$file);        
    }

}