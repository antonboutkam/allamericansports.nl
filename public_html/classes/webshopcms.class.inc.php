<?php
class Webshopcms {
    public static function getBy($field,$val,$langCode,$webshopId){

        if($langCode=='gb'){
            $langCode = 'en';
        }
        $sql = sprintf('SELECT 
                            wcm.url,
                            wcm.tag,
                            wcm.title,
                            wcm.id id,
                            wcm.content
                        FROM 
                            webshop_cms wcm,
                            locales l
                        WHERE 
                            wcm.fk_webshop=%d 
                        AND  l.locale="%s"
                        AND wcm.fk_locale = l.id                        
                        AND %s="%s"
                        ORDER BY weight DESC',
                        $webshopId,
                        $langCode,
                        quote($field),
                        quote($val));

        $out = fetchArray($sql,__METHOD__);

        return $out;
    }
    
            
    /**
     * Webshopcms::move()
     * Pas de volgorde van een record aan.
     * 
     * @param $id het id van het record wat omhoog of omlaag moet
     * @param $direction de richting (up|down)     
     * @return void
     */            
    public static function move($id,$direction){
        $sql = sprintf('UPDATE `webshop_cms`
                        SET sorting = sorting%2$s1
                        WHERE id=%1$d',
                        $id,($direction=='up')?'-':'+');
        #echo $sql."\n";
        query($sql,__METHOD__);
        $sql = sprintf('UPDATE 
                            `webshop_cms` upd, 
                            `webshop_cms` orig
                        SET upd.sorting = upd.sorting%2$s1
                        WHERE upd.id!=%1$d
                        AND orig.id=%1$d
                        AND upd.sorting = orig.sorting',
                        $id,($direction=='up')?'+':'-');
        #echo $sql;
        query($sql,__METHOD__);        
    }    
    
