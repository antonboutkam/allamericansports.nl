<?php

class Edit_note{
    function  run($params){
        if($params['_do']=='store'){
            $id = NotesDao::store($params['note']);
        }            

        return $params;
    }
}