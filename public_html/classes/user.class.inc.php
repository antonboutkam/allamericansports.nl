<?php
class User{
    public static function setSetting($user_id,$setting,$value){
        $user_id = (int)$user_id;
        $setting = quote($setting);

        $data   = array('fk_user'=>$user_id,'setting'=> $setting,'value'=>$value);

        $sQuery = "SELECT id FROM user_setting WHERE fk_user = $user_id AND setting='$setting'";

        $iId = fetchVal($sQuery, __METHOD__);

        $keyval = array('id' => $iId);

        DB::instance()->store('user_setting', $keyval, $data);
    }
   public static function getSetting($user_id,$setting){
        $data = fetchVal(sprintf('SELECT value FROM `user_setting` wss                                  
                                  WHERE wss.fk_user=%d AND wss.setting="%s"',$user_id,$setting),__METHOD__);					  
		return $data;
    }
    public static function setAutologin(){
        if($user = self::getCurent()){
            $key = md5('sec' . time() . 'sys');
            self::store(array('remember_id'=>$key),$user['id']);
            setcookie('auto',$key,time()+604800,'/');
        }
    }
    public static function resetByEmail($email){        
        $user       =   self::getByEmail($email);
        if($user['id']){
            $user['pass']   = substr(md5(time()+$user['id']),10,6);
            self::newPassword($user['id'], $user['pass']);
            $html           = parse('mail/passreset',$user);

            $host = str_replace("_",".",$_SERVER['HTTP_HOST']);
            
            Mailer::sendMail('no-reply@'.$host,$user['email'],$user['full_name'],"password reset",$html);
        }else
            return "email_not_found";                
    }
    public static function newPassword($userId,$newpass){
        query(sprintf('UPDATE users SET pass="%s" WHERE id=%d',md5($newpass),$userId),__METHOD__);
    }
    public static function store($user,$id){
        return Db::instance()->store('users',array('id'=>$id),$user);        
    } 
    public static function autoLogin($remember_id){
        $row =  fetchRow(sprintf('SELECT * FROM users WHERE remember_id="%s"',quote($remember_id)),__METHOD__);
        if(!empty($row))
            self::login($row,true);
    }
    public static function login($params, $skipmd5 = false){

        $_SESSION['testmode'] = (isset($params['testmode']) && $params['testmode']==1)?1:0;
        if(!$skipmd5){
            $params['pass'] = md5($params['pass']);
        }

        $sPass = $params['pass'];
        $sEmail = quote($params['email']);

        $sQuery = "SELECT * FROM users WHERE email='$sEmail' AND pass='$sPass'  AND is_deleted=0 AND status=1";

        $data = fetchRow($sQuery, __METHOD__);

        if(is_array($data)){
            query($sql = sprintf('INSERT INTO authentication_log (user_id, ip) VALUE(%s,"%s")',
                        $data['id'],$_SERVER['REMOTE_ADDR']),__METHOD__);
            $_SESSION['member']             = $data;
            $_SESSION['member']['location'] = $params['location'];
            return $_SESSION['member']; 
        }                                    
    }
    public static function getFirstname(){
        $exploded = explode(' ',$_SESSION['member']['full_name']);                
        return array_shift($exploded);   
    }
    public static function fromDb($userId){
        return fetchRow(sprintf('SELECT u.*, DATE_FORMAT(birthdate,"%%e %%M") birthdate FROM users u WHERE id=%d',$userId),__METHOD__);
    }
    public static function getCurent(){
        if(isset($_SESSION['member'])){
            return $_SESSION['member'];
        }
        return null;

    }
    public static function getLevel(){
        if(isset($_SESSION['member'])){
            return $_SESSION['member']['level'];
        }
        return false;

    }
    public static function getId(){
        if(isset($_SESSION['member'])){
            return $_SESSION['member']['id'];
        }
    }
    public static function getLocaton(){
        if(isset($_SESSION['member'])){
            return $_SESSION['member']['location'];
        }
    }
    public static function setLocaton($id){ 
       $_SESSION['member']['location'] =$id;        
    }
    public static function getWindowState(){
        if(!empty($_SESSION['member']['windowstate']))
            return $_SESSION['member']['windowstate'];   
        return 'small';  
    }
    public static function getLocationName(){
        if(isset($_SESSION['member']['location'])){
            return Location::getName($_SESSION['member']['location']);
        }
    }
    public static function setWindowState($windowstate){
        $_SESSION['member']['windowstate'] = $windowstate;
        query($sql = sprintf('UPDATE users SET windowstate="%s" WHERE id=%d',$windowstate,$_SESSION['member']['id']),__METHOD__);
    }	    			
    public static function isMember(){
        if(isset($_SESSION['member'])){
            return $_SESSION['member'];
        }
        return false;
    }
    public static function getAll($sort=null,$incDeleted=false){
        if(!$incDeleted){
                $where = 'WHERE is_deleted=0';
        }
        if($sort)
            $sort = sprintf("ORDER BY %s",$sort);
            
        $result['data'] = fetchArray(sprintf('SELECT SQL_CALC_FOUND_ROWS * 
                                       FROM users 
                                       %s 
                                       %s',
                                       $where,$sort),__METHOD__);
        $result['rowcount'] = fetchVal('SELECT FOUND_ROWS() AS `found_rows`',__METHOD__);
        return $result;             
    }
    public static function getById($id){        
        return fetchRow(sprintf('SELECT * FROM users WHERE id=%d',$id),__METHOD__);
    }
    public static function getByEmail($email){        
        return fetchRow(sprintf('SELECT * FROM users WHERE email="%s"',$email),__METHOD__);
    }    
    public static function delete($id){
        return query($sql = sprintf('UPDATE users SET is_deleted=1 WHERE id=%d',$id),__METHOD__);
    }
}