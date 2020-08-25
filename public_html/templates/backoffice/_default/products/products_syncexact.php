<?php
class Products_syncexact{
    
    static function xmlHighlight($s){
      $s = preg_replace("|<([^/?])(.*)\s(.*)>|isU", "[1]<[2]\\1\\2[/2] [5]\\3[/5]>[/1]", $s);
      $s = preg_replace("|</(.*)>|isU", "[1]</[2]\\1[/2]>[/1]", $s);
      $s = preg_replace("|<\?(.*)\?>|isU","[3]<?\\1?>[/3]", $s);
      $s = preg_replace("|\=\"(.*)\"|isU", "[6]=[/6][4]\"\\1\"[/4]",$s);
      $s = htmlspecialchars($s);
      $s = str_replace("\t","&nbsp;&nbsp;",$s);
      $s = str_replace(" ","&nbsp;",$s);
      $replace = array(1=>'0000FF', 2=>'0000FF', 3=>'800000', 4=>'FF00FF', 5=>'FF0000', 6=>'0000FF');
      foreach($replace as $k=>$v) {
        $s = preg_replace("|\[".$k."\](.*)\[/".$k."\]|isU", "<font color=\"#".$v."\">\\1</font>", $s);
      }    
      return nl2br($s);
    }
    function  run($params){

        $sql = sprintf('
                SELECT 
                        epl.*,
                        REPLACE(epl.xml,"﻿","") xml,                        
                       u.full_name
                FROM 
                    exact_product_log epl 
                LEFT JOIN users u ON u.id = epl.fk_user
                WHERE epl.fk_catalogue=%d 
                ORDER BY epl.`date` DESC
                LIMIT 10',
                $params['id']);
        
        $params['data']     =   fetchArray($sql,__METHOD__);                
        if(!empty($params['data'])){
            foreach($params['data'] as $id => $row){
                $params['data'][$id]['xml_entities'] = htmlentities($row['xml']);
//                xmlHighlight
            }
        }
        $params['exact_synced'] = 1;
        $params['content']  =   parse('products_syncexact',$params);
        return $params;
    }
 
}