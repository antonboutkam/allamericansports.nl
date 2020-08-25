<?php
require_once('pdf/invoice.php');
class Billgen{
    private static $translation;
    public static function run($params){        
        /*****
         * 
         * DIT IS EEN AANGEPASTE VARIANT VOOR PRIJZEN INC BTW
         * 
         */
        $btw_float = '1.'.Cfg::getPref('btw');
        $btw_float = (float)$btw_float;
        $papertype = ($params['type']=='offer')?'OFFER':'BILL';
        #define('EURO',chr(128).' ');                    
        
        $order                          = OrderDao::getOrder($params['orderid']);        
        $params['lang']                 = Lang::getCodeByLanguageId($order['fk_locale']);
                
        self::$translation              = Translate::getTranslationNoInject($params['lang'],'bill_pdf','webshops');        
        Invoice::$translation           = self::$translation;
        $order_items                    = OrderDao::getOrderItems($params['orderid']);

        $exploded   = explode(' ',$order['accepted_by']);
        $helped_by  = array_shift($exploded);
        $pdf        = new Invoice('P', 'mm', 'A4' );
        
        
        #_d($order);
        
        #_d($order_items);
        
        #exit();
        
        $code       =  $papertype.$order['order_id'];
        //$pdf->Image(filename,x,y,w,h); 
        
        $pdf->AddPage();            
       # $pdf->Code128(155,274,$code,50,3);
        
        $shop = Webshop::getWebshopById($order['fk_webshop']);
        if($shop=='_default'){
            $shop = 'allamericansports.nl';
        }
         
        if(!isset($params['return_totals_no_pdf']) && !isset($params['return_totals_no_pdf_inc_vat']))                
            $pdf->Image('./img/custom/'.$shop.'/bill-logo.jpg',5,5);

        if($params['lang']=='en')
            $params['lang'] = 'gb';
        
        $webshop_settings   = Webshop::getWebshopSettings($shop);       
        $billingAddress     = Cfg::getPref('billing_address_'. $params['lang']);               
        $billingFooter      = Cfg::getPref('billing_footer_'. $params['lang']);
        
        if(!$billingAddress){
            $billingAddress = 'Factuuradres niet ingesteld';
        }
        if(!$billingFooter){
            $billingFooter = 'Bankgegegvens niet ingesteld';
        }
        
        if($order['paymethod_visible']=='Contant of Pin bij afhalen' && $params['lang']=='gb')
               $order['paymethod_visible'] = 'Cash, pay at pickup'; 
        
        
        $pdf->addSociete($webshop_settings['bill_title'],$billingAddress,10,30);
        
        if(strtolower($order['paymethod_visible'])=='op rekening'){
            $label = 'Overmaken binnen 5 dagen naar bankrekeningnummer';            
            $billingCredit = Cfg::getPref('billing_credit');
            $pdf->addSociete($label ,$billingCredit,10,242);            
        }else{
            if($params['lang']=='gb')
                $pdf->addSociete('',$billingFooter,10,244);
            else
                $pdf->addSociete('Bank/bedrijf gegevens' ,$billingFooter,10,247);		  
        }        		                
                       
        $label = ($papertype=='BILL')?self::$translation['lbl_bill']:self::$translation['lbl_offer'];
        $pdf->fact_dev($label." ", str_pad($order['order_id'], 6, "0", STR_PAD_LEFT));
        
        if($params['type']=='summate')
            $pdf->addSummate(self::$translation['lbl_summation']." ".$order['paydate_visible'],80,95);

        if($params['type']=='copy')    
            $pdf->temporaire(self::$translation['lbl_billcopy']);
        
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
        $cols=array( self::$translation['lbl_articlenumber']        => 45,
                     self::$translation['lbl_description']          => 38,
                     self::$translation['lbl_color_uc']                => 18,
                     self::$translation['lbl_size_uc']                 => 18,
                     self::$translation['lbl_quantity']             => 22,
                     self::$translation['lbl_price']                => 30,
                     self::$translation['lbl_total']                => 20);
        $pdf->addCols($cols,105,-60);
        $cols=array( self::$translation['lbl_articlenumber']        => "L",
                     self::$translation['lbl_description']          => "L",
                     self::$translation['lbl_color_uc']                => "L",
                     self::$translation['lbl_size_uc']                 => "L",
                     self::$translation['lbl_quantity']             => "R",
                     self::$translation['lbl_price']                => "R",
                     self::$translation['lbl_total']                => "R");

        
        $y    = 114;
        
        if(is_array($order_items['data'])){
            foreach($order_items['data'] as $order_item){         
                $price      = $order_item['sale_price']*$btw_float;
                
                $quantity   = $order_item['quantity']; 
                if(in_array($order_item['type_vis'],array('Stof'))){
                    $totalprice = $price*$order_item['quantity'];
                    $price      = sprintf("%0.2f",$price * 100).' per  meter';                                        
                    $quantity   = sprintf("%0.2f",$quantity / 100).' meter';
                }else{
                    $price      = number_format($price,2,'.','');
                    $totalprice = $price*$order_item['quantity'];
                }
                
                
                $colors     =  ColorDao::getProductColors($order_item['p_id']);
                $joinColors = array();
                if(!empty($colors)){
                    foreach($colors as $color){
                        $joinColors[] = $color['color'];
                    }
                }
                          
                $line = array( self::$translation['lbl_articlenumber']        => ($order_item['article_number'])?$order_item['article_number'] : ' - ',
                               self::$translation['lbl_description']          => $order_item['article_name'],
                               self::$translation['lbl_color_uc']                => join(',',$joinColors),
                               self::$translation['lbl_size_uc']                 => $order_item['vis_size'],                               
                               self::$translation['lbl_quantity']             => $quantity,
                               self::$translation['lbl_price']                => EURO.number_format($price,2,",","."),
                               self::$translation['lbl_total']                => EURO.number_format(sprintf("%0.2f",$totalprice),2,",","."));
                # echo $totals.' + '.sprintf("%0.2f",$totalprice)."<br>";
                $totals = $totals+sprintf("%0.2f",$totalprice);
                
                $size   = $pdf->addLine( $y, $line );
                $y   += $size + 2;
                if($order_item['discount_perc']>0){
                    $discount = ($totalprice/100)*($order_item['discount_perc']);
                    $line = array( self::$translation['lbl_articlenumber']        => ' ',
                                   self::$translation['lbl_description']          => 'Korting '.$order_item['discount_perc'].'% op art. '.$order_item['article_number'],
                                   self::$translation['lbl_color_uc']                => '',
                                   self::$translation['lbl_size_uc']                 => '',
                                   self::$translation['lbl_quantity']             => '1',
                                   self::$translation['lbl_price']                => 'n.v.t.',/*sprintf("%0.2f",($totalprice/100)*(100-$row['discount_perc']))*/
                                   self::$translation['lbl_total']                => EURO.number_format(sprintf("%0.2f",$discount),2,",","."));
                    #  echo $totals.' - '.$discount."<br>";
                    $totals = $totals-$discount;
                    $size   = $pdf->addLine( $y, $line );
                    $y   += $size + 2;
                }
            }
         
        }

        if($order['pay_fee_perc']!='0.00'){
            $amount = ($totals / 100) * $order['pay_fee_perc'];
            
            $line = array( self::$translation['lbl_articlenumber']        => self::$translation['lbl_transactioncosts'],
                           self::$translation['lbl_description']          => self::$translation['lbl_percentage'].' '.$order['pay_fee_perc'].'%',
                           self::$translation['lbl_color_uc']                => '',
                           self::$translation['lbl_size_uc']                 => '',
                           self::$translation['lbl_quantity']             => '1',
                           self::$translation['lbl_price']                => sprintf("%0.2f",($order['pay_fee_perc'] * $btw_float)).'%',
                           self::$translation['lbl_total']                => EURO.number_format($amount,2,",","."));
            # echo $totals.' + '.$amount."<br>";
            $totals = $totals + $amount;
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;            
        }
        
        if($order['discount_fixed']!='0.00'){
            $line = array( self::$translation['lbl_articlenumber']        => self::$translation['lbl_discount'],
                           self::$translation['lbl_description']          => self::$translation['lbl_discount'].' '.$order['discount_fixed'],
                           self::$translation['lbl_color_uc']                => '',
                           self::$translation['lbl_size_uc']                 => '',
                           self::$translation['lbl_quantity']             => '1',
                           self::$translation['lbl_price']                => '-'.$order['discount_fixed'],
                           self::$translation['lbl_total']                => '- '.EURO.number_format(sprintf("%0.2f",($order['discount_fixed']*$btw_float)),2,",","."));
            # echo $totals.' - '.($order['send_cost']*$btw_float)."<br>";
            $totals = $totals-$order['discount_fixed'];
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }
        
        if($order['discount_perc']!='0.00'){
            $amount = ($totals / 100) * $order['discount_perc'];
            $line = array( self::$translation['lbl_articlenumber']        => self::$translation['lbl_discount'],
                           self::$translation['lbl_description']          => self::$translation['lbl_discountpercent'].' '.$order['discount_perc'].'%',
                           self::$translation['lbl_color_uc']                => '',
                           self::$translation['lbl_size_uc']                 => '',
                           self::$translation['lbl_quantity']             => '1',
                           self::$translation['lbl_price']                => sprintf("%0.2f",$order['discount_perc']).'%',
                           self::$translation['lbl_total']                => '- '.EURO.number_format(sprintf("%0.2f",$amount),2,",","."));
            # echo $totals.' - '.$amount."<br>";
            $totals = $totals - $amount;
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }


        if($order['pay_fee_fixed']!='0.00'){
            $line = array( self::$translation['lbl_articlenumber']     => self::$translation['lbl_transactioncosts'],
                           self::$translation['lbl_color_uc']             => '',
                           self::$translation['lbl_size_uc']              => '',
                           self::$translation['lbl_description']       => self::$translation['lbl_transactioncosts'].' '.strtolower($order['paymethod_visible']),
                           self::$translation['lbl_quantity']          => '1',
                           self::$translation['lbl_price']             => EURO.number_format(sprintf("%0.2f",$order['pay_fee_fixed']*$btw_float),2,",","."),
                           self::$translation['lbl_total']             => EURO.number_format(sprintf("%0.2f",$order['pay_fee_fixed']*$btw_float),2,",","."));
            $totals = $totals+($order['pay_fee_fixed']*$btw_float);
            
            # echo $totals.' + '.$order['pay_fee_fixed']*$btw_float."<br>";
            $size   = $pdf->addLine( $y, $line );
            $y   += $size + 2;
        }
        
        if($order['send_cost']!='0.00'){
            $line = array( self::$translation['lbl_articlenumber']        => self::$translation['lbl_sendcost'],
                           self::$translation['lbl_description']          => self::$translation['lbl_sendcostbox'],
                           self::$translation['lbl_color_uc']                => '',
                           self::$translation['lbl_size_uc']                 => '',                           
                           self::$translation['lbl_quantity']             => 1,
                           self::$translation['lbl_price']                => EURO.number_format(sprintf("%0.2f",$order['send_cost']*$btw_float),2,",","."),
                           self::$translation['lbl_total']                => EURO.number_format(sprintf("%0.2f",$order['send_cost']*$btw_float),2,",","."));
            # echo $totals.' + '.($order['send_cost']*$btw_float)."<br>";
            $totals = $totals+($order['send_cost']*$btw_float);
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
            $pdf->addTotals($totals,Cfg::getPref('btw'),170,262);
        }      
       # echo "Final totals $totals<Br>";
                     
        $pdf->addKaderTotals(122);
        $tot_prods = array( array ( "px_unit" => 600, "qte" => 1, "tva" => 1 ),
                            array ( "px_unit" =>  10, "qte" => 1, "tva" => 1 ));
        $tab_tva = array( "1"       => 19.6,
                          "2"       => 5.5);
  
        // Terms and conditions regel    
        $pdf->SetXY(10,274);
        $pdf->SetFont('Arial','',6);
        
        
        $string = self::$translation['lbl_terms_and_conditions'];
        $length = $pdf->GetStringWidth( $string );
        $pdf->Cell( $length, 2, $string);        

        if($params['return_binary'])
            return $pdf->Output('','S');            
        $pdf->Output($params['outfile']);                     
    } 
}