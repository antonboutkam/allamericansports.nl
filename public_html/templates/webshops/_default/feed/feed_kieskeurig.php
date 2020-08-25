<?php
class Feed_kieskeurig{
    function run($params){	
        $vatCalc =  (Cfg::getPref('btw')/100)+1;
        
        $sql = 'SELECT 
                    c.id,
                    ROUND(c.sale_price * '.$vatCalc.',2) sale_price,
                    ct.description,
                    ct.title,
                    wmp.menu_item menu,
                    wm.menu_item menu_parent,
                    wmgp.menu_item menu_grand_parent,
                    dt_normal.label dt_normal,
                    dt_nostock.label dt_no_stock,
                    cpg.fk_product_group                  
                FROM 
                    catalogue_translation ct,
                    catalogue_product_group cpg,
                    catalogue c
                    LEFT JOIN catalogue_menu cm ON cm.fk_catalogue = c.id
                    LEFT JOIN webshop_menu wm ON wm.id = cm.fk_webshop_menu
                    LEFT JOIN webshop_menu wmp ON wmp.id = wm.fk_parent
                    LEFT JOIN webshop_menu wmgp ON wmgp.id = wmp.fk_parent
                    LEFT JOIN delivery_time dt_normal ON c.delivery_time = dt_normal.id
                    LEFT JOIN delivery_time dt_nostock ON c.delivery_time = dt_nostock.id    
                WHERE 
                 ct.fk_catalogue = c.id
                AND cpg.fk_catalogue = c.id
                GROUP BY c.id
                LIMIT 30';

                   
        $q   = mysqli_query(Db::instance()->dbh, $sql);
        
        header('Content-type:text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;                 
        echo '<products>'.PHP_EOL;          
        while($row = mysqli_fetch_assoc(Db::instance()->dbh, $q)){
            
            echo '   <product>'.PHP_EOL;
            echo '       <id>'.$row['id'].'</id>'.PHP_EOL; 
            echo '       <productgroep>'.$row['menu'].' &lt; '.$row['menu_parent'].' &lt; '.$row['menu_grand_parent'].'</productgroep>'.PHP_EOL; 
            echo '       <merk>Canon</merk>'.PHP_EOL; 
            echo '       <type>Ixus 70</type>'.PHP_EOL; 
            echo '       <toevoeging-type>roze</toevoeging-type>'.PHP_EOL; 
            echo '       <extra-productbeschrijving><![CDATA['.htmlentities($row['description']).']]></extra-productbeschrijving>'.PHP_EOL; 
            echo '       <partnumber>'.$row['ean'].'</partnumber>'.PHP_EOL;
            echo '       <ean-code>'.$row['ean'].'</ean-code>'.PHP_EOL; 
            echo '       <prijs>'.$row['sale_price'].'</prijs>'.PHP_EOL;
            if($row['sale_price']>75){
                echo '       <verzendkosten>0.00</verzendkosten>'.PHP_EOL; 
            }else{
                echo '       <verzendkosten>4.95</verzendkosten>'.PHP_EOL; 
            }             
            echo '       <afhaalkosten>0.0</afhaalkosten>'.PHP_EOL;
            if($row['exact_stock']>0){
                echo '       <levertijd>'.htmlentities($row['dt_normal']).'</levertijd>'.PHP_EOL;
            }else{
                echo '       <levertijd>'.htmlentities($row['dt_no_stock']).'</levertijd>'.PHP_EOL;
            } 
            $row['title_encoded'] = stripSpecial($row['title']);
                                                
            echo '       <deeplink>http://www.allamericansports.nl/nl/product/'.$row['title_encoded'].'-'.$row['fk_product_group'].'-'.$row['id'].'.html</deeplink>'.PHP_EOL; 
            echo '       <imagelink>http://www.allamericansports.nl/img/upload/377x308_'.$row['id'].'.jpg</imagelink>'.PHP_EOL; 
            echo '       <voorraad>'.$row['exact_stock'].'</voorraad>'.PHP_EOL;
            echo '   </product>'.PHP_EOL; 
        }
        
        echo '</products>';	  
        exit();                        
    }    
}
