<?php
class ExactSupplier extends ExactService{

    /**
     * @param string $sCode
     * @return string
     * @throws ErrorException
     */
    public function syncAllSuppliers(){
        $sTimestamp = null;
        while(true)
        {
            // List accounts
            $sAddUrl = '';
            if($sTimestamp)
            {
                $sAddUrl = '&TSPaging='.$sTimestamp;
            }
            $sResponseXml = $this->getApi()->sendRequest('download','Topic=Accounts&Params_IsSupplier=1&pagesize=1&_Division_='.$this->getDivision().$sAddUrl, 'get');

            $xmlParsed = simplexml_load_string($sResponseXml);

            if(count($xmlParsed->Accounts->Account) === 1000)
            {
                $bHasNextPage = true;
                $sTimestamp = $xmlParsed->Topics->Topic->attributes()->ts_d->__toString();
            }
            else
            {
                $bHasNextPage = false;
            }

            foreach($xmlParsed->Accounts->Account as $item){
                unset($item->VATSales);
                unset($item->VATPurchase);
                unset($item->VATLiability);
                unset($item->IntraStat);

                $store['origin']            = 'exact';
                $store['exact_id']          = $item->attributes()->code->__toSTring();

                if((string)$item->IsSupplier == 1)
                {
                    $store['type']              = 'supplier';
                }
                else
                {
                    continue;
                }
                $store['company_name']      = $item->Name->__toSTring();
                $store['website']           = $item->HomePage->__toSTring();

                if(property_exists($item,'Language'))
                {
                    $store['fk_locale']         = Lang::getLocaleIdByLanguageCode($item->Language->attributes()->code->__toSTring());
                }
                if(property_exists($item,'Address'))
                {
                    $store['billing_street']    = $item->Address->AddressLine1->__toSTring();
                    $store['billing_number']    = $item->Address->AddressLine2->__toSTring();
                    $store['billing_postal']    = $item->Address->PostalCode->__toSTring();

                    if(property_exists($item->Address,'State'))
                    {
                        $store['billing_city']      = $item->Address->State->attributes()->code->__toSTring();
                    }
                    $countryName                = Countryiso::getNameByIso2Code($item->Address->Country->attributes()->__toSTring());
                }
                $store['billing_country']   = $countryName;
                $store['phone']             = $item->Phone->__toSTring();
                $store['fax']               = $item->Fax->__toSTring();
                $store['email']             = $item->Email->__toSTring();

                $sQuery = "SELECT id FROM relations WHERE exact_id = '".quote($store['exact_id'])."'";
                $iRelationId = fetchVal($sQuery, __METHOD__);
                RelationDao::store($store, $iRelationId);
            }
            if(!$bHasNextPage)
            {
                break;
            }
        }
    }
}

