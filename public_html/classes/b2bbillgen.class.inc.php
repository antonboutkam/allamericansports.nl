<?php
require_once('pdf/invoice.php');
class B2bBillgen{
    public static function run($params){
        $papertype = ($params['type']=='offer')?'OFFER':'BILL';
        
        Translate::init($params['lang'],'bill_pdf');
        $translation                    = Translate::getTranslation();
        Invoice::$translation           = $translation;
        $order                          = OrderDao::getOrder($params['orderid']);        


        $order_items                    = OrderDao::getOrderItems($params['orderid']);

        $helped_by  = array_shift(explode(' ',$order['accepted_by']));
        $pdf        = new Invoice( 'P', 'mm', 'A4' );
        
        $code       =  $papertype.$order['order_id'];
        //$pdf->Image(filename,x,y,w,h); 
        
        $pdf->AddPage();            
        $pdf->Code128(155,274,$code,50,3);
        
        $shop = Webshop::getWebshopById($order['fk_webshop']);
        if($shop=='_default'){
            $shop = 'viadennis.nl';
        }
         
        if(!isset($params['return_totals_no_pdf']) && !isset($params['return_totals_no_pdf_inc_vat']))                
            $pdf->Image('./img/custom/'.$shop.'/bill-logo.jpg',5,5);
        
   //     $pdf->SetXY(115,270);
        // @todo, custom name hier invoeren.

        $webshop_settings = Webshop::getWebshopSettings($shop);
        


        $billingAddress = Cfg::getPref('billing_address');
        
        
        $billingFooter = Cfg::getPref('billing_footer');
        if(!$billingAddress){
            $billingAddress = 'Factuuradres niet ingesteld';
        }
        if(!$billingFooter){
            $billingFooter = 'Bankgegegvens niet ingesteld';
        }
        
        $pdf->addSociete($webshop_settings['bill_title'],$billingAddress,10,35);
        
		if(strtolower($order['paymethod_visible'])=='op rekening'){
			$label = 'Overmaken binnen 5 dagen naar bankrekeningnummer';
            
            $billingCredit = Cfg::getPref('billing_credit');
            //$billingCredit = 'lalala';
            $pdf->addSociete($label ,$billingCredit,10,242);            
		}else{
            $pdf->addSociete('Bank/bedrijf gegevens' ,$billingFooter,10,247);		  
		}
        		
        
        
                       
        $label = ($papertype=='BILL')?$translation['lbl_bill']:$translation['lbl_offer'];

        $pdf->fact_dev($label." ", str_pad($order['order_id'], 6, "0", STR_PAD_LEFT));


        if($params['type']=='summate')
            $pdf->addSummate($translation['lbl_summation']." ".$order['paydate_visible']);

        if($params['type']=='copy')    
            $pdf->temporaire($translation['lbl_billcopy']);

        
        $pdf->addDate($order['order_date_visible']);
        $pdf->addClient(str_pad($order['relation_id'], 6, "0", STR_PAD_LEFT));
        $pdf->addPageNumber("1");   
        
        $vat = ($order['foreign_vat'])?sprintf("\nBTW %s",$order['foreign_vat']):''; 

        $tmpheader = ($order['company_name']!='')?$order['company_name']:$order['cp_firstname'].' '.$order['cp_lastname'];
        $pdf->addClientAdresse(sprintf("%s\n%s %s\n%s %s\n%s%s",
                                            $tmpheader,
                                            $order['billing_street'],
                                            $order['billing_number'],
                                            $order['billing_postal'],
                                            $order['billing_city'],
                                            $order['billing_country'],
                                            $vat),10,65);

        if($papertype=='BILL')
            $pdf->addReglement($order['paymethod_visible']);
        else
            $pdf->addEcheance($order['paydate_visible'],10);
        

        if($params['type']!='summate'&& $papertype!='OFFER')
            $pdf->addEcheance($order['paydate_visible']);
        // $pdf->addNumTVA("FR888777666");
        // $pdf->addReference($helped_by);
        $cols=array( $translation['lbl_articlenumber']        => 45,
                     $translation['lbl_description']         => 68,
                     $translation['lbl_quantity']               => 22,
                     $translation['lbl_price']                => 26,
                     $translation['lbl_total']               => 30);
        $pdf->addCols( $cols,105,-60);
        $cols=array( $translation['lbl_articlenumber']        => "L",
                     $translation['lbl_description']         => "L",
                     $translation['lbl_quantity']               => "R",
                     $translation['lbl_price']                => "R",
                     $translation['lbl_total']                  => "R");

        
        $y    = 114;
        
        if(is_array($order_items['data'])){
            foreach($order_items['data'] as $order_item){
                $line = array( $translation['lbl_articlenumber']        => ($order_item['article_number'])?$order_item['article_number'] : ' - ',
                               $translation['lbl_description']          => $order_item['article_name'],
                               $translation['lbl_quantity']             => $order_item['quantity'],
                               $translation['lbl_price']                => sprintf("%0.2f",$order_item['sale_price']),
                               $translation['lbl_total']                => sprintf("%0.2f",$order_item['sale_price']*$order_item['quantity']));
                $totals = $totals+($order_item['sale_price']*$order_item['quantity']);
                
                $size   = $pdf->addLine( $y, $line );
                $y   += $size + 2;
                if($order_item['discount_perc']>0){
                    $line = array( $translation['lbl_articlenumber']        => ' ',
                                   $translation['lbl_description']          => 'Korting '.$order_item['discount_perc'].'% op art. '.$order_item['article_number'],
                                   $translation['lbl_quantity']             => '1',
                                   $translation['lbl_price']                => sprintf("%0.2f",$order_item['pay_price']-$order_item['sale_price']),
                                   $translation['lbl_total']                => sprintf("%0.2f",($order_item['pay_price']-$order_item['sale_price'])*$order_item['quantity']));
					$totals = $totals+($order_item['sale_price']*$order_item['quantity']);
                    $size   = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
                }
            }
        }

        if($order['send_cost']!='0.00'){
            $line = array( $translation['lbl_articlenumber']        => 'Verzendkosten',
                           $translation['lbl_description']          => 'Verzendkosten',
                           $translation['lbl_quantity']             => 1,
                           $translation['lbl_price']                => sprintf("%0.2f",$order['send_cost']),
                           $translation['lbl_total']                => sprintf("%0.2f",$order['send_cost']));
            $totals = $totals+$order['send_cost'];
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;     
        }

        if($order['pay_fee_perc']!='0.00'){
            $amount = ($totals / 100) * $order['pay_fee_perc'];
            
            $line = array( $translation['lbl_articlenumber']        => 'TRANSACTIEKOSTEN',
                           $translation['lbl_description']          => 'Percentage '.$order['pay_fee_perc'].'%',
                           $translation['lbl_quantity']             => '1',
                           $translation['lbl_price']                => sprintf("%0.2f",$order['pay_fee_perc']).'%',
                           $translation['lbl_total']                => number_format($amount,2));
            
            $totals = $totals + $amount;
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;            
        }
        
        if($order['discount_fixed']!='0.00'){
            $line = array( $translation['lbl_articlenumber']        => 'Korting',
                           $translation['lbl_description']          => 'Korting '.$order['discount_fixed'],
                           $translation['lbl_quantity']             => '1',
                           $translation['lbl_price']                => '-'.$order['discount_fixed'],
                           $translation['lbl_total']                => '-'.sprintf("%0.2f",$order['discount_fixed']));
            $totals = $totals-$order['discount_fixed'];
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }
        if($order['discount_perc']!='0.00'){
            $amount = ($totals / 100) * $order['discount_perc'];
            $line = array( $translation['lbl_articlenumber']        => 'Korting',
                           $translation['lbl_description']          => 'Kortingspercentage '.$order['discount_perc'].'%',
                           $translation['lbl_quantity']             => '1',
                           $translation['lbl_price']                => sprintf("%0.2f",$order['discount_perc']).'%',
                           $translation['lbl_total']                => '-'.sprintf("%0.2f",$amount));

            $totals = $totals - $amount;
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }


        if($order['pay_fee_fixed']!='0.00'){
            $line = array( $translation['lbl_articlenumber']     => 'Transactiekosten',
                           $translation['lbl_description']       => 'Transactiekosten '.strtolower($order['paymethod_visible']),
                           $translation['lbl_quantity']          => '1',
                           $translation['lbl_price']             => $order['pay_fee_fixed'],
                           $translation['lbl_total']             => sprintf("%0.2f",$order['pay_fee_fixed']));
            $totals = $totals+$order['pay_fee_fixed'];
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }

        if($params['return_totals_no_pdf'])
            return $totals;
        if($params['return_totals_no_pdf_inc_vat']){
            $vat = 1+(Cfg::getPref('btw')/100);
            return round($totals * $vat,2);
        }
                        
            
        // Inter coomunitarie levering
        if($order['foreign_vat']){
            $pdf->addTotals($totals,0,133,262);    
        }else{
            $pdf->addTotals($totals,Cfg::getPref('btw'),133,262);
        }
        
            
        $pdf->addKaderTotals(122);

        $tot_prods = array( array ( "px_unit" => 600, "qte" => 1, "tva" => 1 ),
                            array ( "px_unit" =>  10, "qte" => 1, "tva" => 1 ));
        $tab_tva = array( "1"       => 19.6,
                          "2"       => 5.5);
  

        // Terms and conditions regel    
        $pdf->SetXY(10,274);
        $pdf->SetFont('Arial','',6);
        $string = 'Op deze factuur zijn de algemene voorwaarden van toepassing, deze kunt u vinden op http://www.'.$order['hostname'].'/page/algemenevoorwaarden';
        $length = $pdf->GetStringWidth( $string );
        $pdf->Cell( $length, 2, $string);        
        
        $pdf->Output($params['outfile']);                     
    } 
}