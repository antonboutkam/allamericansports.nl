<?php
class NotesDao{
    public static function getAll($locationId=null){
        
        $sql = sprintf('SELECT 
                            notes.*,
                            users.full_name,
                            DATE_FORMAT(created_on,"%%a %%e %%b %%Y") created_on_vis 
                        FROM                             
                            notes, 
                            users
                        WHERE 
                            deleted_by IS NULL 
                        AND notes.created_by=users.id
                        AND (notes.for_location=%d OR notes.for_location IS NULL)
                        ORDER BY created_on DESC',$locationId);
        
        $data =  fetchArray($sql,__METHOD__);
        
        if(!empty($data))
            foreach($data as $row=>$record)
                $data[$row]['created_u_user'] = array_shift(explode(' ',$record['full_name']));
        
        return $data;                   
    } 
    public static function delete($id){
        $sql = sprintf('DELETE FROM notes WHERE id=%d',$id);
        query($sql,__METHOD__);
    }
    public static function store($content,$locationId='null'){
        $sql = sprintf('INSERT INTO notes (`created_by`,`created_on`,`content`,`for_location`)
                VALUES (%d,NOW(),"%s",%s)',User::getId(),addslashes($content),$locationId);

        query($sql,__METHOD__);                
    }                      
}