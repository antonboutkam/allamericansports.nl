<?php
class Webshop {

    public static function doFirst($params){                  
        $params['brandboxes']           = Brandboxdao::getAll($params['current_webshop_id'],$params['locale']);                
        $params['main_menu']            = self::getMainMenu($params['hostname'],$params['lang']);


        $params['is_member']            = RelationDao::isMember();
        $params['modules']              = Cfg::getModules();                                                                  
        $params['footer']['col_one']    = Webshopcms::getBy('footer_col_one',1,$params['lang'],$params['current_webshop_id']);
        $params['footer']['col_two']    = Webshopcms::getBy('footer_col_two',1,$params['lang'],$params['current_webshop_id']);
        $params['footer']['col_three']  = Webshopcms::getBy('footer_col_three',1,$params['lang'],$params['current_webshop_id']);
   
        if($params['modules']['webshop_menu_editor']){
            $sBrand = '';
            if(isset($params['page_props']) && isset($params['page_props']['brand'])){
                $sBrand = $params['page_props']['brand'];
            }

            $cacheKey = 'webshop_menu_id'.$params['hostname'].$sBrand;
            if(!$params['main_menu_id'] = WebshopCache::cached($cacheKey)){
                $params['main_menu_id'] = Webshop::getMenuIdByName($params['hostname'], $sBrand);
                WebshopCache::store($cacheKey, $params['main_menu_id']);
            }
        }
        $cacheKey = 'webshop_settings'.$params['hostname'];
        if(!$params['webshop_settings'] = WebshopCache::cached($cacheKey))
            $params['webshop_settings'] = WebshopCache::store($cacheKey, Webshop::getWebshopSettings($params['hostname']));
 
		if(!empty($params['webshop_settings'])){
			foreach($params['webshop_settings'] as $id=> $setting){
				$params['webshop_settings'][$id] = str_replace('%[lang]%',$params['lang'],$setting);
			}
		}													
        $params['webshop_settings']['contact_email'] = str_replace('@','#at#',$params['webshop_settings']['contact_email']);
        if(isset($_SESSION['basket_db']) && $_SESSION['basket_db']){
            $params['products_in_cart']     = ShoppingBasketDb::getTotalQuantity($_SESSION['basket_db']);
            $params['total_price_in_cart']  = ShoppingBasketDb::getTotal($_SESSION['basket_db']);            
        }else{
            $params['products_in_cart']     = ShoppingBasket::getTotalQuantity();
            $params['total_price_in_cart']  = ShoppingBasket::getTotal();            
        }
        #$params['products_in_cart'] =str_pad($params['products_in_cart'], 4, "0", STR_PAD_LEFT); 
                
        if(file_exists(sprintf('./js/webshops/%s.jquery.js',$params['file'])))
           $params['extra_js_file'][] = sprintf('%s.jquery.js',$params['file']);
                                
        $params['head']                     = parse('inc/head',$params);                        
        $params                             = Shoppingbasket::getCart($params);
        $params['webshop_id']               = self::getIdByWebshop($params['hostname']);

        return $params;
    }
    
    
    public static function getMenuIdByTranslationTag($menu_item_lc,$lang,$hostname){
        
        $localeId = Lang::getLocaleIdByLanguageCode($lang);
            $sql = sprintf('SELECT 
                                wm.id,
                                IF(wmt.fk_locale = %d,1,0) prio
                            FROM 
                                webshop_menu wm, 
                                webshop_menu_translations wmt,
                                webshops w                            
                            WHERE 
                                w.id = fk_webshop         
                            AND wmt.fk_webshop_menu = wm.id                        
                            AND w.hostname = "%s"
                            AND wmt.menu_item_lower = "%s"
                            ORDER BY prio',
                            $localeId,
                            $menu_item_lc,
                    $hostname);                  
        #echo nl2br($sql)."<br><Br>";
        $section = fetchRow($sql,__METHOD__); 
        return $section['id'];        
    }
    public static function getMainMenu($hostname,$lang){
        $cacheKey = 'Webshop_getMainMenu'.$hostname.','.$lang;
        $out = GlobalCache::isCached($cacheKey);
        if(empty($out)){
        $localeId = Lang::getLocaleIdByLanguageCode($lang);
        $sql = sprintf('SELECT wmt.*
                        FROM 
                            webshop_menu wm, 
                            webshop_menu_translations wmt,
                            webshops w 
                        WHERE 
                            w.id = fk_webshop
                        AND wm.in_mainnav=1
                        AND wmt.fk_webshop_menu = wm.id
                        AND wmt.fk_locale = %d
                        AND w.hostname = "%s"
                        ORDER BY sorting',
                        $localeId,
                        $hostname);        
        $out = fetchArray($sql,__METHOD__);
        if(!empty($out))
            foreach($out as $id=>$row){
                $out[$id]['menu_item_lower_enc'] = str_replace(' ','-',$row['menu_item_lower']);
            }
            GlobalCache::store($cacheKey,$out);
        }            
        return $out;
    }    
    public static function getAvailable($removeDefault=false){
        $result = fetchArray("SELECT * FROM webshops",__METHOD__);
            if($removeDefault)
                foreach($result as $key=>$row)
                    if($row['hostname']=='_default')
                        unset($result[$key]);
        return $result;
    }
    public static function duplicate($src_id,$dst_id){
        $items = fetchArray($sql = sprintf('SELECT * FROM catalogue_menu WHERE fk_catalogue=%d',$src_id),__METHOD__);
        if(!empty($items)){
            foreach($items as $item){
                $store[] = sprintf('("%d","%d")',$item['fk_webshop_menu'],$dst_id);                
            }
            $insert = sprintf('INSERT INTO catalogue_menu (fk_webshop_menu,fk_catalogue) VALUE %s',join(',',$store));
            query($insert,__METHOD__);
        }
    }
    static function getFullMenuTree($iRootParent = null, $aOut = null, $sPrefixLabel = '')
    {
        if($aOut == null)
        {
            $aOut = array();
        }
        if($iRootParent == null)
        {
            $iRootParent = 'IS NULL';
        }
        else
        {
            $iRootParent = "= $iRootParent";
        }
        $sQuery = "SELECT * FROM webshop_menu WHERE fk_parent $iRootParent";
        $aWebshopMenu = fetchArray($sQuery, __METHOD__);

        foreach($aWebshopMenu as $aItem)
        {
            $aOut[] = array(
                'label' => $sPrefixLabel.$aItem['menu_item'],
                'id' => $aItem['id']
            );
            $aOut = self::getFullMenuTree($aItem['id'], $aOut, $aItem['menu_item'].' » ');
        }
        return $aOut;
    }
    public static function getLeftMenu($fk_webshop){
        $sql = sprintf('SELECT 
                            p.menu_item brand,
                            c.menu_item model
                        FROM 
                            webshop_menu p,
                            webshop_menu c
                        WHERE
                            p.id = c.fk_parent
                            AND p.fk_parent IS NULL
                            AND p.fk_webshop=%d',$fk_webshop); 
        $rawData = fetchArray($sql,__METHOD__);
        foreach($rawData as $record){            
            $out[$record['brand']]['brand_lower_enc'] = strtolower(urlencode($record['brand']));
            $out[$record['brand']]['model_lower_enc'] = strtolower(urlencode($record['model']));
            $out[$record['brand']]['brand_enc'] = urlencode($record['brand']);
            $out[$record['brand']]['brand'] = $record['brand'];
            $out[$record['brand']]['models'][] = array( 'model'=>$record['model'],
                                                        'model_enc'=>urlencode($record['model']),
                                                        'model_lower_enc'=>strtolower(urlencode($record['model'])));
        }    
        return $out;
    }
	public static function getTopSubMenuById($iFkWebshop, $menu_id, $sLanguageCode, $iSelectedItem = null){
            
        $iFkLocale = Lang::getLocaleIdByLanguageCode($sLanguageCode);
        $sql = sprintf('SELECT 
                            cm.id,
                            wmtp.menu_item parent_item,
                            wmtp.id parent_item_id,
                            wmt.menu_item brand,
                            wmt.id brand_id,
                            cmt.menu_item model,
                            cmt.id model_id
                        FROM
                            webshop_menu_translations wmtp,
                            webshop_menu wmp,                         
                            webshop_menu_translations wmt,
                            webshop_menu wm,
                            webshop_menu_translations cmt,
                            webshop_menu cm                            
                        WHERE
                            wmp.id = wm.fk_parent
                            AND wm.id = cm.fk_parent
                            AND wm.fk_parent IS NOT NULL
                            
                            AND wmtp.fk_locale=%1$d                            
                            AND wmtp.fk_webshop_menu = wmp.id        
                            
                            AND wmt.fk_locale=%1$d                            
                            AND wmt.fk_webshop_menu = wm.id                            
                            
                            AND cmt.fk_locale=%1$d
                            AND cmt.fk_webshop_menu = cm.id                                                     
                            AND wm.fk_webshop=%2$d 
                            AND cm.fk_parent=%3$d',$iFkLocale,$iFkWebshop, $menu_id);
        
        $aRawData = fetchArray($sql,__METHOD__);
        foreach($aRawData as $key => $aRecord)
        {
            if($aRecord['id'] == $iSelectedItem)
            {
                $aOut['data']['brand_lower_enc'] = strtolower(urlencode($aRecord['brand']));
                $aOut['data']['model_lower_enc'] = strtolower(urlencode($aRecord['model']));
                $aOut['data']['parent_item_enc'] = strtolower(urlencode($aRecord['parent_item']));
            
                $aOut['data']['crum_model_id'] = $aRecord['model_id'];
                $aOut['data']['crum_brand_id'] = $aRecord['brand_id'];
                $aOut['data']['crum_parent_item_id'] = $aRecord['parent_item_id'];
                
                $aOut['data']['brand'] = $aRecord['brand'];
                $aOut['data']['model'] = $aRecord['model'];
                $aOut['data']['parent_item'] = $aRecord['parent_item'];
            }

            $sActive = '';
            if($aRecord['id'] == $iSelectedItem)
            {
                $sActive = 'active';
            }

            $aOut['data']['brand_enc'] = urlencode($aRecord['brand']);
            $aOut['data']['brand'] = $aRecord['brand'];
            $aOut['data']['models'][] = array(
                'url' => stripSpecial($aRecord['parent_item'].'-'.$aRecord['brand'].'-'.$aRecord['model']).'-'.$aRecord['id'].'.html',
                'parent' => strtolower(urlencode(str_replace(' ','-',$aRecord['brand']))),
                'model' => $aRecord['model'],
                'children' => ($sActive == 'active') ? self::getTopSubMenuById($iFkWebshop, $aRecord['id'], $sLanguageCode) : null,
                'model_enc' => urlencode($aRecord['model']),
                'model_lower_enc' => strtolower(urlencode(str_replace(' ','-',$aRecord['model']))),
                'active' => $sActive,
                'id' => $aRecord['id'],
            );
        }
        return $aOut;
    }	
	public static function getLeftMenuById($iFkWebshpo, $catalogueMenuFkParent, $sLanguageCode)
    {
        $iFkLocale = Lang::getLocaleIdByLanguageCode($sLanguageCode);

        $sql = sprintf('SELECT 
                            cm.id,
                            wmt.menu_item brand,
                            cmt.menu_item model,
                            wm.sorting
                        FROM 
                            webshop_menu_translations wmt,
                            webshop_menu wm,
                            webshop_menu_translations cmt,
                            webshop_menu cm                            
                        WHERE
                            wm.id = cm.fk_parent
                            -- AND wm.fk_parent IS NULL
                            
                            AND wmt.fk_locale=%1$d                            
                            AND wmt.fk_webshop_menu = wm.id                            
                            
                            AND cmt.fk_locale=%1$d
                            AND cmt.fk_webshop_menu = cm.id
                            
                            
                            AND wm.fk_webshop=%2$d 
                            AND cm.fk_parent=%3$d
                            ORDER by cm.sorting ASC',$iFkLocale,$iFkWebshpo, $catalogueMenuFkParent);

        $aRawData = fetchArray($sql,__METHOD__);

        $aOut = array();
        foreach($aRawData as $iKey => $aRecord)
        {
            $aOut['data']['brand_lower_enc'] = strtolower(urlencode($aRecord['brand']));
            $aOut['data']['model_lower_enc'] = strtolower(urlencode($aRecord['model']));
            $aOut['data']['brand_enc'] = urlencode($aRecord['brand']);
            $aOut['data']['brand'] = $aRecord['brand'];

            $sParent = strtolower(urlencode(str_replace(' ','-',$aRecord['brand'])));


            $aOut['data']['models'][] = array(
                'parent' => $sParent,
                'model'=> $aRecord['model'],
                'model_enc' => urlencode($aRecord['model']),
                'model_lower_enc' => stripSpecial($aRecord['model']),
                'id' => $aRecord['id']
            );
        }

        return $aOut;
    }
    public static function create($hostname){
        query(sprintf('INSERT INTO webshops (hostname) VALUE("%s")',$hostname),__METHOD__);
    }
    public static function getWebshopById($webshop_id){                
        return fetchVal(sprintf('SELECT hostname FROM `webshops` WHERE id=%d',$webshop_id),__METHOD__);
    }
    public static function getIdByWebshop($webshop_hostname){          
        $key = $webshop_hostname.'webshop_id';
        if(!$result = WebshopCache::cached($key)){
            $result =fetchVal(sprintf('SELECT id FROM `webshops` WHERE hostname="%s"',$webshop_hostname),__METHOD__);
            WebshopCache::store($key,$result);                
        }        
        return $result;                 
    }    
    public static function getWebshopSetting($webshop,$setting,$disableCache=false){
        $cacheKey = 'getWebshopSetting'.$webshop.$setting;
        
        $out = GlobalCache::isCached($cacheKey);
        if(!$out || $disableCache){        
            $webshop = str_replace(array('.nuidev','.nuicart'),'',$webshop);
            $data = fetchRow($sql = sprintf('SELECT
                                            *,
                                            IF(s.hostname="_default",1,0) prio
                                        FROM
                                            `webshop_setting` wss,
                                            `webshops` s
                                        WHERE
                                            (  s.hostname="%s"
                                        OR  s.hostname="_default")
                                        AND wss.webshop_id=s.id
                                        AND wss.setting="%s" ORDER BY prio LIMIT 1',$webshop,$setting),__METHOD__);                                    
            
            $out = $data['value'];
            GlobalCache::store($cacheKey,$out);
        }        
        return $out;        
    }
    public static function getWebshopSettings($webshop_idOrHostname){
        
        $cacheKey = 'getWebshopSettings'.$webshop_idOrHostname;
        
        $out = GlobalCache::isCached($cacheKey);
        if(!$out){
            if(is_numeric($webshop_idOrHostname))
                $where = sprintf('wss.webshop_id=%d',$webshop_idOrHostname);
            else
                $where = sprintf('s.hostname="%s"',addslashes($webshop_idOrHostname));
    
            $data = fetchArray(sprintf('SELECT
                                            *
                                        FROM
                                            `webshop_setting` wss,
                                            `webshops` s
                                        WHERE
                                            (%s
                                        OR  s.hostname="_default")
                                        AND wss.webshop_id=s.id',
                    $where),__METHOD__);
            if(is_array($data)&&count($data)>0){
                foreach($data as $key=>$val)
                    if(!isset($out[$val['setting']])||$val['hostname']!='_default')
                        $out[$val['setting']] = $val['value'];            
            }
            GlobalCache::store($cacheKey,$out);            
        }
        return $out;
    }
    public static function setWebshopSetting($webshop_id,$setting,$value){
        $keyval = array('webshop_id'=>$webshop_id,'setting'=>$setting);
        $data   = array('webshop_id'=>$webshop_id,'setting'=>$setting,'value'=>$value);

        DB::instance()->store('webshop_setting',$keyval,$data);
    }
    public static function getMenuSegment($webshop_hostname=null, $fkParent=null,$query=null){
        $where[] = '1=1';
        if($fkParent)
            $where[] = sprintf('fk_parent=%d',$fkParent);
        else{
            $where[] = 'fk_parent IS NULL';
        }
        if($query)
            $where[] = sprintf('wm.menu_item_lower LIKE "%%%s%%"',addslashes(strtolower($query)));
        if($webshop_hostname)
            $where[] = sprintf('w.hostname = "%s"',$webshop_hostname);
        
        
        $sql = sprintf('SELECT wm.*,                    
                            LOWER(wm.menu_item) lower_menu_item 
                        FROM 
                            webshop_menu wm,
                            webshops w 
                        WHERE 
                        %s                          
                        AND w.id = wm.fk_webshop                        
                        ORDER BY menu_item ASC',join(' AND ',$where));
        // echo "<br><br>".nl2br($sql)."<br><br>";
        $data =  fetchArray($sql,__METHOD__);
        $out = urlEncodeFieldsInArray($data, array('menu_item','lower_menu_item'),'encoded_','-');        
        
        return $out;
    }
    /**
     *
     * @param string $sWebshopHostname
     * @param string $sName
     * @param integer $iParentId parent id of "root_element" om te forceren dat de parent NULL is.
     * @return integer
     */
   public static function getMenuIdByTranslatedName($sWebshopHostname, $locale, $sName, $iParentId=null, $grandParent=null) {
       
       $extraFrom = $extraWhere = '';
       if(trim($iParentId)!=''){
           if($iParentId=='root_element'){
                $extraFrom   .= ' LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent'.PHP_EOL;;
                $extraWhere  .= ' AND wmp.id IS NULL'.PHP_EOL;; 
           }else{
               $iParentId      = quote(strtolower($iParentId));
               $extraFrom   .= sprintf(' LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent AND LOWER(wmp.menu_item)="%s"',$iParentId).PHP_EOL;;
               $extraWhere  .= ' AND wmp.id IS NOT NULL'.PHP_EOL;;                     
           }
       }
       if($grandParent){
           $grandParent  = quote(strtolower($grandParent));
           $extraFrom   .= sprintf(' LEFT JOIN webshop_menu wmgp ON wmgp.id = wmp.fk_parent AND LOWER(wmgp.menu_item)="%s"',$grandParent).PHP_EOL;
           $extraWhere  .= ' AND wmgp.id IS NOT NULL'.PHP_EOL;;          
       }      
       $sql = sprintf('SELECT wm.id
                        FROM                             
                            webshops w,
                            webshop_menu wm,
                            webshop_menu_translations wmt
                            %s
                        WHERE                         
                        wm.fk_webshop=w.id
                        AND w.hostname="%s"
                        AND wmt.menu_item_lower="%s"        
                        AND wmt.fk_webshop_menu=wm.id
                        AND wmt.fk_locale=%s
                        %s',
                        $extraFrom,
                        $sWebshopHostname,
                        addslashes($sName),
                        
                        $locale,
                        $extraWhere
                        );                         
       return fetchVal($sql,__METHOD__);
   }    
    /**
     *
     * @param string $sWebshopHostname
     * @param string $sName
     * @param integer $iParent parent id of "root_element" om te forceren dat de parent NULL is.
     * @return integer
     */
   public static function getMenuIdByName($sWebshopHostname, $sName, $iParent=null, $grandParent=null) {
       if(empty($sName))
       {
           return null;
       }

       $extraFrom = $extraWhere = '';
       if(trim($iParent)!=''){
           if($iParent=='root_element'){
                $extraFrom   .= ' LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent'.PHP_EOL;;
                $extraWhere  .= ' AND wmp.id IS NULL'.PHP_EOL;; 
           }else{
               $iParent      = quote(strtolower($iParent));
               $extraFrom   .= sprintf(' LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent AND LOWER(wmp.menu_item)="%s"',$iParent).PHP_EOL;;
               $extraWhere  .= ' AND wmp.id IS NOT NULL'.PHP_EOL;;                     
           }
       }
       if($grandParent){
           $grandParent  = quote(strtolower($grandParent));
           $extraFrom   .= sprintf(' LEFT JOIN webshop_menu wmgp ON wmgp.id = wmp.fk_parent AND LOWER(wmgp.menu_item)="%s"',$grandParent).PHP_EOL;
           $extraWhere  .= ' AND wmgp.id IS NOT NULL'.PHP_EOL;;          
       }      
       $sql = sprintf('SELECT wm.id
                        FROM                             
                            webshops w,
                            webshop_menu wm
                            %s
                        WHERE 
                        LOWER(wm.menu_item)="%s"                         
                        AND wm.fk_webshop=w.id
                        AND w.hostname="%s"
                        %s',
                        $extraFrom,
                        addslashes($sName),
                        $sWebshopHostname,
                        $extraWhere); #echo  $sql ;                                  
       
       
       return fetchVal($sql,__METHOD__);
   }
    public static function getTranslatedMenuItemNameById($itemId,$locale) {
       $sql = sprintf('SELECT 
                            wmt.menu_item,
                            IF(wmt.fk_locale=%d,1,0) prio
                        FROM 
                            webshop_menu wm,
                            webshop_menu_translations wmt
                        WHERE 
                             wm.id=%d
                         AND wmt.fk_webshop_menu=wm.id
                         ORDER BY prio',
                        $locale,$itemId); 
       #echo nl2br($sql);
       $result = fetchVal($sql,__METHOD__);
       if(!isset($result['menu_item']) || !$result['menu_item'])
       {
           return 'root';
       }

        return $result['menu_item'];
   }    
   public static function getMenuItemNameById($itemId) {
       $sql = sprintf('SELECT wm.menu_item
                        FROM webshop_menu wm
                        WHERE 
                         id=%d',
                        $itemId); 
       $result = fetchVal($sql,__METHOD__);
       if(!$result)
           return 'root';
        return $result;
   }  
   public static function getMenuItemById($itemId) {
       $sql = sprintf('SELECT *
                        FROM webshop_menu wm
                        WHERE 
                         id=%d',
                        $itemId);          
       $result = fetchRow($sql,__METHOD__);
       if(!$result)
           return 'root';
        return $result;
   }  
   public static function getMenu($iSegmentId = 'root'){
        $sQuery = sprintf('SELECT 
                        w.*,
                        wm.*,
                        wm.id menu_id                                                                                               
                    FROM 
                        webshops w,       
                        webshop_menu wm                                                            
                    WHERE 
                        wm.fk_parent %s        
                    AND w.id = wm.fk_webshop
                    ORDER BY 
                        w.hostname, wm.menu_item ASC',
                    ($iSegmentId  == 'root' ? 'IS NULL' : sprintf('= %d',$iSegmentId )));

        $data =  fetchArray($sQuery,__METHOD__);
        $c = 0;
        $sPrevHostname = null;

        $out = array();

        foreach($data as $key=>$val){
        if($val['hostname'] != $sPrevHostname){
            $sPrevHostname = $val['hostname'];
            $c++;
        }
        $out[$c]['hostname'] = $val['hostname'];
        $val['menu_item_lower_enc'] = urlencode(str_replace('/','-',$val['menu_item_lower']));
        $out[$c]['data'][] = $val;
        }
        return $out;
   }
   public static function getMenuStructures($iSegmentId = null, $aProductTypes = null){

        $extraWhere = '';
        if(is_array($aProductTypes) && !empty($aProductTypes))
        {
            $extraWhere = sprintf('AND pt.type IN ("%s")',join('","',$aProductTypes));
        }
        $sql = sprintf('SELECT 
                        w.*,
                        wm.*,
                        wm.id menu_id,
                        IF(wmc.id IS NULL,0,1) has_children,
                        wmc.id has_children,
                        wmp.menu_item parent_menu_item,
                        wmp.menu_item_lower parent_menu_item_lower,
                        wmgp.menu_item grand_parent_menu_item,
                        wmgp.menu_item_lower grand_parent_menu_item_lower,
                        wmggp.menu_item_lower grand_grand_parent_menu_item_lower                                                                                                   
                    FROM 
                        webshops w,    
                        catalogue_menu cm,   
                        catalogue c,
                        product_type pt, 
                        webshop_menu wm
                        LEFT JOIN webshop_menu wmc ON wm.id=wmc.fk_parent
                        LEFT JOIN webshop_menu wmp ON wmp.id=wm.fk_parent
                        LEFT JOIN webshop_menu wmgp ON wmgp.id=wmp.fk_parent
                        LEFT JOIN webshop_menu wmggp ON wmggp.id=wmgp.fk_parent                                                                    
                    WHERE 
                        wm.fk_parent %s
                    AND cm.fk_webshop_menu = wm.id
                    AND c.id=cm.fk_catalogue
                    AND c.type = pt.id
                    AND c.global_stock > 0                        
                    %s                              
                    AND w.id = wm.fk_webshop
                    GROUP BY wm.id
                    ORDER BY 
                        w.hostname, wm.menu_item ASC',
                    ($iSegmentId ==null)?'IS NULL':sprintf('= %d',$iSegmentId ),$extraWhere);

        $data =  fetchArray($sql,__METHOD__);
        $c = 0;
        $sPrevHostname = null;

        $aOut = array();

        foreach($data as $key => $val){

            if($val['hostname'] != $sPrevHostname)
            {
                $sPrevHostname = $val['hostname'];
                $c++;
            }

            $aOut[$c]['hostname'] = $val['hostname'];
            $val['menu_item_lower_enc'] = urlencode(str_replace('/','-',$val['menu_item_lower']));
            if($val['parent_menu_item_lower'])
            {
                $val['parent_menu_item_lower_enc'] = urlencode($val['parent_menu_item_lower']);
            }
            if($val['parent_menu_item_lower'])
            {
                $val['grand_parent_menu_item_lower_enc'] = urlencode($val['grand_parent_menu_item_lower']);
            }
            if($val['grand_grand_parent_menu_item_lower'])
            {
                $val['grand_grand_parent_menu_item_lower_enc'] = urlencode($val['grand_grand_parent_menu_item_lower']);
            }


            $aOut[$c]['data'][] = $val;
        }
        return $aOut;
   }

   
   public static function getProductMenuStructures($segmentId=null,$productId=null,$flatResult=false,$webshopId=null){       
       $where = array();
       if($webshopId)
           $where[] = sprintf('w.id=%d',$webshopId);
       if($segmentId)
           $where[] = sprintf('wm.fk_parent=%d',$segmentId);
       else
           $where[] = 'wm.fk_parent IS NULL';
        $sql = sprintf('SELECT 
                            wm.*,
                            w.hostname hostname,
                            wm.id menu_id,
                            wm.in_mainnav,
                            IF(wmc.id IS NULL,0,1) has_children,
                            IF(cm.id IS NULL,0,1) in_webshop,
                                (
                                    SELECT 
                                        GROUP_CONCAT(CONCAT(l.locale,": ",wmt.menu_item)) 
                                    FROM 
	                                   webshop_menu_translations wmt,
	                                   locales l
                                    WHERE 
                                        wmt.fk_webshop_menu=wm.id
                                    AND l.id=wmt.fk_locale
                                ) locales
                                                    
                        FROM 
                            webshops w,        
                            webshop_menu wm    
                            LEFT JOIN catalogue_menu cm ON cm.fk_webshop_menu = wm.id AND cm.fk_catalogue=%1$d
                            LEFT JOIN webshop_menu wmc ON wmc.fk_parent = wm.id                                                        
                        WHERE 
                            %2$s
                        AND w.id = wm.fk_webshop
                        GROUP BY wm.id
                        ORDER BY 
                             w.hostname, wm.sorting',$productId,join(' AND ',$where));
        //exit($sql);
        $data =  fetchArray($sql,__METHOD__);        
        
        $c = 0; 
        if($flatResult){
            return $data;
        }
        $prevHostname = null;

        foreach($data as $key=>$val){            
            if($val['hostname']!=$prevHostname){
                $prevHostname = $val['hostname'];
                $c++;
            }
            $out[$c]['hostname'] = $val['hostname'];
            $out[$c]['webshop_id'] = $val['fk_webshop'];
            
            $out[$c]['data'][] = $val;
        }
        if(!isset($out)){
            return null;
        }
        if($webshopId){
            return $out[$c];
        }
        return $out;
   }   
   public static function addMenuRecursiveDown($menu_item,$product_id){
       $sql = sprintf('INSERT IGNORE INTO catalogue_menu 
                    (fk_webshop_menu,fk_catalogue)
                    VALUES
                    (%d,%d)',$menu_item,$product_id);       
                           
       query($sql,__METHOD__);
       
       $data = fetchArray($sql = sprintf('SELECT id FROM webshop_menu WHERE fk_parent=%d',$menu_item),__METHOD__);              
       foreach($data as $row)
           self::addMenuRecursiveDown($row['id'], $product_id);       
   }

   public static function addRecursiveUp($iMenuItem, $iProductId){
       $parent_id = fetchVal($sQuery = sprintf('SELECT fk_parent FROM webshop_menu WHERE id=%d',$iMenuItem),__METHOD__);

       if($parent_id){
           $sQuery = sprintf('INSERT IGNORE INTO catalogue_menu 
                        (fk_webshop_menu,fk_catalogue)
                        VALUES
                        (%d,%d)',$parent_id,$iProductId);

           query($sQuery,__METHOD__);
           self::addRecursiveUp($parent_id, $iProductId);
       }
   }
   
   public static function removeMenuRecursiveDown($menu_item,$product_id){
       $sql = sprintf('DELETE FROM catalogue_menu WHERE fk_webshop_menu=%d AND fk_catalogue=%d',$menu_item,$product_id);
       query($sql,__METHOD__);
       $data = fetchArray(sprintf('SELECT id FROM webshop_menu WHERE fk_parent=%d',$menu_item),__METHOD__);
       foreach($data as $row)
           self::removeMenuRecursiveDown($row['id'], $product_id);
   }   
   public static function storeMenuItem($webshop_id,$parent,$menu_item,$menu_id,$in_mainnav){       
       if($menu_id){
            $sql = sprintf('UPDATE webshop_menu 
                            SET menu_item="%s", menu_item_lower ="%s",in_mainnav=%d
                            WHERE 
                                id=%d
                                AND fk_webshop=%d',
                                $menu_item,strtolower($menu_item),$in_mainnav,$menu_id,$webshop_id);
       }
       else
       {
            $sql = sprintf('INSERT INTO webshop_menu 
                            (fk_webshop,menu_item,menu_item_lower,fk_parent,in_mainnav) VALUE 
                            (%d,"%s","%s",%s,%d)',$webshop_id,quote($menu_item),quote(strtolower($menu_item)),($parent)?$parent:'null',$in_mainnav);
       }
       
        // mail('anton@nui-boutkam.nl','query',$sql);
        query($sql,__METHOD__);
        if(isset($menu_id)){
           return $menu_id;
        }

        Db::instance();
        return mysqli_insert_id(Db::instance()->dbh);
   }
   public static function storeMenuItemTranslations($menu_item_id,$translations,$descriptions){
       
        if(!empty($translations)){
            foreach($translations as $fk_locale=>$translation){                                
                $id = fetchVal($sql = sprintf('SELECT id FROM webshop_menu_translations WHERE fk_locale=%d AND fk_webshop_menu=%d',$fk_locale,$menu_item_id),__METHOD__);
                
                if($id){                                    
                    $sql = sprintf('UPDATE webshop_menu_translations SET
                                menu_item="%s",
                                menu_item_lower="%s",
                                description="%s"   
                            WHERE fk_locale=%d AND fk_webshop_menu=%d',
                            quote($translation),
                            quote(strtolower($translation)),
                            quote($descriptions[$fk_locale]),
                            $fk_locale,
                            $menu_item_id);
                }else{
                    $sql = sprintf('INSERT INTO webshop_menu_translations 
                                (fk_locale,fk_webshop_menu,menu_item,menu_item_lower,description)
                            VALUE (%d,%d,"%s","%s","%s")',
                            $fk_locale,
                            $menu_item_id,
                            quote($translation),
                            quote(strtolower($translation)),
                            quote($descriptions[$fk_locale]));               
                }                                
                query($sql,__METHOD__);
            }
        }
   }
    public static function getMenuItemTranslation($fk_locale,$menu_id,$outfield='menu_item'){
        $sql = sprintf('SELECT '.$outfield.'  
                        FROM webshop_menu_translations 
                        WHERE fk_locale=%d 
                        AND fk_webshop_menu=%d',$fk_locale,$menu_id);
		#echo nl2br($sql);
        return fetchVal($sql,__METHOD__);
    }   
   /*
    * Removes an item from the webshop_menu table recursively down.
    * And cleans up the catalogue_menu table
    */
   public static function removeMenuItem($sectionId){
       $sql = sprintf('DELETE FROM catalogue_menu WHERE fk_webshop_menu=%d',$sectionId);
       query($sql,__METHOD__);
       $sql = sprintf('SELECT id FROM webshop_menu WHERE fk_parent=%d',$sectionId);
       $data  = fetchArray($sql,__METHOD__);
       foreach($data as $key=>$val){
           self::removeMenuItem($val['id']);
           $sql = sprintf('DELETE FROM webshop_menu WHERE id=%d',$val['id']);
           query($sql,__METHOD__);
       }    
       $sql = sprintf('DELETE FROM webshop_menu WHERE id=%d',$sectionId);
       query($sql,__METHOD__);      
   }
   public static function getParentId($sectionId){
       return fetchVal($sql = sprintf('SELECT fk_parent FROM webshop_menu WHERE id=%d',$sectionId),__METHOD__);
   }
   public static function getMenuItemsByArticleNumber($productNumber,$limit=null,$pageNum=1,$addPaginate=false,$query=''){
        $sWhere = '';
        if(!empty($query)){
            $sWhere = sprintf('
                AND (LOWER(wmggp.menu_item) LIKE "%1$s%%" 
                    OR LOWER(wmgp.menu_item) LIKE "%1$s%%" 
                    OR LOWER(wmp.menu_item) LIKE "%1$s%%" 
                    OR LOWER(wm.menu_item) LIKE "%1$s%%"
                    OR LOWER(c.article_number) LIKE "%1$s%%")',quote(strtolower($query)));
        }

        $sLimitClause = null;

        if($pageNum)
        {
            $sLimitClause = sprintf('LIMIT %d, %d',$pageNum*$limit-$limit,$limit);
        }
        elseif($limit)
        {
            $sLimitClause = 'LIMIT '.$limit;
        }
                      
           
       $sql = sprintf('SELECT 
                            SQL_CALC_FOUND_ROWS
                            t.type,
                            wmggp.menu_item grandgrandparent_menu_item, 
                            wmgp.menu_item grandparent_menu_item, 
                            wmp.menu_item parent_menu_item,
                            wm.menu_item,
                            c.article_number,
                            c.id m_product_id                            
                        FROM 
                            catalogue_menu cm,                        
                            catalogue c,
                            product_type t,
                            webshop_menu wm
                            LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent
                            LEFT JOIN webshop_menu wmgp ON wmgp.id = wmp.fk_parent
                            LEFT JOIN webshop_menu wmggp ON wmggp.id = wmgp.fk_parent
                        WHERE 
                        fk_catalogue=%d
                        AND t.id=c.type
                        AND wm.id=cm.fk_webshop_menu
                        AND c.id=cm.fk_catalogue
                        AND wmgp.menu_item IS NOT NULL
                        %s
                        %s',
                    $productNumber,
                        $sWhere,
                        $sLimitClause);
       
       $products = fetchArray($sql,__METHOD__);               
       foreach($products as $id=>$product){
           foreach($product as $key=>$val){
               if(trim($val)=='')
                   unset($products[$id][$key]);        
               $product[$key] = str_replace('/','--',urldecode($val));
               $product[$key] = str_replace('%2f','--',urldecode($product[$key]));               
               $product[$key] = urlencode($product[$key]);
           }    
           $products[$id]['url']    = str_replace('//','/',strtolower(join("/",$product)).'.html');
           $alttype                 = (in_array($product['type'],array('Lader','Originele lader'))?'adapter':'accu');
           $products[$id]['url123'] = sprintf('%s-voor',$alttype);
           foreach(array('grandgrandparent_menu_item','grandparent_menu_item','parent_menu_item','menu_item') as $prop)
                if(!empty($product[$prop])){
                    $products[$id]['url123']        .= sprintf('-%s',urlencode(strtolower($product[$prop])));
                    $products[$id]['product_title'] .= sprintf(' %s',ucfirst(urldecode($product[$prop])));                    
                }     
           $products[$id]['url123'] .= '-'.$product['m_product_id'];
       }
       $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
       if($addPaginate){
            $result['pages'] = paginate($pageNum,$result['rowcount'],$limit,'gotopage-menu');
       }
              
       $result['data']  = $products;
       
       return $result;       
    }
    public static function getProductsNotInWebshop(){        
        $sql = sprintf('SELECT c.id,c.article_number,c.brand, c.article_name
                        FROM catalogue c           
                        LEFT JOIN catalogue_menu cm ON cm.fk_catalogue=c.id
                        WHERE cm.fk_catalogue IS NULL');
        return fetchArray($sql,__METHOD__);         
    }

    public static function getEmptyMenuItems(){
        query('CREATE TEMPORARY TABLE catalogue_menu_ex  LIKE catalogue_menu',__METHOD__);
        query(' INSERT INTO catalogue_menu_ex 
                        SELECT 
                        cm.* 
                        FROM 
                        catalogue_menu cm,
                        catalogue c,
                        product_type pt
                        WHERE
                        c.id=cm.fk_catalogue
                        AND pt.id = c.`type`
                        AND pt.`type` NOT IN("Toetsenbord","Netsnoer")',__METHOD__);
        $sql = sprintf('SELECT 
                            wmggp.menu_item grand_grand_parent,
                            wmgp.menu_item grand_parent,
                            wmp.menu_item parent,
                            wm.menu_item item
                        FROM 
                            webshop_menu wm
                            LEFT JOIN webshop_menu wmc ON wmc.fk_parent=wm.id
                            LEFT JOIN catalogue_menu_ex cm ON cm.fk_webshop_menu=wm.id
                            LEFT JOIN webshop_menu wmp ON wmp.id=wm.fk_parent
                            LEFT JOIN webshop_menu wmgp ON wmgp.id=wmp.fk_parent
                            LEFT JOIN webshop_menu wmggp ON wmggp.id=wmgp.fk_parent
                        WHERE 
                            wmc.id IS NULL
                            AND cm.id IS NULL');
        return fetchArray($sql,__METHOD__);           
    }
    public static function getMostPopularByBrandId($brandId,$webshop_id=5,$limit=10){
        $currMonth = date('Ym',mktime(0,0,0,date('n'),1,date('Y')));
        $prevMonth = date('Ym',mktime(0,0,0,date('n')-1,1,date('Y')));        
        query('CREATE TEMPORARY TABLE ids(id INT(11) NOT NULL);',__METHOD__);
        $sql = sprintf('INSERT INTO ids
                        SELECT 
                            wmgc.id                         
                        FROM
                            stats_webshop_menu swm,
                            webshop_menu wm,
                            webshop_menu wmc,
                            webshop_menu wmgc                            
                        WHERE
                        wmc.fk_parent = wm.id
                        AND wmgc.fk_parent = wmc.id
                        AND wm.fk_webshop=%d
                        AND swm.fk_menu_item = wmgc.id
                        AND wm.id=%d
                        AND swm.track_month IN("%d","%d")',
                        $webshop_id,
                        $brandId,
                        $currMonth,
                        $prevMonth);
        query($sql,__METHOD__);
        $sql = sprintf('INSERT INTO ids
                        SELECT 
                            wmggc.id                         
                        FROM
                            stats_webshop_menu swm,
                            webshop_menu wm,
                            webshop_menu wmc,
                            webshop_menu wmgc,
                            webshop_menu wmggc                            
                        WHERE
                        wmc.fk_parent = wm.id
                        AND wmgc.fk_parent = wmc.id
                        AND wmggc.fk_parent = wmgc.id
                        AND wm.fk_webshop=%d
                        AND swm.fk_menu_item = wmgc.id
                        AND wm.id=%d
                        AND swm.track_month IN("%d","%d")',
                        $webshop_id,
                        $brandId,
                        $currMonth,
                        $prevMonth);
        query($sql,__METHOD__);        
        $sql = sprintf('SELECT 
                            wm.*,
                            swm.clicks,
                            wmp.menu_item_lower series_menu_item_lower,
                            wmp.menu_item series_menu_item 
                           FROM 
                            ids,
                            webshop_menu wm,
                            webshop_menu wmp,
                            stats_webshop_menu swm
                           WHERE 
                            ids.id = wm.id
                            AND wmp.id = wm.fk_parent
                            AND swm.fk_menu_item=wm.id
                            AND swm.track_month IN("%d","%d")
                            GROUP BY wm.id
                            ORDER BY swm.clicks DESC                            
                            LIMIT %d',                            
                            $currMonth,
                            $prevMonth,$limit);
        
        $aOut   = fetchArray($sql,__METHOD__);
        if(empty($aOut))
        {
            return null;
        }

        $aOut = array();
        foreach($aOut as $id=> $row)
        {
            $aOut[$id]['menu_item_lower_enc'] = urlencode($row['menu_item_lower']);
            $aOut[$id]['series_menu_item_lower_enc'] = urlencode($row['series_menu_item_lower']);
            
        }
        return $aOut;
    }        
    public static function getMenuUp($menuId){
        $fk_parent = true;
        while($fk_parent != false){
            $sql            = sprintf('SELECT   wm.id item_id,
                                                wm.menu_item,
                                                wm.menu_item_lower,
                                                wm.fk_parent 
                                        FROM 
                                                webshop_menu wm 
                                        WHERE   wm.id=%d',$menuId);
            $row            = fetchRow($sql,__METHOD__);
            $fk_parent      = $row['fk_parent'];
            $menuId         = $row['fk_parent'];            
            $data[]         = $row; 
        }
        $data = array_reverse($data);
        return $data;        
    }    
	
	public static function getMenuBannerImage($webshopId, $menuItemId){
        $sql                = sprintf('SELECT has_banner FROM webshop_menu WHERE id=%d',$menuItemId);
        
        $has_image          = fetchVal($sql,__METHOD__);
        if($has_image)
        {
            $imgFile         = sprintf('/img/navigation/%s.jpg',$menuItemId);            
            return $imgFile;  
        }
        else
        {
            return false;
        }                
    }
    
    public static function getMenuDescription($locale,$menuItemId){
        $sql                = sprintf('SELECT description 
                                        FROM webshop_menu_translations 
                                        WHERE 
                                            fk_locale=%d
                                        AND fk_webshop_menu=%d',$locale,$menuItemId);
        
        $desc = fetchVal($sql,__METHOD__);
        return $desc;
    }
    
	public static function deleteMenuBannerImage($menuItemId){
        $imgDir         = sprintf('%s/img/navigation/',$_SERVER['DOCUMENT_ROOT']);
        unlink($imgDir.'/'.$menuItemId.'.jpg');           
        $update         = sprintf('UPDATE webshop_menu SET has_banner=0 WHERE id=%d',$menuItemId);          
        query($update,__METHOD__);
    }
	public static function storeMenuBannerImage($webshopId,$menuItemId){
        $webshopName    = self::getWebshopById($webshopId); 
        $navDir         = sprintf('%s/img/navigation',$_SERVER['DOCUMENT_ROOT']);
        $imgDir         = sprintf('%s/img/navigation/',$_SERVER['DOCUMENT_ROOT']);
        if(!is_writable($navDir)){
            trigger_error(__METHOD__.' Script heeft nog geen schrijfrechten in: '.$navDir,E_USER_ERROR);
        }
        if(!is_dir($imgDir)){
            mkdir($imgDir);
            chmod($imgDir,0777);
        }                    
        move_uploaded_file($_FILES['image']['tmp_name'],$imgDir.'/'.$menuItemId.'.jpg');                
        $update = sprintf('UPDATE webshop_menu SET has_banner=1 WHERE id=%d',$menuItemId); 
        query($update,__METHOD__);
        
    }
	
	public static function getSitemapTree($fk_webshop,$locale){      
	   $cacheKey = 'Webshoop_getSitemapTree'.$fk_webshop.','.$locale;
      // $out = GlobalCache::isCached($cacheKey);
       //if(!empty($out)){
    		$sql = sprintf('SELECT                                                          
                                pwmt.menu_item parent,                                                          
                                cwmt.menu_item child,
                                c.id child_id,
                                p.id parent_id
                            FROM 
                                webshop_menu_translations pwmt,
                                webshop_menu_translations cwmt,
                                webshop_menu p,
                                webshop_menu c
                            WHERE
                                p.id = c.fk_parent
                                AND cwmt.fk_webshop_menu=c.id
                                AND cwmt.fk_locale=%1$d
                                AND pwmt.fk_webshop_menu=p.id
                                AND pwmt.fk_locale=%1$d                                                                
                                AND p.fk_parent IS NULL
                                AND p.fk_webshop=%2$d
                            GROUP BY c.id
                            ORDER BY 1,2',$locale,$fk_webshop); #echo $sql ;

                                        
            $rawData = fetchArray($sql,__METHOD__);
            $sectionId = 0;
            foreach($rawData as $key => $record){
                if(!isset($out[$record['parent_id']]['label']) || $out[$record['parent_id']]['label'] != $record['parent'])
                    $sectionId = $sectionId+1;                                    
    			$out[$record['parent_id']]['label']         = $record['parent'];
                $out[$record['parent_id']]['section_id']    = $sectionId;
                
                $purl                                       = stripSpecial($record['parent']);
                $curl                                       = stripSpecial($record['child']);
                $out[$record['parent_id']]['url']           = $purl;
                $record['url']                              = $purl.'/'.$curl.'-'.$record['child_id'];
                $out[$record['parent_id']]['data'][]        = $record;        
            }   
       //     GlobalCache::store($cacheKey,$out);            
       // }
        return $out;
		
	}
}