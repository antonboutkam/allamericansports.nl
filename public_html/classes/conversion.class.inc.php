<?php
class Conversion {
    public static function track(){
        $sql            = sprintf(sprintf('SELECT id FROM conversion WHERE php_session_id="%s"',session_id()));
        $id             = fetchVal($sql,__METHOD__);
        $currentpage    = sprintf('http://%s%s',$_SERVER['HTTP_HOST'],$_SERVER['REQUEST_URI']);
        if($id){
            $sql = sprintf('UPDATE conversion
                            SET exitpage="%s",
                                end=now()
                                %s                                
                            WHERE php_session_id="%s"',
                            $currentpage,
                            isset($_SESSION['basket'])?', basket="'.addslashes(serialize($_SESSION['basket'])).'"':'',
                            session_id());                                        
        }else{            
            $sql = sprintf('INSERT INTO 
                                conversion(php_session_id,referer_url,langingpage,exitpage,ip,browser,start,end)
                                VALUES
                                ("%s","%s","%s","%s","%s","%s",now(),now())',
                                session_id(),
                                $_SERVER['HTTP_REFERER'],
                                $currentpage,
                                $currentpage,
                                $_SERVER['REMOTE_ADDR'],
                                $_SERVER['HTTP_USER_AGENT']);            
        }
        
        query($sql,__METHOD__);            	
    }
    public static function registerOrder($orderId){
        query($sql = sprintf('UPDATE conversion SET order_id=%d WHERE php_session_id="%s"',$orderId,session_id()),__METHOD__);          
    }
}
