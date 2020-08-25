<?php
class Stock_move{
    function run($params){
        if($params['data'])
            parse_str($params['data'], $move);

        if($params['_do']=='reserve'){                                    
            if(is_array($move)){
                foreach($move['move'] as $productId=>$quantity){                    
                    if($quantity>0){
                        if(!isset($params['did'])){
                            $params['did'] = DeliveryDao::createBlank('internal');
                            TransferDao::create($params['did'],$params['to_location']);
                        }
                        $stock = ProductDao::getProductLocations($productId, $params['from_location']);
                                                                        
                        foreach($stock as $stocklocation){
                            if(($stocklocation['quantity'] <= $quantity) && $quantity>0){
                                $quantity = $quantity - $stocklocation['quantity'];
                                DeliveryDao::insertDeliveryRecord($params['did'], $productId, $stocklocation['configuration_id'], 0-$stocklocation['quantity']);
                            }else if($quantity>0){
                                DeliveryDao::insertDeliveryRecord($params['did'], $productId, $stocklocation['configuration_id'], 0-$quantity);
                                $quantity = 0;
                            }
                        }                    
                    }
                }
            }
            NotesDao::store($params['note_txt'],$params['to_location']);
        }                
        $params['from_location']        = (empty($params['from_location']))?WarehouseDao::getMainWarehouseId():$params['from_location'];        
        $params['locations']            = WarehouseDao::getLocations();

        
        //if the to location is not set, get a random location that is not the current location.
        if(empty($params['to_location'])){
            foreach($params['locations'] as $id=>$location){
                if($location['id']!=$params['from_location']){
                    $params['to_location']=$location['id'];
                    break;
                }
            }
        }

        $params['from_location_name']   = Location::getName($params['from_location']);
        $params['to_location_name']     = Location::getName($params['to_location']);

        $params['locations_to']         = self::makeDropdown($params['locations'],$params['from_location'],$params['to_location']);
        
        $params['products']             = self::getData($params['from_location'],$params['to_location'],$params['filter']);
        $params['product_move_table']   = parse('inc/product_move_table',$params);
        return $params;
    }
    function makeDropdown($locations,$from,$to){
        foreach($locations as $location){
            if($location['id']!=$from)
                $output[] = sprintf('<option value="%s" %s>%s</option>',$location['id'],(($location['id']==$to)?'selected="selected"':''),$location['name']);
        }
        if(!empty($output))
            return join(PHP_EOL,$output);
    }
    function getData($fromLocation,$toLocation,$filter=''){
           if(trim($filter)!='')
               $extraWhere = sprintf('AND (c.article_number LIKE "%%%1$s%%" OR c.article_name LIKE "%%%1$s%%" OR c.description LIKE "%%%1$s%%")',$filter);

           $sql = sprintf('SELECT
                                    c.article_number,
                                    c.article_name,
                                    s.product_id,
                                    s.configuration_id,
                                    SUM(s.quantity) quantity_from
                            FROM
                                    stock s,
                                    warehouse_configuration wc,
                                    warehouse_locations wl,
                                    catalogue c
                            WHERE
                                    s.configuration_id = wc.id
                            AND	wc.location_id = wl.id
                            AND	c.id=s.product_id
                            AND	wl.id=%1$s
                            %3$s
                            GROUP BY
                                    s.product_id,
                                    wl.id
                            HAVING quantity_from > 0
                            ORDER BY wc.path, wc.rack, wc.shelf, c.article_number',$fromLocation,$toLocation,$extraWhere);

          $data = fetchArray($sql,__METHOD__);
          if(empty($data))
            return;
          foreach($data as $key=>$row)
              if(empty($data[$key]['quantity_to'])){
                  $data[$key]['quantity_to'] = '0';

              }
          return $data;
    }    
}