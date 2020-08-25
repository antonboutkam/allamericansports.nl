<?php
class ExactProduct extends ExactService {

    public function upload($iFkProduct){

        $sExactProductXml = self::makeById($iFkProduct);

        $sResponseXml = $this->getApi()->sendRequest('upload','Topic=Items&_Division_='.$this->getDivision(), 'post', $sExactProductXml['with_binary']);

        $responseArray = self::xml2Array($sResponseXml);

        // 0 error , 1=warning, 2=succes, 3=fatal
        if($responseArray['Messages']['Message']['@attributes']['type']!=2){
            ProductDao::setVal('exact_last_sync_fail',1,$iFkProduct);
            $msg[] = "Error product id $iFkProduct";
            $msg[] = "Error melding \"".$responseArray['Messages']['Message']['Description'].'"';
            if(!isset($_SERVER['HTTP_HOST']))
                echo join(",",$msg).PHP_EOL;
            // mail(Cfg::getPref('exact_online_status_mails'),'Allamericansports product '.$productId.' kon niet worden bijgewerkt in Exact',join(PHP_EOL,$msg));
        }else{
            ProductDao::setVal('exact_last_sync_fail',0,$iFkProduct);
        }
        ProductDao::setVal('exact_last_sync_desc ',$responseArray['Messages']['Message']['Description'],$iFkProduct);

        return null;
    }

