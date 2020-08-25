<?php
class Settings_webshop_navigation{
    function run($params){

        if(isset($params['sectionid'])){
            self::fixSorting($params['webshop_id'], $params['sectionid']);
        }else{
            self::fixSorting($params['webshop_id']);
        }

        if(isset($params['direction']) && $params['direction']){
            self::move($params['itemId'], $params['SegementId'], $params['direction']);
        }

        if(isset($params['_do']) && $params['_do'] == 'remove'){
            $params['parent_id'] = Webshop::getParentId($params['section_id']);                
            Webshop::removeMenuItem($params['section_id']); 
            $params['removed_section'] = $params['section_id'];
            $params['sectionid'] = $params['parent_id'];
        }

        $params['shopname']     = Webshop::getWebshopById($params['webshop_id']);

        if(!isset($params['sectionid'])){
            $params['sectionid'] = null;
        }
        $params['menu']         = Webshop::getProductMenuStructures($params['sectionid'],null,false,$params['webshop_id']);
        $params['section_name'] = Webshop::getMenuItemNameById($params['sectionid']);        
        $params['treesegment']  = parse('inc/treesegment',$params);
        $params['content']      = parse('settings/settings_webshop_navigation',$params);
        
        return $params;
    }
   /**
     * Als er rechtstreeks op de database is gerommeld, of de volgorde niet meer klopt kan dat gerepareerd worden met deze routine.
     * @param $volgorde het volgorde nummer van het huidige record
     * @param $module de module / tabel waarmee gewerkt moet worden.
     */    
    public static function fixSorting($webshopId,$segmentId=false){        
        $params['menu'] = Webshop::getProductMenuStructures($segmentId,null,false,$webshopId);    
        
        if($params['menu']['data'][0]['sorting']>0)
            return;
        
        if($segmentId==false){
            $where = 'fk_parent IS NULL';
        }else{
            $where = sprintf('fk_parent=%d',$segmentId);
        }
        query($sql = sprintf('UPDATE webshop_menu SET sorting =0 WHERE %s',$where),__METHOD__);
        
        if(!empty($params['menu']['data'] ))            
            foreach($params['menu']['data'] as $key=>$val){            
                query($sql = sprintf('UPDATE webshop_menu SET sorting =%d WHERE id=%d',$key+1,$val['id']),__METHOD__);                    
            }
    }    
    /**
     * Pas de volgorde van een record aan.
     * 
     * @param $id het id van het record wat omhoog of omlaag moet
     * @param $direction de richting (up|down)
     * @param string $module de module of tabel waarmee gewerkt moet worden.     
     * @return void
     */
    public static function move($id,$segmentId,$direction){
        if($segmentId==false){
            $where = 'AND orig.fk_parent IS NULL AND upd.fk_parent IS NULL';
        }else{
            $where = sprintf('AND orig.fk_parent=%1$d AND orig.fk_parent=%1$d',$segmentId);
        }
        $sql = sprintf('UPDATE `webshop_menu`
                        SET sorting = sorting%2$s1
                        WHERE id=%1$d',
                        $id,($direction=='up')?'-':'+');
        
        query($sql,__METHOD__);
        $sql = sprintf('UPDATE `webshop_menu` upd, `webshop_menu` orig
                        SET upd.sorting = upd.sorting%2$s1
                        WHERE upd.id!=%1$d
                        AND orig.id=%1$d
                        AND upd.sorting = orig.sorting
                        %3$s',
                        $id,($direction=='up')?'+':'-',$where);        
        query($sql,__METHOD__);        
    }        
}