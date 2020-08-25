<?php
class WebshopBanner {
    public static function delete($webshop_id,$id){
        $item = self::getItemById($id);
        if($item['order']){
            query($sql = sprintf('UPDATE webshop_banner SET `order`=`order`-1 WHERE `order`>%d AND webshop_id=%d',$item['order'],$webshop_id),__METHOD__);
            query($sql = sprintf('DELETE FROM webshop_banner WHERE id=%d',$id),__METHOD__);
            $shopname = Webshop::getWebshopById($data['webshop_id']);

            $file = sprintf('./img/banner/%s/%s.jpg',$shopname,$id);
            if(file_exists($file))        
                unlink($file);
        }
    }
    public static function changeOrder($webshop_id,$new_order){
        foreach($new_order as $order=>$id){
            query($sql = sprintf('UPDATE webshop_banner SET `order`=%d WHERE webshop_id=%d AND id=%d',$order,$webshop_id,$id),__METHOD__);
        }
    }
    public static function store($data){                  
        if(!$data['order']){
            $data['order'] = self::getMaxOrder($data['webshop_id'])+1;
        }        
        $shopname = Webshop::getWebshopById($data['webshop_id']);
        
        $id = $data['id'];
        unset($data['id']);
        $id = store('webshop_banner',array('id'=>$id), $data);
        if($_FILES['file']['tmp_name']){
            if(!is_dir($dir = sprintf('./img/banner/%s',$shopname))){                                
                mkdir($dir,0777);
            }                       
            move_uploaded_file($_FILES['file']['tmp_name'], $dir.'/'.$id.'.jpg');
        }    
        return $id;                
    }
    public static function getItemById($id){
        return getById('webshop_banner',$id,__METHOD__);
    }
    public static function getMaxOrder($webshop_id){
        $sql = sprintf('SELECT MAX(`order`) maxorder FROM webshop_banner WHERE webshop_id=%d',$webshop_id);        
        return fetchVal($sql,__METHOD__);
    }
    public static function getAll($webshop_id){
        $key = 'webshopbanner_'.$webshop_id; 
        if(!$result = WebshopCache::cached($key)){
            $sql = sprintf('SELECT * FROM webshop_banner WHERE webshop_id=%d ORDER BY `order`',$webshop_id);
            $result = fetchArray($sql,__METHOD__);
            WebshopCache::store($key,$result);                
        }        
        return $result;          
    }        
}
