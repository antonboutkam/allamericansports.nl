<?php
class ExactHandleOath{

    static function handle($sRequestUri){

        // When we first come in, code is not set.
        if(strpos($sRequestUri, 'code')){
            $sRequestUri = $_SESSION['oauth_request_uri'];
        }
        // So we register the current url in a session
        $_SESSION['oauth_request_uri'] = $sRequestUri;

       // ExactOathPersistent::store($_SESSION['connection_parameters']['accessToken'], $_SESSION['connection_parameters']['expiresIn'], $_SESSION['connection_parameters']['refreshToken']);

        // Now we try to get our previously stored token.
        // This method looks in the databasee for the token set.
        // The tokenset consists of access_token, expires_in and refresh_token
        $aTokenSet = ExactOathPersistent::getToken();
/*
        // We assume the user is not authenticated
        $bIsAuthenticated = false;

        // If code isset, the user just got back from Exact.
        if(isset($_GET['code'])){
            $bIsAuthenticated = true;
        }

        // Code is not set but we do have a tokenset, this means we are authenticated .
        if(!empty($aTokenSet)) {
            $bIsAuthenticated = true;
        }
        */
        $oExactConfig = new ExactConfig(Cfg::get('EXACT_CLIENT_ID'), Cfg::get('EXACT_CLIENT_SECRET'), Cfg::get('EXACT_CLIENT_COUNTRY'), Cfg::get('EXACT_DIVISION'), $sRequestUri);
        $oExactApi = new ExactApi($oExactConfig, $aTokenSet['refresh_token']);
        return $oExactApi;
       /*
       if(!$bIsAuthenticated){
            // If we are not authenticated, this means we should authenticate now.
            // Sending the client to Exact
            $oExactAuthenticate = new ExactAuthenticate($oExactConfig);
            redirect($oExactAuthenticate->getAuthenticationUrl());
            exit();
       }

       */
       //return $oExactApi;exit("stranded here, the is $bIsAuthenticated");

        /*

        if(isset($_GET['code']) && empty($aTokenSet)) {

            // Nieuw token aanmaken
            Log::message('exact_token', 'Getting Exact token for the first time during this session', __METHOD__);
            $oExactApi->getOAuthClient()->setRedirectUri($oExactConfig->getReturnUrl());

            $oExactApi->getAccessToken()

            $aTokenResult = $oExactApi->getOAuthClient()->getAccessToken($_GET['code']);
            if(!empty($aTokenResult)){
                $oExactApi->setRefreshToken($aTokenResult['refresh_token']);
                $oExactApi->setExpiresIn($aTokenResult['expires_in']);
                $oExactApi->setAccessToken($aTokenResult['access_token']);
                ExactOathPersistent::store($oExactApi->getAccessToken(), $oExactApi->getExpiresIn(), $oExactApi->getRefreshToken());
            }


            Log::message('exact_token', 'Got accessToken: '.$oExactApi->getAccessToken(), __METHOD__);
            Log::message('exact_token', 'Got refreshToken: '.$oExactApi->getRefreshToken(), __METHOD__);
            Log::message('exact_token', 'Got Expires in : '.$oExactApi->getExpiresIn(), __METHOD__);

         }
         /*
         else{
            // Huidig token refreshen?

            if(empty($aTokenSet)){
                ExactOathPersistent::clearTokenSet();
                exit('Something went wrong, we have cleared the Exact online session, please hit F5 or reload the page to try again. If this happens often, contact the administrator.');
            }

            $oExactApi->setRefreshToken($aTokenSet['refresh_token']);
            $oExactApi->setExpiresIn($aTokenSet['expires_in'], false);
            $oExactApi->setAccessToken($aTokenSet['access_token']);

            Log::message('exact_token', 'Was already connected, regenerate accesstoken if needed.', __METHOD__);
            Log::message('exact_token', 'My accessToken: '.$aTokenSet['access_token'], __METHOD__);
            Log::message('exact_token', 'My refreshToken: '.$aTokenSet['refresh_token'], __METHOD__);
            Log::message('exact_token', 'Expires in : '.$aTokenSet['expires_in'], __METHOD__);
        }
        */
        return $oExactApi;

    }


}
