<?php
class GlobalCache {
    private static function getCacheDir(){        
	$sCacheDir = '/tmp/global/';
        if(!is_dir($sCacheDir))
{
mkdir($sCacheDir, 0777, true);
}
	return '/tmp/global/';        
    }
    public static function isCached($key){
        if(basename($_SERVER['SCRIPT_FILENAME'])!='index.php')
            return false;
                              
        if(file_exists(self::getCacheDir().$key)){
            $contents = file_get_contents(self::getCacheDir().$key);
            if(!empty($contents)){
                return unserialize($contents);
            }
        }
    }
    public static function store($key,$val){
        touch(self::getCacheDir().$key);        
        if(!empty($val)){
            $data = serialize($val);
            file_put_contents(self::getCacheDir().$key,$data);
        }        
    }
    public static function clearAll(){
        $files = glob(self::getCacheDir().'*');
        if(!empty($files)){
            foreach($files as $file){
				// Sometimes more then one people are running this function at the same time. 
				// In that case it might happen that a cache file is already deleted when we get here.
				if(file_exists($file))
					@unlink($file);
            }
        }
    }
}
