<?php
ini_set('memory_limit', '500M');

require_once('./libs/excelexport/PHPExcel.php');

class Amazonexport{
    
    function run($params){
        if($params['password']!='d3jo87w93'){
            exit('Wrong password');
        }
        $exceltitle = "Amazon germany export";
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("NuiCart");
        $objPHPExcel->getProperties()->setLastModifiedBy("NuiCart");
        if(!empty($exceltitle)){
            $objPHPExcel->getProperties()->setTitle($exceltitle);
            $objPHPExcel->getProperties()->setSubject($exceltitle);  
            $objPHPExcel->getActiveSheet()->setTitle($exceltitle);
        }
        
        $az         = range('A','Z');
        $azCopy     = $az;
        $azCopy2    = $az;
        
        foreach($azCopy as $num=>$char){
            foreach($azCopy2 as $num2=>$char2){
                $tmp[] = $char.$char2; 
            }
        }
        $aToZz  = array_merge($az,$tmp); 
        $data   = self::getData();

        $curCol = 0;
        foreach($data['data'][0] as $fieldName=>$fieldVal){
            $objPHPExcel->getActiveSheet()->SetCellValue($aToZz[$curCol].'1', $fieldName);
            $curCol++;
        }

        foreach($data['data'] as $rowNum=>$fields){
            $colCount = count($fields);
            $curCol = 0;
            foreach($fields as $fieldName=>$fieldVal){
                $objPHPExcel->getActiveSheet()->SetCellValue($aToZz[$curCol].($rowNum+2), $fieldVal);
                $curCol++;
            }
        } 
		
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$exceltitle.'.xlsx"');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output'); 
        exit();        
    }
    
    private static function getData(){
        $sql = sprintf('SELECT 
                            c.article_number as `Article number`,	
                            c.ean as `EAN Code`,
                            ROUND(c.sale_price * 1.%s,2) as `Price`,
                            1 as `Quantity`,
                            ct.title as `Product Title`,
                            ct.description as `Product Description`,                                                                                    
                            IF(c.photo=1,CONCAT("http://allamericansports.nuicart.nl/img/upload/",c.id,".jpg"),"") as `Image URL`,
                            "" Maufacturer,
                            brands.value as Brand
                            FROM 
                                catalogue c,
                                catalogue_translation ct,
                                webshops w,
                                locales l,
                                lookups as brands
                                                                                                                                                                                                
                            WHERE
                                c.deleted IS NULL
                            AND c.in_webshop=1
                            AND brands.`group` = "brand"
                            AND c.brand = brands.id                                                        
                            AND ct.fk_catalogue = c.id
                            AND ct.fk_webshop = w.id
                            AND ct.fk_locale = l.id                            
                            AND w.hostname = "allamericansports.nl"
                            AND l.locale="en"                                                                                                                
                             
                            ',Cfg::getPref('btw')
                            );

        $out['data'] = fetchArray($sql,__METHOD__);
        return $out;        
    }
}