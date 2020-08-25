<?php
class Cleanup{
    public static function run($params){        
        if($params['orderid']){                        
            $trackingCode = Analytics::getTrackingCode($params['orderid']);
            $toMail = 'anton@nui-boutkam.nl';
            mail($toMail,'tracking code voor'.$params['orderid'],$trackingCode);
            exit('orderid was emailed to: '.$toMail);
        }else{
            $out                      = parse('cleanup',$params);            
        }
        exit($out);        
    }
}
