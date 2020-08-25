<?php
require_once('pdf/invoice.php');

/**
 * Warehouse_pdf
 * 
 * @package bleuturban
 * @copyright 2010
 * @version $Id$
 * @access public
 */
class Warehouse_pdf {
    function run($params){
        Translate::init($params['lang'],'bill_pdf');
        $translation                    = Translate::getTranslation();
        Invoice::$translation           = $translation;
                
        $params['delivery_detail']      = OrderDao::getOrder($params['orderid']);
        //pre_r($params['delivery_detail']);
        
        $params['deliveryId']           = DeliveryDao::getDeliveryId($params['orderid']);
        $params['delivery']             = DeliveryDao::getDelivery($params['deliveryId'],true);
        
        $params['info']                 = DeliveryDao::getDeliveryInfo($params['deliveryId']);
        
        //$params['delivery_detail']      = DeliveryDao::getDeliveryDetail($params['deliveryId']);
 
        
        //$tmp = OrderDao::getOrderItems(OrderDao::getOrderItems($params['orderid']));
        
//        pre_r($params['delivery_detail']);
//
//Array
//(
//    [type] => external
//    [print_date] => 28/12/2010 10:26
//    [user_id] => 5
//    [full_name] => Anton Boutkam
//    [completed] => no
//)
        
        $helped_by = array_shift(explode(' ',$params['delivery_detail']['picked_by']));                                                 
        $pdf = new Invoice( 'P', 'mm', 'A4' );
        $pdf->AddPage();
 
        $code =  'D'.str_pad($params['deliveryId'], 6, "0", STR_PAD_LEFT);         
                                 
                            
        $pdf->fact_dev( "MAGAZIJNPLAN ", str_pad($params['deliveryId'], 6, "0", STR_PAD_LEFT));
                        
        $pdf->addDateTime(date('Y/m/d'));
        
        $typen = array('external'=>'Levering','sale'=>'Order','internal'=>'Verplaatsing');
                        
        $pdf->addType($typen[$params['info']['type']]);
        
        //User::getFirstname()
        
        $pdf->addPageNumber("1");
                                                           
        
        $pdf->Code128(12,15,$code,40,10);
        $pdf->SetXY(11,21);
        $pdf->Write(12,$code); 
                
   //     $pdf->addEcheance($order['paydate_visible']);
        // $pdf->addNumTVA("FR888777666");
        
        
        
        $cols=array( "ARTIKELNUMMER"        => 35,        
                     "LOCATIE"              => 32,                                          
                     "PAD"                  => 26,
                     "STELLING"             => 20,
                     "PLANK"                => 20,
                     "AANTAL"               => 40);
        $pdf->addCols( $cols,50);
        $cols=array( "ARTIKELNUMMER"        => "L",
                     "LOCATIE"              => "L",        
                     "PAD"                  => "R",
                     "STELLING"             => "R",
                     "PLANK"                => "R",
                     "AANTAL"               => "R");

        
        $y    = 60;
		
		pre_r($params['delivery']);
        foreach($params['delivery'] as $product){
            $line = array( "ARTIKELNUMMER"      => $product['article_number'],
                           "OMSCHRIJVING"       => $product['article_name'],
                           "LOCATIE"            => $product['name'],
                           "PAD"                => $product['path'],
                           "STELLING"           => $product['rack'],
                           "PLANK"              => $product['shelf'],
                           "AANTAL"             => $product['quantity']);
            $totals = $totals+($order_item['sale_price']*$order_item['quantity']);                           
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }

        $pdf->Output();
        exit();

    }
  
}