    private static function makeById($id){
        $product  = self::getDataById($id);
        return self::makeOneWithData($product);
    }
    private static function getDataById($productId){
        $product            = ProductDao::getById($productId);
        $product['colors']  = ColorDao::getProductColors($productId);
        return $product;
    }
    private static function makeOneWithData($product){
        $xml['with_binary'] = self::top();
        $xml['with_binary'] .= self::element($product);
        $xml['with_binary'] .= self::foot();

        $xml['no_binary'] = self::top();
        $xml['no_binary'] .= self::element($product,true);
        $xml['no_binary'] .= self::foot();

        return $xml;
    }
    private static function top(){
        $xml =  '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<eExact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="eExact-XML.xsd">'.PHP_EOL.
            '<Items>';
        return $xml;
    }
    private static function foot(){
        $xml =  '</Items>'.PHP_EOL.
            '</eExact>';
        return $xml;
    }
    private static function element($data,$stripbinary=false,$imagedir='./img/upload/'){
        #pre_r($data);
        $img_name = $data['id'].'.jpg';
        #echo $imagedir.$img_name."\n";
        if($data['photo'] && file_exists($imagedir.$img_name)){
            $image  = base64_encode(file_get_contents($imagedir.$img_name));
            $has_img = true;
        }
        if($stripbinary){
            $image = 'binary-data-stripped';
        }

        $row =
            '<Item code="'.$data['ean'].'" searchcode="'.$data['article_number'].'">'.PHP_EOL;
        if(!empty($data['sport_label'])){
            $row .=
                '   <ItemCategory number="1" code="'.preg_replace('/[^a-zA-Z0-9_-]+/','',$data['sport_label']).'" class="Sports">'.PHP_EOL.
                '       <Description><![CDATA['.$data['sport_label'].']]></Description>'.PHP_EOL.
                '   </ItemCategory>'.PHP_EOL;
        }
        if(!empty($data['product_type'])){
            $row .=
                '   <ItemCategory number="2" code="'.preg_replace('/[^a-zA-Z0-9_-]+/','',$data['product_type']).'" class="Subcategorie">'.PHP_EOL.
                '       <Description><![CDATA['.$data['product_type'].']]></Description>'.PHP_EOL.
                '   </ItemCategory>'.PHP_EOL;
        }

        if(!empty($data['colors'][0]['color'])){
            $data['colors'][0]['color'] = str_replace('Bordeaux rood','Bordeaux Rood',$data['colors'][0]['color']);


            $row .=
                '   <ItemCategory number="3" code="'.preg_replace('/[^a-zA-Z0-9_-]+/','',$data['colors'][0]['color']).'" class="Kleur">'.PHP_EOL.
                '       <Description><![CDATA['.$data['colors'][0]['color'].']]></Description>'.PHP_EOL.
                '   </ItemCategory>'.PHP_EOL;
        }


        if(!empty($data['product_size_tag'])){


            $data['product_size_tag'] = str_replace('One size','One Size',$data['product_size_tag']);

            $row .=
                '   <ItemCategory number="4" code="'.preg_replace('/[^a-zA-Z0-9_-]+/','',$data['product_size_tag']).'" class="Maat">'.PHP_EOL.
                '       <Description><![CDATA['.$data['product_size_tag'].']]></Description>'.PHP_EOL.
                '   </ItemCategory>'.PHP_EOL;
        }

        if($data['supplier']){
            $supplier = RelationDao::getById($data['supplier']);
            if(!empty($supplier)){

                $row .="    <ItemAccounts>".PHP_EOL;
                $row .="        <ItemAccount>".PHP_EOL;
                $row .="        <Account code=\"".$supplier['exact_id']."\">".PHP_EOL;
                $row .="        <Name>".$supplier['company_name']."</Name>".PHP_EOL;
                $row .="        </Account>".PHP_EOL;
                $row .="        <IsPrimary>1</IsPrimary>".PHP_EOL;
                $row .="        <SupplierItemCode>".$data['ean']."</SupplierItemCode>".PHP_EOL;
                $row .="            <Purchase>".PHP_EOL;
                $row .="                <Price>".PHP_EOL;
                $row .="                    <Currency code=\"EUR\" />".PHP_EOL;
                $row .="                    <Value>".$data['purchase_price'].'.'.$data['purchase_price_ct']."</Value>".PHP_EOL;
                $row .="                </Price>".PHP_EOL;
                $row .="            </Purchase>".PHP_EOL;
                $row .="        </ItemAccount>".PHP_EOL;
                $row .="    </ItemAccounts>".PHP_EOL;
            }
        }



        #print_r($data['sale_price_ct']);
        #exit();
        if(empty($data['description'])){
            // Description is verplicht dus indien niet opgegeven, toch iets invullen.
            $row .= '   <Description>Geen omschrijving opgegeven</Description>'.PHP_EOL;
        }else{
            $row .= '   <Description><![CDATA['.$data['article_name'].']]></Description>'.PHP_EOL;
        }
        $row .=
            '   <ExtraDescription><![CDATA['.$data['description'].']]></ExtraDescription>'.PHP_EOL.
            '   <IsSalesItem>1</IsSalesItem>'.PHP_EOL.
            '   <IsStockItem>1</IsStockItem>'.PHP_EOL.
            '   <IsPurchaseItem>1</IsPurchaseItem>'.PHP_EOL.
            '   <IsFractionAllowedItem>0</IsFractionAllowedItem>'.PHP_EOL.
            '   <IsMakeItem>0</IsMakeItem>'.PHP_EOL.
            '   <IsSubContractedItem>0</IsSubContractedItem>'.PHP_EOL.
            '   <IsTime>0</IsTime>'.PHP_EOL.
            '   <IsOnDemandItem>0</IsOnDemandItem>'.PHP_EOL.
            '   <IsWebshopItem>0</IsWebshopItem>'.PHP_EOL.
            '   <CopyRemarks>1</CopyRemarks>'.PHP_EOL;

        $data['ledger_label'] = str_replace('AmericanFootball','Football',$data['ledger_label']);

        $ledgers = array('Football','Honkbal','Indoor','Outdoor','Overig','Softbal','Sportkleding','Supplementen','Turnen');

        if(in_array($data['ledger_label'],$ledgers)){
            $row .= '<Assortment code="'.$data['ledger_label'].'"></Assortment>'.PHP_EOL;
        }

        $row .=
            '   <Sales>'.PHP_EOL.
            '       <Price>'.PHP_EOL.
            '            <Currency code="EUR" />'.PHP_EOL.
            '            <VAT code="4" type="I" charged="0" vattransactiontype="B" blocked="0"></VAT>'.PHP_EOL.
            '            <Value>'.$data['sale_price_vat'].'.'.$data['sale_price_vat_ct'].'</Value>'.PHP_EOL.
            '       </Price>'.PHP_EOL.
            '       <Unit code="stuk" type="O">'.PHP_EOL.
            '           <Description>Piece</Description>'.PHP_EOL.
            '       </Unit>'.PHP_EOL.
            '   </Sales>'.PHP_EOL.
            '   <Costs>'.PHP_EOL.
            '       <Price>'.PHP_EOL.
            '           <Currency code="EUR"/>'.PHP_EOL.
            '               <Value>'.$data['purchase_price'].'.'.$data['purchase_price_ct'].'</Value>'.PHP_EOL.
            '       </Price>'.PHP_EOL.
            '   </Costs>'.PHP_EOL.

            /*
            '   <Costs>'.PHP_EOL.
            '       <Price>'.PHP_EOL.
            '           <Currency code="EUR" />'.PHP_EOL.
            '            <Value>'.$data['purchase_price'].'.'.$data['purchase_price_ct'].'</Value>'.PHP_EOL.
            '       </Price>'.PHP_EOL.
            '   </Costs>'.PHP_EOL.
            */
            '   <DateStart></DateStart>'.PHP_EOL.
            '   <DateEnd></DateEnd>'.PHP_EOL;

        if($has_img){
            $row .= '<Image>'.PHP_EOL.
                '<Name>'.$img_name.'</Name>'.PHP_EOL.
                '<BinaryData>'.$image.'</BinaryData>'.PHP_EOL.
                '</Image>'.PHP_EOL;
        }

        $i = 1;
        /*
        if(!empty($data['product_type'])){
            $row .=
                '   <ItemCategory number="'.$i.'" code="'.preg_replace('/[^A-Z0-9]+/','',strtoupper($data['product_type'])).'" class="Sports">'.PHP_EOL.
                '       <Description><![CDATA['.$data['product_type'].']]></Description>'.PHP_EOL.
                '  </ItemCategory>'.PHP_EOL;
                $i++;
        }
        */
        $row .='   <Note><![CDATA['.$data['description_nl'].']]></Note>'.PHP_EOL.
            '</Item>'.PHP_EOL;

        #mail('info@nuicart.nl','Xml',$row);
        return $row;
    }

}