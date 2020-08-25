<?php
class Mail_OutOfOrder{
   public static function  run($params){
        if($params['fk_locale']=='40'){
            $params['page']  = WebshopCms::getPageByUrl(1,'standaard+mail+niet+voorradig');
        }else{
            $params['page']  = WebshopCms::getPageByUrl(1,'default+mail+outofstock');
        }
        $params['order']    =  OrderDao::getOrder($params['orderid']);
        $array = array('cp_firstname','cp_lastname');
        foreach($array as $item){
            $params['page']['content'] = str_replace('['.$item.']',$params['order'][$item],$params['page']['content']);            
        }

        $params['page']['content'] = '<tr><td>'.$params['page']['content'].'</td></tr>';
        if($params['_do']=='sendmail'){
            $params['mail']['content'] =  '<tr><td>'.$params['mail']['content'].'</td></tr>';
            $params['sendmail'] = true;
            #_d($params['order']);
            Mailer::sendPlainHtml($params['mail']['content'],$params['order']['email'],'info@allamericansports.nl',$params['order']);                
        
            exit('<script type="text/javascript">parent.$.fancybox.close();</script>');
        }
       return $params;
    }
}

?>