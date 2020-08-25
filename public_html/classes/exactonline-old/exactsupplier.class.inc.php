<?php
class ExactSupplierOLD extends ExactBase{
   public static function getAll(){
        $ch = self::curlConnect();
                
        $url = self::$baseurl."/docs/XMLDownload.aspx?Topic=Accounts&Params_IsSupplier=1&pagesize=1&PartnerKey=".self::$partnerkey."&UserName=".self::$username."&Password=".self::$password;        
        
        curl_setopt($ch, CURLOPT_URL, $url);        
        $result = curl_exec($ch);                
        
        curl_close($ch);
        $xmlParsed = simplexml_load_string($result);
        
        foreach($xmlParsed->Accounts->Account as $item){
            unset($item->VATSales);
            unset($item->VATPurchase);
            unset($item->VATLiability);
            unset($item->IntraStat);
                    
            $store['origin']            = 'exact';                                      
            $store['exact_id']          = $item->attributes()->code->__toSTring();
            $store['type']              = 'supplier';
            $store['company_name']      = $item->Name->__toSTring();
            $store['website']           = $item->HomePage->__toSTring();            
            
            if(property_exists($item,'Language')){
                $store['fk_locale']         = Lang::getLocaleIdByLanguageCode($item->Language->attributes()->code->__toSTring());
            }
            if(property_exists($item,'Address')){
                $store['billing_street']    = $item->Address->AddressLine1->__toSTring();
                $store['billing_number']    = $item->Address->AddressLine2->__toSTring();
                $store['billing_postal']    = $item->Address->PostalCode->__toSTring();
                
                if(property_exists($item->Address,'State')){
                    $store['billing_city']      = $item->Address->State->attributes()->code->__toSTring();
                }
                $countryName                = Countryiso::getNameByIso2Code($item->Address->Country->attributes()->__toSTring());
            }
            $store['billing_country']   = $countryName;
            $store['phone']             = $item->Phone->__toSTring();
            $store['fax']               = $item->Fax->__toSTring();
            $store['email']             = $item->Email->__toSTring();
            
            $tpl = "SELECT id FROM relations WHERE exact_id = %d";
            $sql = sprintf($tpl,$store['exact_id']);
            $id = fetchVal($sql,__METHOD__);
            RelationDao::store($store,$id);
        }                        
   }
}    