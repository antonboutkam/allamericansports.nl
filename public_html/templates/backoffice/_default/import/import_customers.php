<?php
class Import_customers{
    function  run($params){
        if(isset($_FILES['file']['tmp_name'])&&$_FILES['file']['tmp_name']!=''){
            $_SESSION['csv'] =  self::parse($_FILES['file']['tmp_name']);                        
        }
        $params['state'] = 'start';
        if($_SESSION['csv']){
            $params['state'] = 'assign';
            $params['csv'] = $_SESSION['csv'];

            foreach($params['csv'] as $row=>$fields){
                $params['table'] .= "<tr>\n";
                foreach($fields as $id=>$field){
                    $params['table'] .= "<td>".$field."</td>";
                }
                $params['table'] .= "</tr>\n";
            }
            $fields = DB::instance()->getTableCols('relations');
            $dropdown .= "<option>trash</option>";
            foreach($fields as $field){
                $dropdown .= "<option>$field</option>";
            }
            foreach(range(0,count($params['csv'][0])) as $key=>$val)
                    $params['dropdowns'][] = array('dropdown'=>$dropdown,'col'=>$key);
        }

        if($params['assign']){
            self::assign($params);
            $params['state'] = 'done';
        }
        return $params;
    }

    private static function parse($filename){
        $fp = fopen($filename,'r+');
        while($line = fgetcsv($fp))
                $arr[] = $line;
        return $arr;
    }
    private static function assign($params){
        
        $sql = 'INSERT INTO relations (';
        foreach($params['col'] as $id=>$column)
            if($column != 'trash')
                $cols[] = "`$column`";
        $cols[] = 'type';

        $sql .= join(",",$cols).") VALUE";
        $running = false;
        foreach($params['csv'] as $row){
            if(!$running && $params['stripfirstcol']=='on'){
                $running = true;
                continue;
            }
            $dat = array();
            foreach($row as $id=>$column){                
                if($params['col'][$id]!='trash'){
                    $dat[] = '"'.addslashes ($column).'"';
                }                
            }
            $dat[] = '"prospect"';
            $lines[] = "(".join(",",$dat).")";
        }
        
        $sql = $sql.join(",",$lines);
        
        query($sql,__METHOD__);
    }
}