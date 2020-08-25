<?php
class Export_adwords_export {
    public static function removeDuplicateWords($string){        
        $string = preg_replace("/([,.?!])/"," \\1",$string);
        $parts = explode(" ",$string);
        $unique = array_unique($parts);
        $unique = implode(" ",$unique);
        $unique = preg_replace("/\s([,.?!])/","\\1",$unique);
        return $unique;
    }
    public static function run($params){        
        if($params['_do']=='clear'){
            query('truncate table adwords_addgoup_add',__METHOD__);
            query('truncate table adwords_addgoup_keyword',__METHOD__);
            query('truncate table adwords_addgroup',__METHOD__);
            query('truncate table adwords_campain',__METHOD__); 
            query('UPDATE webshop_menu SET addwords_revision=0',__METHOD__);
            exit('Data cleared!');       
        }else{
            // Haal het laaste revisienummer op.
            $revision = fetchVal($sql = sprintf('SELECT MAX(addwords_revision) current_revision FROM webshop_menu'),__METHOD__);
            $tobedone = fetchVal(sprintf('SELECT COUNT(*) quantity FROM webshop_menu WHERE addwords_revision=%d',$revision),__METHOD__);
            
            if($revision==0)
                $revision = 1;
            if($tobedone==0){
                $revision = $revision + 1;
                $tobedone = 'all rows';
            }
            echo "Current revision: ".$revision." items to be done ".$tobedone;
            
            // Haal de volgende 1000 records op.
            $rows = fetchArray($sql = sprintf('SELECT * FROM webshop_menu WHERE addwords_revision <= %d LIMIT 1000',$revision),__METHOD__);
            
            // Markeer van de de huidige dataset het revisienummer zodat we weten dat deze "done" zijn.
            foreach($rows as $row)
                $ids[] = $row['id'];
            
            $sql = sprintf('UPDATE webshop_menu SET addwords_revision=%d WHERE id IN (%s)',$revision,join(",",$ids));
            query($sql,__METHOD__);
            
            // Haal productinformatie op van de huidige dataset
            $productData = self::getCatalogueItems($ids);
            
            foreach($productData as $row){
                $sql = sprintf('INSERT IGNORE INTO adwords_campain (campain_name) VALUE ("%s")',
                    quote($row['brand']));
                query($sql,__METHOD__);
                
                $sql = sprintf('SELECT id FROM adwords_campain WHERE campain_name="%s"',quote($row['brand']));
                            
                $campainId = fetchVal($sql, __METHOD__);                        
                $sql = sprintf('INSERT INTO adwords_addgroup 
                                    (fk_adwords_campain,fk_product,group_name)
                                VALUE(%d,%d,"%s")',
                                $campainId,
                                $row['product_id'],
                    quote($row['hostname'].$row['article_number'].'_'.$row['l4'].$row['l3'].$row['l2'].$row['l1']));
                $addGroupId = query($sql,__METHOD__);
                
                $keywordSets = array();
                $keywordSets[] = array('keyword'=>$row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1'].' '.$row['type'],'type'=>'Broad');        
                $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['type']).' lador','type'=>'Broad');
                $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1'].' '.$row['type']),'type'=>'Exact');
                $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l1'].' '.$row['type']),'type'=>'Exact');
                $keywordSets[] = array('keyword'=>trim($row['l2'].' '.$row['l1'].' '.$row['type']),'type'=>'Exact');                      
                if($row['alt_artnums']){
                    $altartnums = explode(",",$row['alt_artnums']);
                        foreach($altartnums as $num)
                            $keywordSets[] = array('keyword'=>$num,'type'=>'Exact');                              
                }                    
                if(in_array(strtolower($row['type']),array('originele lader','lader'))){
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1']).' lador','type'=>'Broad');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1']).' oplader','type'=>'Broad');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1']).' adapter','type'=>'Broad');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1']).' transformator','type'=>'Broad');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1']).' voeding','type'=>'Broad');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l3'].' '.$row['l2'].' '.$row['l1']).' travo','type'=>'Broad');
                    $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1']).' lador','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1']).' oplader','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1']).' adapter','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1']).' transformator','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1']).' voeding','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l3'].' '.$row['l2'].' '.$row['l1']).' travo','type'=>'Exact'); 
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l1']).' oplader','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l1']).' lador','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l1']).' adapter','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l1']).' voeding','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l2']).' oplader','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l2']).' lador','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l2']).' adapter','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l4'].' '.$row['l2'].' '.$row['l2']).' voeding','type'=>'Exact');                    
                    $keywordSets[] = array('keyword'=>trim($row['l2'].' '.$row['l1']).' oplader','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l2'].' '.$row['l1']).' lador','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l2'].' '.$row['l1']).' adapter','type'=>'Exact');
                    $keywordSets[] = array('keyword'=>trim($row['l2'].' '.$row['l1']).' voeding','type'=>'Exact');      
                    $keywordSets[] = array('keyword'=>$row['article_number'],'type'=>'Exact');                                                                                          
                }
                $keywordSets[] = array('keyword'=>$row['l2'].' '.$row['l2'].' '.$row['l1'].' '.$row['type'],'type'=>'Exact');
                $keywordSets[] = array('keyword'=>$row['article_name'],'type'=>'Broad');
                foreach($keywordSets as $set){
                    $sql = sprintf('INSERT INTO adwords_addgoup_keyword 
                                        (fk_adwords_addgroup,keyword,matchtype)
                                    VALUE(%d,"%s","%s")',
                                    $addGroupId,
                        quote(trim(self::removeDuplicateWords($set['keyword']))),
                                    $set['type']);                       
                    query($sql,__METHOD__);            
                }
                
                $adds[1]['headline']        = trim($row['l4'].' '.$row['l3']).' Lader nodig?';
                $adds[1]['description1']    = $row['type'].' voor uw '.trim($row['l2'].' '.$row['l1']);
                $adds[1]['description2']    = 'Bij ons voor maar 29,95!';
                $adds[1]['displayurl']      = $row['url_vis'];
                $adds[1]['url']             = $row['url'];
    
                $adds[2]['headline']        = $row['type'].' voor '.trim($row['l1'].' '.$row['l2']).'?';
                $adds[2]['description1']    = 'Wij hebben ze vanaf '.$row['sale_price'];
                $adds[2]['description2']    = 'Bestel snel online via ViaDennis.nl';
                $adds[2]['displayurl']      = $row['url_vis'];
                $adds[2]['url']             = $row['url'];
    
                $adds[3]['headline']        = trim($row['brand'].' '.$row['l2'].' '.$row['l1']).'?';
                $adds[3]['description1']    = $row['type'].' voor '.$row['l1'].'';
                $adds[3]['description2']    = 'Vandaag besteld, morgen al in huis!';
                $adds[3]['displayurl']      = $row['url_vis'];
                $adds[3]['url']             = $row['url'];
                
                $adds[4]['headline']        = $row['brand'].' '.$row['l1'].' adapter defect?';
                $adds[4]['description1']    = 'Bestel nu vanaf '.$row['sale_price'].' euro';
                $adds[4]['description2']    = 'En ontvang deze morgen al in huis!';
                $adds[4]['displayurl']      = $row['url_vis'];
                $adds[4]['url']             = $row['url'];            
            
                foreach($adds as $add){
                    $sql = sprintf('INSERT INTO adwords_addgoup_add (fk_adwords_addgroup,headline,description1,description2,displayurl,url)
                                    VALUE (%d,"%s","%s","%s","%s","%s")',
                                    $addGroupId,
                        quote($add['headline']),
                        quote($add['description1']),
                        quote($add['description2']),
                        quote($add['displayurl']),
                        quote($add['url']));
                    query($sql,__METHOD__);
                }
            }
        }

        exit();
    }

    public static function getCatalogueItems($menuIds){
        $sql = sprintf('SELECT 
                            c.id product_id,
                            wmggp.menu_item l4,
                            wmgp.menu_item l3,
                            wmp.menu_item l2,
                            wm.menu_item l1, 
                            c.article_name,
                            pt.`type`,
                            c.article_number,
                            c.description,
                            w.hostname,
                            GROUP_CONCAT(aa.article_number) alt_artnums,
                            REPLACE((c.sale_price*1.19),".",",") sale_price,
                            IF(wmc.id IS NULL,1,0) end_node
                        FROM                                                    
                            product_type pt,
                            stock s,
                            catalogue c,
                            alt_articlenumber aa,
                            catalogue_menu cm
                            LEFT JOIN webshop_menu wm ON wm.id=cm.fk_webshop_menu                            
                            LEFT JOIN webshop_menu wmp ON wmp.id=wm.fk_parent
                            LEFT JOIN webshop_menu wmgp ON wmgp.id=wmp.fk_parent
                            LEFT JOIN webshop_menu wmggp ON wmggp.id=wmgp.fk_parent,
                            webshops w,
                            webshop_menu wmlink
                            LEFT JOIN webshop_menu wmc ON wmlink.fk_parent=wmlink.id
                        WHERE    
                        cm.fk_catalogue=c.id
                        AND wmlink.id=wm.id                        
                        AND pt.id=c.`type`
                        AND aa.product_id = c.id
                        AND s.product_id=c.id
                        AND c.deleted IS NULL
                        AND wmp.menu_item IS NOT NULL
                        AND wmgp.menu_item IS NOT NULL 
                        AND wm.fk_webshop=w.id
                        AND cm.fk_webshop_menu IN(%s)
                        GROUP BY cm.id
                        HAVING SUM(s.quantity)>1',join(",",$menuIds));
                      
        $data = fetchArray($sql,__METHOD__);
        foreach($data as $key=>$row){
            $base = '';
            if($row['l4']){
                $data[$key]['brand'] = $row['l4'];
                $base = strtolower(urlencode($row['l4']).'/'.urlencode($row['l3']).'/'.urlencode($row['l2']).'/'.urlencode($row['l1']));
            }else if($row['l3']){
                $data[$key]['brand'] = $row['l3'];
                $base = strtolower(urlencode($row['l3']).'/'.urlencode($row['l2']).'/'.urlencode($row['l1']));
            }else if($row['l2']){
                $data[$key]['brand'] = $row['l2'];
                $base = strtolower(urlencode($row['l2']).'/'.urlencode($row['l1']));            
            }else{
                $data[$key]['brand'] = $row['l1'];
            }    
            $base = urlencode(strtolower($row['type'])).'/'.$base;            
            if($row['end_node']==1)
                $base = $base.'/'.$row['article_number'].'.html';            
            $data[$key]['url'] = 'http://www.'.$row['hostname'].'/'.preg_replace("#//#",'/',$base);
            $data[$key]['url_vis'] = $row['hostname'].'/'.$row['l2'];
        }        
        return $data;
    }
}

