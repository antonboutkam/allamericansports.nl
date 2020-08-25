<?php
function sessionDir(){        	
    return dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/session/';                    
}
$isLocked = false;
function sess_open($path, $name) {
    return true;    
}
function sess_read($sessionId) { 
    if(file_exists(sessionDir().$sessionId)){
        $fileContents = file_get_contents(sessionDir().$sessionId);
        $data = unserialize($fileContents);
        return $data['session_data'];
    }
}
function sess_write($sessionId, $data) { 	
    $save['session_data']       = $data;
    $save['server']['agent']    = getBrowser($_SERVER['HTTP_USER_AGENT']);
    $save['server']['ip']       = $_SERVER['SERVER_ADDR'];
    $save['server']['uri']      = $_SERVER['REQUEST_URI'];
	$save['server']['host']     = $_SERVER['HTTP_HOST'];

    touch(sessionDir().$sessionId);
    file_put_contents(sessionDir().$sessionId,serialize($save));          
}
function sess_close() {  
    $sessionId = session_id();	
     //perform some action here
        return true; // altijd true returnen voor succs.
}
function sess_destroy($sessionId) {	    
	unlink(sessionDir().$sessionId);
	return true;
}
function sess_garbage($lifetime) {	
	return true;
}
function sessionClean(){    
    $files  = glob(sessionDir().'*'); 
    foreach ($files as $file){
        $filetime = filemtime($file);
        $timenow  = time();
        $answer = $timenow - $filetime;
            if ($answer > 900){ #900 =  15 minuten.
                unlink('/'.$file) ;
            }  
    }  
}

function sessionTimeLastAction($file){
    $filetime = filemtime($file);
    $timenow  = time();
    $answer = $timenow - $filetime;
    if ($answer > 60){
        $minutes = $answer / 60;
        return round($minutes,0). ' minuten';
    }
    return $answer .' seconden';
}


function sessionCount(){
     $files  = count(glob(sessionDir().'*'));
     return $files;
}
function sessionInfo(){
     $files  = glob(sessionDir().'*');
	 $agentmap = array('firefox'=>'ff','chrome'=>'chrome','ie'=>'ie');
	 
     foreach($files as $file){
         $session 							= unserialize(file_get_contents($file));
		 $agent								= strtolower(preg_replace('/[^a-zA-Z]+/','',$session['server']['agent']));
		 $session['server']['agent_short'] 	= $agent;
		 if(strlen($session['server']['uri'])>33){
			$session['server']['uri_short'] 	= substr($session['server']['uri'],0,30).'...';
		 }else{
			$session['server']['uri_short'] 	= $session['server']['uri'];
		 }
         $session['server']['last_seen'] 	= sessionTimeLastAction($file);
         $out[] = $session['server'];
     }
     return $out;
}
function getBrowser($agent){
    $agent = strtolower($agent);
    foreach(array('firefox','chrome','safari','msie 10.0','msie 9.0','msie 8.0','msie 7.0') as $browser){
        if(strpos($agent, $browser)){
            if ($browser == 'msie 10.0'){
                $browser = 'IE 10.0';
            }
            if ($browser == 'msie 9.0'){
                $browser = 'IE 9.0';
            }
            if ($browser == 'msie 8.0'){
                $browser = 'IE 8.0';
            }
            if ($browser == 'msie 7.0'){
                $browser = 'IE 7.0';
            }
            
            return ucfirst($browser);
        }
    }    
    return 'unknown';            
}

session_set_save_handler("sess_open", "sess_close", "sess_read", "sess_write", "sess_destroy", "sess_garbage");
