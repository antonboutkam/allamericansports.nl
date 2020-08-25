<?php
class Xml_salesmonthdays{
    function  run($params){
        $data = self::getData();
        $out[] = "<graph caption='Verzonden orders' xAxisName='Maand' yAxisName='Aantal' showValues='0' numberPrefix='' decimalPrecision='0' bgcolor='F3f3f3' bgAlpha='70' showColumnShadow='1' divlinecolor='c5c5c5' divLineAlpha='60' showAlternateHGridColor='1' alternateHGridColor='f8f8f8' alternateHGridAlpha='60' >";
        foreach($data as $row){
            $out[] = sprintf("<set name='%s' value='%s' color='b61817'/>",$row['day'],$row['orders']);         
        }
        $out[] = '</graph>';
        exit(join(PHP_EOL,$out));        
    }

    function getData(){
        $sql = sprintf('SELECT
                            COUNT(o.id) orders,
                            DATE_FORMAT(o.send,"%%e") day
                        FROM 
                            orders o
                        WHERE 
                            send BETWEEN SYSDATE() - INTERVAL 20 DAY AND SYSDATE() 
                        GROUP BY DATE_FORMAT(o.send,"%%c-%%e")
                        ORDER BY DATE_FORMAT(o.send,"%%c-%%e") DESC');
        return fetchArray($sql,__METHOD__);
    }

}