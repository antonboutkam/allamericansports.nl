<?php
class Ziplookup{
    function run($params){
        $data = file_get_contents($url = sprintf('http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false',$params['zip']));
        $data = json_decode($data,true);
        $out['city'] = $data['results'][0]['address_components'][1]['long_name'];
        $out['country'] = $data['results'][0]['address_components'][4]['long_name'];
        
        print json_encode($out);
        exit();
    }
}