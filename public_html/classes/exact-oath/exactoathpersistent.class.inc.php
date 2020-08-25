<?php
class ExactOathPersistent{

    static function store($sAccessToken, $sExpiresIn, $sRefreshToken){
        self::clearTokenSet();

        $sQuery = "INSERT INTO exact_oath (`access_token`, `expires_in`, `refresh_token`)
                    VALUE('$sAccessToken', '$sExpiresIn', '$sRefreshToken')";

        query($sQuery, __METHOD__);
    }
    static function clearTokenSet(){
        $sQuery = "DELETE FROM exact_oath ";
        query($sQuery, __METHOD__);
    }
    static function getToken(){
        $sQuery = "SELECT * FROM exact_oath";
        $aRow = fetchRow($sQuery, __METHOD__);
/*
        if($aRow['expires_in'] < time())
        {
            self::clearTokenSet();
            return null;
        }
*/
        return $aRow;
    }
}