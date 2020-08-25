<?php
require_once('pdf/invoice.php');

/**
 * Bill_pdf
 * 
 * @package bleuturban
 * @author Oriana Martinelli
 * @copyright 2010
 * @version $Id$
 * @access public
 */
class Order_picker_pdf {
    function run($params){
        //$translation = Translate::getTranslation();
        //Invoice::$translation = $translation;
        
        
             
        $order                          = OrderDao::getOrder($params['orderid']);                
        $params['lang']                 = Lang::getCodeByLanguageId($order['fk_locale']);
        
        $translation = Translate::getTranslationNoInject($params['lang'],'order_picker_pdf','backoffice');
        
        Invoice::$translation = $translation;    
        
                
        
        OrderDao::setOrderUser($params['orderid'],User::getId(),'picked');
        $order                          = OrderDao::getOrder($params['orderid']);        
        $order_items                    = OrderDao::getOrderItems($params['orderid']);
        $relation                       = RelationDao::getById($order['relation_id']);
        $picked_by = array_shift(explode(' ',$order['picked_by']));                                                 
        $pdf = new Invoice( 'P', 'mm', 'A4' );
        $pdf->AddPage();


        $colors     =  ColorDao::getProductColors($order_item['p_id']);
        $joinColors = array();
        if(!empty($colors)){
            foreach($colors as $color){
                $joinColors[] = $color['color'];
            }
        }

        $webshop_settings = Webshop::getWebshopSettings($params['hostname']);
        
        if($params['lang']=='en'){
            $params['lang']='gb';            
        }
        $billingAddress = Cfg::getPref('billing_address_'.$params['lang']);
        
        if(!$billingAddress){
            $billingAddress = 'Factuuradres_niet_ingesteld';
        }
        $pdf->addSociete($webshop_settings['bill_title'],$billingAddress);

        $pdf->fact_dev($translation['lbl_loc'], str_pad($order['order_id'], 6, "0", STR_PAD_LEFT));
                
        $pdf->addDate(date('d/m/Y'));
        
        // $pdf->addClient(str_pad($order['relation_id'], 6, "0", STR_PAD_LEFT));
        $pdf->addPageNumber("1");
        
        if($order['has_delivery_address'] == 1){
            $pdf->addClientAdresse(sprintf("%s\n%s\n%s %s\n%s %s\n%s",
                                                $order['company_name'],
                                                $order['cp_firstname'].' '.$order['cp_lastname'],
                                                $order['shipping_street'],
                                                $order['shipping_number'],
                                                $order['shipping_postal'],
                                                $order['shipping_city'],
                                                $order['shipping_country']));                                                   
		}else{
            $pdf->addClientAdresse(sprintf("%s\n%s\n%s %s\n%s %s\n%s",
                                                $order['company_name'],
                                                $order['cp_firstname'].' '.$order['cp_lastname'],
                                                $order['billing_street'],
                                                $order['billing_number'],
                                                $order['billing_postal'],
                                                $order['billing_city'],
                                                $order['billing_country'])); 		  		  
		}
        // $pdf->addReglement("Pin");
        //$pdf->addEcheance($order['paydate_visible']);
        // $pdf->addNumTVA("FR888777666");
        //$pdf->addReference($helped_by);
        $cols=array( $translation['lbl_articlenumber']              => 45,
                     $translation['lbl_description']                => 58,
                     $translation['lbl_color']                      => 25,
                     $translation['lbl_size']                       => 25,
                     $translation['lbl_quantity']                   => 20,
                     $translation['lbl_box']                        => 18);
        $pdf->addCols( $cols, 75);
        $cols=array( $translation['lbl_articlenumber']              => "L",
                     $translation['lbl_description']                => "L",
                     $translation['lbl_color']                      => "L",
                     $translation['lbl_size']                       => "L",
                     $translation['lbl_quantity']                   => "C",
                     $translation['lbl_box']                        => "C");
//        $pdf->addLineFormat($cols);
//        $pdf->addLineFormat($cols);
        
        $y    = 85;
        #_d($order_items);
        #exit();
        foreach($order_items['data'] as $order_item){
            $line = array( $translation['lbl_articlenumber']        => $order_item['article_number'],
                           $translation['lbl_description']          => $order_item['article_name'],
                           $translation['lbl_color']                => join(',',$joinColors),
                           $translation['lbl_size']                 => $order_item['vis_size'],
                           $translation['lbl_quantity']             => $order_item['quantity'],
                           $translation['lbl_box']                  => ($order_item['package_box'])?$order_item['package_box']:'n/a');
            $totals = $totals+($order_item['sale_price']*$order_item['quantity']);                           
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }

//        $pdf->addKaderTotals();
//
//        $tot_prods = array( array ( "px_unit" => 600, "qte" => 1, "tva" => 1 ),
//                            array ( "px_unit" =>  10, "qte" => 1, "tva" => 1 ));
//        $tab_tva = array( "1"       => 19.6,
//                          "2"       => 5.5);
////        $params  = array( "RemiseGlobale" => 1,
////                              "remise_tva"     => 1,       // {la remise s'applique sur ce code TVA}
////                              "remise"         => 0,       // {montant de la remise}
////                              "remise_percent" => 10,      // {pourcentage de remise sur ce montant de TVA}
////                          "AccompteExige" => 1,
////                              "accompte"         => 0,     // montant de l'acompte (TTC)
////                              "accompte_percent" => 15);
////                    
//        $pdf->addTotals($totals,19);
//        $pdf->addRemarque("lalala");
        //$pdf->addCadreEurosFrancs();
        $pdf->Output();
        exit();
        
    }
  
}