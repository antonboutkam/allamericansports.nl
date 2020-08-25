<?php
class Users_view{
    function  run($params){
        $user = User::fromDb($params['id']);
        return array_merge($params,$user);
    }
}