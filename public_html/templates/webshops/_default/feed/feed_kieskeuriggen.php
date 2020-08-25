<?php
class Feed_kieskeuriggen{
    function run($params){
        $vatCalc =  (Cfg::getPref('btw')/100)+1;
        
        $sql = 'SELECT 
                    c.id,
                    ROUND(c.sale_price * '.$vatCalc.',2) sale_price,
                    ct.description,
                    ct.title,
                    c.ean,
					c.exact_stock,
					pt.type type_vis,
                    wmp.menu_item menu,
                    wm.menu_item menu_parent,
                    wmgp.menu_item menu_grand_parent,
                    dt_normal.label dt_normal,
                    dt_nostock.label dt_no_stock,
                    cpg.fk_product_group ,
                    lbrand.value brand_vis,
                    GROUP_CONCAT(DISTINCT co.color) colors                 
                FROM 
                    catalogue_translation ct,
                    catalogue_product_group cpg,
                    lookups lbrand,
                    catalogue_color cc,
                    colors co,
                    catalogue c
                    LEFT JOIN catalogue_menu cm ON cm.fk_catalogue = c.id
                    LEFT JOIN webshop_menu wm ON wm.id = cm.fk_webshop_menu
                    LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent
                    LEFT JOIN webshop_menu wmgp ON wmgp.id = wmp.fk_parent
                    LEFT JOIN delivery_time dt_normal ON c.delivery_time = dt_normal.id
                    LEFT JOIN delivery_time dt_nostock ON c.delivery_time_nostock = dt_nostock.id  
					LEFT JOIN product_type pt ON c.type = pt.id
                WHERE 
                 ct.fk_catalogue = c.id
                AND cpg.fk_catalogue = c.id
                AND lbrand.id = c.brand
                AND cc.fk_catalogue = c.id
                AND co.id = cc.fk_color
                AND lbrand.`group` = "brand"
                AND c.in_webshop = 1
                GROUP BY c.id';			
                   
        $q   = mysqli_fetch_assoc(Db::instance()->dbh, $sql);
        $tmpFeedLocation = './feed/kieskeurig-tmp.xml';
        $finalFeedLocation = './feed/kieskeurig.xml';
        $ob_file = fopen($tmpFeedLocation,'w');
        
        
        #header('Content-type:text/xml');
        fwrite($ob_file,'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);                 
        fwrite($ob_file,'<products>'.PHP_EOL);    
	
        while($row = mysqli_fetch_assoc(Db::instance()->dbh, $q)){
			if(empty($row['menu']) && empty($row['menu_parent']) && empty($row['menu_grand_parent'])){
			     // Extra query doen om te kijken of er andere 
                 // producten in de groep zijn die wel aan de menu tree hangen.
                 
                 // 1. Ale producten uit de groep ophalen.
                 $tpl = 'SELECT MAX(id) FROM webshop_menu WHERE id IN 
                        (select fk_webshop_menu from catalogue_menu where fk_catalogue in 
                        (select fk_catalogue from catalogue_product_group where fk_product_group IN 
                        (select fk_product_group from catalogue_product_group where fk_catalogue="%d"))) ORDER BY id';
                 $sql   = sprintf($tpl,$row['id']);
                 
                #echo nl2br($sql)."<br><br>";
                $menu_items   = array();
                $id           = fetchVal($sql,__METHOD__);
                $loop         = true;                 
                $tpl           = 'SELECT fk_parent,menu_item FROM webshop_menu WHERE id=%d';
                $sql           = sprintf($tpl,$id);
                $menu_row      = fetchRow($sql,__METHOD__);
                if(!empty($menu_row)){
                     $menu_items[]  = $menu_row['menu_item'];
                     while($menu_row['fk_parent']){
                        $sql = sprintf($tpl,$menu_row['fk_parent']);  
                        $menu_row = fetchRow($sql,__METHOD__);
                        if(!empty($menu_row)){
                            $menu_items[]  = $menu_row['menu_item'];
                        }    
                     }                               
                     $menu_items = array_reverse($menu_items);                                                      
                     if(!empty($menu_items))
                        $row['custom_menu'] = join(' > ',$menu_items);        
                }                                                                     			
            }
            
            if(!preg_match("/^[0-9]{13}$/", $row['ean'])) {
                $productIdsWithInvalidEan[] = $row['id'];                
            }
            
            
            fwrite($ob_file,'   <product>'.PHP_EOL);
            fwrite($ob_file,'       <id>'.$row['id'].'</id>'.PHP_EOL);
            if($row['custom_menu'])
                fwrite($ob_file,'       <productgroep>'.htmlentities(trim($row['custom_menu'])).'</productgroep>'.PHP_EOL); 
            else
                fwrite($ob_file,'       <productgroep>'.htmlentities(trim($row['menu'].' > '.$row['menu_parent'].' > '.$row['menu_grand_parent'],' > ')).'</productgroep>'.PHP_EOL); 
            fwrite($ob_file,'       <merk>'.$row['brand_vis'].'</merk>'.PHP_EOL); 
            fwrite($ob_file,'       <type>'.htmlentities($row['type_vis']).'</type>'.PHP_EOL); 
            fwrite($ob_file,'       <toevoeging-type>'.$row['colors'].'</toevoeging-type>'.PHP_EOL); 
            $srcEnc = mb_detect_encoding($row['description'], "UTF-8, ASCII, ISO-8859-1");
            $newStr = iconv($srcEnc,"UTF-8",$row['description']);
                                    
            fwrite($ob_file,'       <extra-productbeschrijving><![CDATA['.$newStr.']]></extra-productbeschrijving>'.PHP_EOL); 
            //echo '       <partnumber>'.$row['ean'].'</partnumber>'.PHP_EOL;
            fwrite($ob_file,'       <ean-code />'.PHP_EOL);

            fwrite($ob_file,'       <partnumber>'.$row['ean'].'</partnumber>'.PHP_EOL);
            #fwrite($ob_file,'       <ean-code>'.$row['ean'].'</ean-code>'.PHP_EOL); 
            fwrite($ob_file,'       <prijs>'.$row['sale_price'].'</prijs>'.PHP_EOL);
            if($row['sale_price']>75){
                fwrite($ob_file,'       <verzendkosten>0.00</verzendkosten>'.PHP_EOL); 
            }else{
                fwrite($ob_file,'       <verzendkosten>4.95</verzendkosten>'.PHP_EOL); 
            }             
            fwrite($ob_file,'       <afhaalkosten>0.0</afhaalkosten>'.PHP_EOL);
            if($row['exact_stock']>0){
                fwrite($ob_file,'       <levertijd>'.htmlentities($row['dt_normal']).'</levertijd>'.PHP_EOL);
            }else{
                fwrite($ob_file,'       <levertijd>'.htmlentities($row['dt_no_stock']).'</levertijd>'.PHP_EOL);
            } 
            $row['title_encoded'] = stripSpecial($row['title']);
                                                
            fwrite($ob_file,'       <deeplink>http://www.allamericansports.nl/nl/product/'.$row['title_encoded'].'-'.$row['fk_product_group'].'-'.$row['id'].'.html</deeplink>'.PHP_EOL); 
            fwrite($ob_file,'       <imagelink>http://www.allamericansports.nl/img/upload/800x600_'.$row['id'].'.jpg</imagelink>'.PHP_EOL); 
            fwrite($ob_file,'       <voorraad>'.$row['exact_stock'].'</voorraad>'.PHP_EOL);
            fwrite($ob_file,'   </product>'.PHP_EOL); 
        }
        
        fwrite($ob_file,'</products>');
        fclose($ob_file);
        if(file_exists($finalFeedLocation))
            unlink($finalFeedLocation);
        rename($tmpFeedLocation,$finalFeedLocation);
		
        
        usleep('500000');
        
        #_d($productIdsWithInvalidEan);                		
        exit('Done');                        
    }    
}