    public static function fixSorting($webshop_id){
        $sql = sprintf('
                    SELECT
                    (SELECT COUNT(*) unsorted FROM webshop_cms WHERE sorting=0 AND fk_webshop=%1$d) unsorted,
                    (SELECT COUNT(*) total_rows FROM webshop_cms WHERE fk_webshop=%1$d) total_rows',$webshop_id);
        
        $sortedUns = fetchRow($sql,__METHOD__);
        
        if($sortedUns['unsorted'] > 0 && $sortedUns['total_rows'] > 0){                        
            query($sql = sprintf('UPDATE webshop_cms SET sorting=0 WHERE fk_webshop=%1$d',$webshop_id),__METHOD__);
            #echo $sql."<br>";            
            $all = fetchArray(sprintf('SELECT id FROM webshop_cms WHERE fk_webshop=%1$d ORDER BY id',$webshop_id),__METHOD__);
            foreach($all as $key=>$val)
                query($sql = sprintf('UPDATE webshop_cms SET sorting =%d WHERE id=%d',$key+1,$val['id']),__METHOD__);      
            #echo $sql."<br>";
            #trigger_error('Er waren unsorted rows in webshop_cms, dit probleem is als het goed is automatisch verholpen. Herlaad de pagina om verder te werken',E_USER_WARNING);            
        }
    }
    
    
    public static function store($id,$webshop_id,$title,$content,$in_footer_menu,$tag,$about_txt,$allow_delete=1,$footer_col_one=0,$footer_col_two=0,$footer_col_three=0,$fk_locale,$url,$layout_manager,$weight, $iFkMenuTree){
        
        $allow_url_override = self::allowUrlOverride($id);
        
        if($id=='new' || $allow_url_override){         
            $url = str_replace(' ','-',$title);
            $url = str_replace('?','',$title);
            $url = str_replace('--','-',$url);
            $url = strtolower(urlencode($url));
            $allow_url_override = 1;
        }

        $keyVal = array('id'=>$id);
        $data   = array('fk_webshop'            =>  $webshop_id,
                        'url'                   =>  $url,
                        'title'                 =>  $title,
                        'weight'                =>  $weight,
                        'content'               =>  $content,
                        'allow_url_override'    =>  $allow_url_override,
                        'in_footer_menu'        =>  $in_footer_menu,                        
                        'about_txt'             =>  $about_txt,
                        'footer_col_one'        =>  $footer_col_one,
                        'footer_col_two'        =>  $footer_col_two,
                        'footer_col_three'      =>  $footer_col_three,
                        'fk_locale'             =>  $fk_locale,
                        'layout_manager'        =>  $layout_manager,
                        'fk_menu_tree'          =>  $iFkMenuTree
            );                
        
		if(!empty($tag))	
			$data['tag'] = $tag;
			
        return Db::instance()->store('webshop_cms', $keyVal, $data);
    }
    public static function allowUrlOverride($id){
        $sql = sprintf('SELECT                             
                            allow_url_override 
                        FROM webshop_cms
                        WHERE id=%d',$id);
        return (bool)fetchVal($sql,__METHOD__);        
    }
    public static function getPages($webshop_id,$currPage=1,$itemsPP=20,$filterQuery='',$fk_locale=null){
        if($currPage)
            $limit          = sprintf('LIMIT %d, %d',$currPage*$itemsPP-$itemsPP,$itemsPP);
        $where = '';
        if(!empty($filterQuery)){            
             $where .= sprintf('AND (
                                        LOWER(url) LIKE "%%%1$s%%" OR 
                                        LOWER(tag) LIKE "%%%1$s%%"  OR 
                                        LOWER(title) LIKE "%%%1$s%%" OR 
                                        LOWER(about_txt) LIKE "%%%1$s%%" )',quote(strtolower($filterQuery)));
        }            
        if($fk_locale){
            $where .=  sprintf(' AND fk_locale=%d ',$fk_locale);
        }
        $sql = sprintf('SELECT SQL_CALC_FOUND_ROWS
                        *
                        FROM webshop_cms 
                        WHERE fk_webshop=%d
                        %s
                        ORDER BY sorting
                        %s',$webshop_id,$where,$limit);
        
        $result['data']     = fetchArray($sql,__METHOD__);        
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        $result['pages']    = paginate($currPage,$result['rowcount'],$itemsPP);
        return $result;
    }
    public static function getFooterMenu($webshop_id){
        $key = $webshop_id.'_footermenu';
        if(!$result = WebshopCache::cached($key)){
            $sql = sprintf('SELECT
                        id,url,title
                        FROM webshop_cms 
                        WHERE fk_webshop=%d
                        AND in_footer_menu=1',$webshop_id);
            $result = fetchArray($sql,__METHOD__);                   
            WebshopCache::store($key,$result);                
        }        
        return $result;                      
    }
    
    public static function getPageByTag($webshop_id,$tag,$createIfNotExist=true){
        $sql = sprintf('SELECT                             
                            * 
                        FROM webshop_cms
                        WHERE tag="%s"
                        AND fk_webshop=%d',                        
                        quote($tag),$webshop_id);                                
        $out = fetchRow($sql,__METHOD__);
        if(empty($out)){            
            if($createIfNotExist){
    #            Webshopcms::store(null,$webshop_id,$tag,'Deze tekst moet vanuit het cms worden ingevuld',0,$tag,'Automatisch aangemaakt',0);
            }                                            
        }            
                 
        return $out;
    }
    public static function getPageById($id){
        $sql = sprintf('SELECT                             
                            * 
                        FROM webshop_cms
                        WHERE id=%d',$id);
        return fetchRow($sql,__METHOD__);            
    }
    public static function delete($webshop_id,$id){
        $sql = sprintf('DELETE FROM webshop_cms WHERE id=%d AND fk_webshop=%d AND allow_delete=1',$id,$webshop_id);
        query($sql,__METHOD__);
    }
    public static function getPageByUrl($webshop_id,$tag){
        $sql = sprintf('SELECT                             
                            * 
                        FROM webshop_cms
                        WHERE fk_webshop=%d
                        AND url="%s"',$webshop_id,$tag);        
        return fetchRow($sql,__METHOD__);                
    }
	public static function uploadPicture(){
		
	}
	public static function storeCmsImage($params){        
		#pre_r($params);pre_r($_FILES);echo($_FILES['picture']['name']);die();
       # $file = './img/cms-image/'.$productId.'.pdf';
		#query($sql = sprintf("UPDATE catalogue SET product_pdf=1 WHERE id=%s",$productId),__METHOD__);        
		$keyVal = array('id'=>$id);
        $data   = array('fk_webshop_cms'            =>  $params['id'],
                        'filename'                   =>  $_FILES['picture']['name'],
                        'alt_tag'                 =>  $params['cmspicture']['alt_tag'],
                        'url'               =>  $params['cmspicture']['url'],
            );
        Db::instance()->store('webshop_cms_photos', $keyVal, $data);
        move_uploaded_file($_FILES['picture']['tmp_name'], './img/cms-image/'.$_FILES['picture']['name']);
        return;        
    }
	 public static function getCmsImages($webshopcms){
        $sql = sprintf('SELECT SQL_CALC_FOUND_ROWS
                        *
                        FROM webshop_cms_photos 
                        WHERE fk_webshop_cms=%d
                        ORDER BY id
                        ',$webshopcms);
        
        $result['data']     = fetchArray($sql,__METHOD__);        
        return $result;
    }
	 public static function deleteCmsImage($id){
		$filename= self::getById('filename',$id);
		if(file_exists('./img/cms-image/'.$filename)){unlink('./img/cms-image/'.$filename);}
        $sql = sprintf('DELETE FROM webshop_cms_photos WHERE id=%d',$id);
        query($sql,__METHOD__);
    }
	public static function getById($fieldName,$id){
        return fetchVal($sql = sprintf('SELECT %s FROM webshop_cms_photos WHERE id="%d"',$fieldName,$id),__METHOD__);
    }
	
	public static function updateCmsImageTag($id, $tag){
		$sql = sprintf('UPDATE webshop_cms_photos SET alt_tag="%s" WHERE id=%d',$tag, $id); 
        query($sql,__METHOD__);
	}
}
