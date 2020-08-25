<?php
class Statistics{
    function run($params){        
        header("Content-type: text/javascript");
        
        if($params['_do']=='track_menu_item'){
            $sql = sprintf('INSERT INTO stats_webshop_menu (fk_menu_item,track_month,clicks)
                    VALUE(%d,%d,1) 
                    ON DUPLICATE KEY UPDATE clicks=clicks+1',
                    $params['fk_menu_item'],
                    date('Ym'));
            query($sql,__METHOD__);            
        }
                        
        echo json_encode(array(1));
        exit();        
    }
}
