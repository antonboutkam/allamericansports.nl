<?php
class Orders_collect{
     function  run($params){
         $modules = array('exact_online');
         $params['modules'] = Cfg::areModulesActive($modules);

         // Altijd keyset met Exact online verversen, scheelt veel gedoe in de code.
         if($params['modules']['exact_online']==1) {
             $oExactApi = ExactHandleOath::handle($_SERVER['REQUEST_URI']);
         }


        $params['modules']['stockpile'] =  Cfg::isModuleActive('module_stockpile');

        if($params['_do']=='store_tracktrace'){
            OrderDao::storeTrackTrace($params['orderid'],$params['tt_url']);
        }
        if($params['_do']=='change_locale'){
            OrderDao::setValById('fk_locale',$params['fk_locale'],$params['id']);                       
        }                                               
        if($params['_do']=='add_gls')
            $params['gls_transaction']  = GlsTransaction::glsAdd($params['order_id']);
        
        if($params['_do']=='add_serial')
            ProductSerial::giveOut($params['orderid'], $params['serial']);

        if($params['_do']=='remove_serial')
            ProductSerial::takeIn( $params['serialId']);

        if($params['_do']=='findserial'){
            $params['serials_found'] = ProductSerial::find($params['serial'],1,20);
            $params['serial_in_db'] = ProductSerial::inDb($params['serial']);
        }

        $params['rand']                 =   rand(0,9999);
        $order                          =   OrderDao::getOrder($params['orderid']);


         if($params['modules']['exact_online']==1){
             if($params['_do']=='add_exact'){


                 $oExactRelation = new ExactRelation($oExactApi, Cfg::get('EXACT_DIVISION'));
                 $oExactRelation->upload($order['relation_id']);

                 $oExactOrder = new ExactOrder($oExactApi, Cfg::get('EXACT_DIVISION'));
                 $oExactOrder->upload($params['orderid']);
            }
             // Exact data toevoegen aan de array
            $order = OrderDao::getOrder($params['orderid']);
         }

        if($params['_do']=='complete_order'){
            parse_str($params['boxconfig'],$boxConfig);
            OrderDao::setBoxConfig($boxConfig,$params['orderid']);
            OrderDao::completePickingAndSending($params['orderid']);
        }
        $params['completed']            =   ($order['picked'])?1:0;                                     
        if(is_array($order))
            if($order['user_id'])
                $params                 =   array_merge($order,$params,User::getById($order['user_id']));
            else
                $params                 =   array_merge($order,$params);

        $order                          = OrderDao::getOrderItems($params['orderid']);   

        // Als stock_reserved 0 is, waren er ten tijde van de bestelling onvoldoende producten voorradig.
        // Als er inmiddels wel voldoende producten voorradig zijn kunnen we ze nu wel reserveren.   
                                            
        foreach($order['data'] as $id=>$row){
            if($row['stock_reserved']==0)
                $currentStock = StockDao::getCurrentTotalStock($order['data']['article_id']);                
            if($row['stock_reserved']==0 && $row['quantity']>=$currentStock){                
                // OrderDao::addProduct($row['order_id'],$row['article_id'],$row['quantity']);
                $order['data'][$id]['stock_reserved'] = 1;
            }else if($row['stock_reserved']==0 && $row['quantity']<$currentStock){
                $order['data'][$id]['insufficient_stock'] = 1;
            }
        } 
             
        $params['serials']                      =   ProductSerial::orderSerials($params['orderid']);

        $params['languages']                    =   TranslateWebshop::getAllEnabledLanguages();
        $tmp['return_totals_no_pdf_inc_vat']    =   true;
        $tmp['orderid']                         =   $params['orderid'];
        $params['totals_incvat']                =    number_format(Billgen::run($tmp),2,",",".");
        $tmp['return_totals_no_pdf']            =   true;
        $tmp['return_totals_no_pdf_inc_vat']    =   false;
        $params['totals_exvat']                 =   number_format(Billgen::run($tmp),2,",",".");
        #pre_r($params);
        Translate::init($params['lang'],'orders');

        if(trim($params['company_name'])=='')
            $params['company_name']     = $params['cp_firstname'].' '.$params['cp_lastname'];
        if(count($params['serials'])>0)
            $params['serial_numbers']   = parse('inc/serial_numbers',$params);
        
        $params['needs_backorder'] = 0;
        if(is_array($order['data'])){
            foreach($order['data'] as $row)
                if($row['stock_reserved']=='0')
                    $params['needs_backorder'] = 1;
            $params['delivery']         =  $order['data'];
        }
        $params['summationform']        =   parse('inc/summation_form',$params);
        #pre_r($params);
        if($params['ajaxresult'])
            exit(json_encode($params));               
        return $params;
    }
}