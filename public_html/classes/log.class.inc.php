<?php
class Log
{
    public static function console($msg){
        $_SESSION['debug']['console_log'][] = $msg;        
    }
    public static function search($query,$webshopId){
        
        $sql = sprintf('INSERT INTO stats_query(search_query,hits)
                        VALUE("%s",1) ON DUPLICATE KEY UPDATE hits=hits+1',
                        quote($query));
        query($sql,__METHOD__);        
        
        $sql = sprintf('INSERT INTO stats_search (fk_query,webshop_id,ip)
                        VALUE(
                        (SELECT id FROM stats_query WHERE search_query="%s"),%d,"%s")',
                        quote($query),
                        $webshopId,
                        $_SERVER['REMOTE_ADDR']);
        query($sql,__METHOD__);                        
    }
    public static function message($type,$message,$method){
        $dirName = '../../log/'.$type;

        if(!is_dir($dirName))
        {
// echo $dirName . "<br>";
            mkdir($dirName, 0777, true);
        }

        if(!is_writable(dirname($dirName)))
        {
            throw new Exception("Directory ".dirname($dirName).' is not writable');
        }

        file_put_contents($dirName.'/'.date('Ymd').'.log',$_SERVER['REMOTE_ADDR'].' '.date('H:i:s').' '.$method.' '.$message."\n",FILE_APPEND);
    }
}
