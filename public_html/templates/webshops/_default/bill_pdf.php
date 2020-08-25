<?php
/**
 * Bill_pdf
 * 
 * @package bleuturban
 * @author Oriana Martinelli
 * @copyright 2010
 * @version $Id$
 * @access public
 */
class Bill_pdf {
    function run($params){

        if(!$_SESSION['relation']['id'])
            redirect($params['root'].'/login.html');

        if(isset($params['type'])&& $params['type']=='old'){
            header("Content-type: application/pdf");
            $bill = fetchVal($sql = sprintf('SELECT CONCAT(prefix,nummer) FROM
                                    bills_old WHERE factuur_id=%d AND klant_id=%d',$params['factuur_id'],$_SESSION['relation']['old_id']),__METHOD__);
            if($bill){
                $dir = ($_SERVER['IS_DEVEL'])?'./old_bills':'../old_bills';
                print file_get_contents($dir.'/'.$bill.'.pdf');
            }
            exit();
        }else{
            $order = OrderDao::getOrder($params['orderid']);
            if($order['relation_id']!=$_SESSION['relation']['id']){
                header('HTTP/1.1 401 Unauthorized');
                exit('You are not authorized to view this bill');
            }else{
                Billgen::run($params);
                exit();
            }
        }
        
    }  
}