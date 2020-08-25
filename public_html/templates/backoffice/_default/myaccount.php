<?php
class Myaccount{
    function  run($params){
        $params['rand']  = rand(0,9999999);
        if($params['uploadimg'])
            Image::storeUserImage(User::getId());
        
        $user = User::fromDb(User::getId());

        if($params['_do']=='changepass'){
            $params['changepass'] = false;
            if($user['pass']==$params['currentpass']){
                $params['changepass'] = true;
                User::newPassword(User::getId(),$params['newpass']);
            }
        }


        return array_merge($params,$user);
    }
}