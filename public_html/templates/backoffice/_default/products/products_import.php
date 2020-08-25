<?php
class Products_import{
    function  run($params){
        ini_set('upload_max_filesize','128M');
        if(isset($_FILES['csvfile']['tmp_name']))
            $_SESSION['csv'] = self::csvToArray($_FILES['csvfile']['tmp_name']);            
                                          
        if(isset($_SESSION['csv'])){
            $params['csv_file_set'] = true;
            $params['csv']  = &$_SESSION['csv'];
        }
                      
                                
        if(!isset($params['importkey']))
            $params['importkey'] = 'import'.date('YmdHis');
        
        
        $cols = array_flip(Db::instance()->getTableCols('catalogue'));
        unset($cols['global_stock'],$cols['id']);
        $params['table_cols'] = array_flip($cols);
        
        $sheetColCount          = count($_SESSION['csv'][0]['rowdata']);
        $params['colrange']     = range(0,$sheetColCount-1);

        if($params['_do']=='assign'){
            self::assign($params);
        }        
        return $params;
    }
    private static function assign($params){        
        foreach($params['columns'] as $id=>$column)
            if(trim($column)=='')
                unset($params['columns'][$id]);                                                            
        $sql = sprintf('INSERT INTO catalogue (%s)',join(",",$params['columns'])).' VALUES ';        
        foreach($params['csv'] as $row)
            $clauses[] = '("'.join('","',$row['rowdata']).'")';                        
        $sqlFinal  = $sql.join(",".PHP_EOL,$clauses); 
        query($sqlFinal,__METHOD__);
        unset($_SESSION['csv']);
        redirect($params['root'].'/products/catalogue.html');        
    }
 
    private function csvToArray($fileName){
        $row = 0;
        if (($handle = fopen($fileName, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {                                                
                $out[$row]['rowdata'] = $data;                
                $row++;
            }
            fclose($handle);
            return $out;
        }        
    }


}