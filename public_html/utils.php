<?php
function splitOverLines($string,$iMaxSentenceLength,$iMaxLineNum=2){            
    $aLine = array();
    $aSentence = array();
    $iCurrentLine = 0;
    $iLatestSpacePos = 0;
    $iCurrSentencePos = 0;
    $iForLoopCount = 0;
    
    for($i=0;$i<strlen($string);$i++){         
        $iForLoopCount++;
        if($iForLoopCount>100)
            break;
        
        $aLine[$i] = $string[$i];
                
        $iCurrSentencePos++;
        if($string[$i] == ' '){
            $iLatestSpacePos = $iCurrSentencePos;            
        }        
        $bBreakedOnSentenceEnd =false;
        if($iCurrSentencePos == $iMaxSentenceLength){            
            
            $aNewLine = array_slice($aLine, 0,$iLatestSpacePos);
            $aSentence[$iCurrentLine] = join('',$aNewLine);            

            $aLine = array();
            $iCurrentLine++;
            $i = $iLatestSpacePos-1;

            if($iCurrentLine == $iMaxLineNum){
                $bBreakedOnSentenceEnd= true;
                break;
            }
            $iCurrSentencePos = 0;
        }        
    } 
    if(!$bBreakedOnSentenceEnd){
        $aNewLine = array_slice($aLine, 0,$iLatestSpacePos);
        $aSentence[$iCurrentLine] = join('',$aNewLine);         
    }
    return join('<br>',$aSentence);
}
function makeRatingstartHtml($averageRating){
    $starsHtml = '';
    if($averageRating){                    
        for($i=0;$i<5;$i++){
            if($i<$averageRating){
                $starsHtml .= '<img src="/img/star-filled.png" alt="sterren" height="10" width="10" />';
                //$row['stars_html'][] = array('file'=>'/img/star-filled.png'); 
            }else{
                $starsHtml .= '<img src="/img/star-empty.png" alt="sterren" height="10" width="10" />';
                // $row['stars_html'][] = array('file'=>'/img/star-empty.png');
            }
        }
    }    
    if(empty($starsHtml)){
        $starsHtml = '<div style="width:50px;height:20px;;"></div>';
    }
    return $starsHtml;
}
function isBot() {
  if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']))
    return true;
  else
    return false;  
}
function removeDoubleDash($str){
    if(strpos($str,'--')){        
        $str = str_replace('--','-',$str);
        $str = removeDoubleDash($str);
    }
    return $str;            
}
function removeBom($str=""){
    if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
        $str=substr($str, 3);
    }
    return $str;
}
/*
 * Basic functions for all needs
 */
function stripSpecial($str){        
    $out = removeDoubleDash(preg_replace('/[^a-z0-9]+/','-',strtolower(trim($str))));
    return trim($out,'-');
    #$out = preg_replace('/-$/','',$out);
    #return $out;
}


function humanfilesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
/** 
 * Converts human readable file size (e.g. 10 MB, 200.20 GB) into bytes. 
 * 
 * @param string $str 
 * @return int the result is in bytes 
 * @author Svetoslav Marinov 
 * @author http://slavi.biz 
 */ 
function filesize2bytes($str) { 
	$str = str_replace('G','GB',$str);
    $bytes = 0; 
    $bytes_array = array( 
        'B' => 1, 
        'KB' => 1024, 
        'MB' => 1024 * 1024, 
        'GB' => 1024 * 1024 * 1024, 
        'TB' => 1024 * 1024 * 1024 * 1024, 
        'PB' => 1024 * 1024 * 1024 * 1024 * 1024, 
    ); 
    $bytes = floatval($str); 

    if (preg_match('#([KMGTP]?B)$#si', $str, $matches) && !empty($bytes_array[$matches[1]])) { 
        $bytes *= $bytes_array[$matches[1]]; 
    } 
    $bytes = intval(round($bytes, 2)); 
    return $bytes; 
} 
/*
 * Basic functions for all needs
 */
function number_format_array($array,$field,$decimals,$decPoint,$thousandSep){
    foreach($array as $id=>$row)           
        $array[$id][$field] = number_format($row[$field],$decimals,$decPoint,$thousandSep);
    return $array;
} 
/** 
 * Recursively remove dirs
 * @param type $dir
 * @return type 
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $dirscan = scandir($dir);
        foreach ($dirscan as $object) {
            if ($object != "." && $object != "..") {
                if (is_link($dir . "/" . $object)) {  # object is symlink
                    if (!unlink($dir . "/" . $object))
                        return FALSE;
                } elseif (is_dir($dir . "/" . $object)) {  # object is folder
                    if (!rrmdir($dir . "/" . $object))
                        return FALSE;
                } else {  # object is file
                    if (!unlink($dir . "/" . $object))
                        return FALSE;
                }
            }
        }
        reset($dirscan);
        if (!rmdir($dir))
            return FALSE;
        return TRUE;
    } else
        return FALSE;
}

/**
* Haal iemands laatste tweet op en sla voor 10 minuten op op schijf.
*/
function getLatestTweet($twitterAccount){
	$tweetfile = './tmp/latesttweet.txt';
	if(!file_exists($tweetfile) || (filemtime($tweetfile)+600)<time()){		
		if(!file_exists($tweetfile)){
			touch($tweetfile);
		}
        $url = sprintf('http://api.twitter.com/1/statuses/user_timeline/%s.json',$twitterAccount);        

        @$data = file_get_contents($url);
        $tweets = json_decode($data,true);
        $latesttweet = $tweets[0]['text'];                        
        $latesttweet = preg_replace('/http:\/\/(.+)/','<a target="_blank" href="$0">$0</a>',$latesttweet);	
		file_put_contents($tweetfile,$latesttweet);
	}
	return file_get_contents($tweetfile);
}
function array_columns($array,$columnCount){
    if(!is_array($array))
            return false;
    $size = ceil(count($array)/$columnCount);
    for($c=1;$c<=$columnCount;$c++){
        $out[$c]['data'] = array_splice($array,0,$size);        
    }
    return $out;  
}

/**
 * normalize()
 * Zet windows regeleinden om naar Unix regeleinden. 
 * @param mixed $s
 * @return
 */
function normalize($s) {
    // Normalize line endings
    // Convert all line-endings to UNIX format
    $s = str_replace("\r\n", "\n", $s);
    $s = str_replace("\r", "\n", $s);
    // Don't allow out-of-control blank lines
    $s = preg_replace("/\n{2,}/", "\n\n", $s);
    return $s;
}
function _d($data){
    pre_r($data);
}
function pre_r($data){
    echo "<pre>".print_r($data,true)."</pre>";

    $aBacktrace = debug_backtrace();
    foreach($aBacktrace as $aLine){
        echo "Called from ".$aLine['file'].':'.$aLine['line']."<br>";
    }

}
function getById($table,$id,$method){    
    return fetchRow(sprintf(sprintf('SELECT * FROM %s WHERE id=%d',$table,$id)),$method);
}
function quote($sString){
    Db::instance();
    $sOut = mysqli_real_escape_string(Db::instance()->dbh, $sString);

    return $sOut;
}
function store($table, $keyValArray, $data){        
    
    return DB::instance()->store($table,$keyValArray,$data);
}
function insert($table,$data){
    DB::instance()->insert($table,$data);    
}
function find($table,$where,$orderby=null){
    $orderByClause ='';
    if($orderby){
        $orderByClause = 'ORDER BY '.quote($orderby);
    }
    $sql = sprintf('SELECT * FROM %s WHERE %s %s',$table,$where,$orderByClause);
    return fetchArray($sql,__METHOD__);
}
function baseHost($hostFromOrder){
    // Er kunnen ook mailtjes vanuit de backoffice verstuurd worden, HTTP_HOST is dus lang niet altijd goed.
    if(!empty($hostFromOrder)){
        $out = $hostFromOrder;
    }else{
        $out = $_SERVER['HTTP_HOST'];    
    }          
    if(strpos($_SERVER['HTTP_HOST'],'nuicart')){
        $out = str_replace('.nl','.nuicart.nl',$out);
    }
    if(strpos($_SERVER['HTTP_HOST'],'nuidev')){
        $out = str_replace('.nl','.nuidev.nl',$out);
    }    
    return $out;                              
        
}
/**
* @param $php__FILE__  als je hier __FILE__ aan meegeeft dan kijkt de template parser alleen in de huidige map waar het php bestand staat.
* @param $siteType webshops|backoffice (null means autodetect)
* @param $customRoot if siteType == webshops, $customRoot can be set to the hostname of a webshop
*/
function parse($template, $data, $php__FILE__ = null, $siteType = null, $customRoot = null)
{
/*
    echo "Template: ".$template."<br>";
    echo "Php file: ".$php__FILE__."<br>";
    echo "Site type: ".$siteType."<br>";
    echo "Custom root: ".$customRoot."<br><br><br>";
*/
    $aFiles = array();

    if($php__FILE__ !== null){
        $sScript = dirname($_SERVER['SCRIPT_FILENAME']);
        $sTemplateDirAbsolutePath = dirname($php__FILE__) . '/' . $template . '.html';
        $sTemplateDirRelativePath = str_replace($sScript, '', $sTemplateDirAbsolutePath);
        $aFiles[] = $sTemplateDirRelativePath;
        $aFiles[] = './' . $sTemplateDirRelativePath;
    }

    $aSiteTypes = array();
    if($siteType)
    {
        $aSiteTypes[] = $siteType;
    }
    $aSiteTypes[] = Cfg::getSiteType();

    foreach($aSiteTypes as $sSiteType)
    {
        if(strpos($template, '_')){
            list($sFolder) = explode('_', $template);

            $aFiles[] = './templates/' . $sSiteType . '/' . Cfg::getCustomRoot() . '/' .$sFolder. '/' . $template . '/' . $template . '.html';
            $aFiles[] = './templates/' . $sSiteType . '/_default/' . $sFolder. '/' . $template. '/' . $template . '.html';

            $aFiles[] = './templates/' . $sSiteType . '/' . Cfg::getCustomRoot() . '/' .$sFolder. '/' . $template . '.html';
            $aFiles[] = './templates/' . $sSiteType . '/_default/' . $sFolder. '/' . $template . '.html';
        }

        $aFiles[] = './templates/' . $sSiteType . '/' . Cfg::getCustomRoot() . '/' . $template . '.html';
        $aFiles[] = './templates/' . $sSiteType . '/_default/' . $template . '.html';
    }

    echo __METHOD__;
    echo "<h1>FILES</h1>";
    echo "<pre>" . print_r($aFiles, true) . "</pre>";
    
    foreach($aFiles as $sFile){

        // echo 'Search for '. $sFile . "<br>";
        if(file_exists($sFile)){
            // echo 'Found '.$sFile."<br>";
            return Template::instance($sFile)->parse($data, true);
        }
    }

    return '';

}
function fetchRow($sql,$method,$db=null){
    $sql = sprintf('-- %s %s %s',$method,PHP_EOL,$sql);
    return Db::instance()->fetchOneRow($sql);
}
function fetchArray($sql,$method,$db=null)
{
    $sql = sprintf('-- %s %s %s',$method,PHP_EOL,$sql);
    return Db::instance()->fetchArray($sql);
}
function fetchVal($sql, $method){
    $sql = sprintf('-- %s %s %s',$method,PHP_EOL,$sql);
    $row =  Db::instance()->fetchOneRow($sql);
    
    if(is_array($row))
        return array_pop($row); 
}
function query($sql,$method,$db=null){
    $sql = sprintf('-- %s %s %s',$method,PHP_EOL,$sql);
    return Db::instance()->query($sql,$db);
}


function queryf(){
    $args = func_get_args();
    foreach($args as $id=>$arg)
        if($id!=0)
            $args[$id] = quote($arg);        
    $query = call_user_func_array('sprintf',$args);
    $sql = sprintf('-- %s %s %s',$method,PHP_EOL,$query);    
    
    return Db::instance()->query($sql,$db);
}
function redirect($url){
    header(sprintf('Location: %s',$url));
    exit();
}
function reloadParent(){
  print '<script type="text/javascript">parent.location.reload();</script>';
  exit();  
}
/*
 * @param $stripSlashesOrReplaceWith true=strip,false=dont strip, string = replace with
 */
function urlEncodeFieldsInArray($source,$fields,$prefix='encoded_',$stripSlashesOrReplaceWith=true){
    if(!is_array($fields)){
        $tmp = $fields;
        unset($fields);
        $fields[] = $tmp;
    }
    if(is_array($source))
        foreach($source as $id=>$row){
            foreach($fields as $field){
                if($stripSlashesOrReplaceWith){
                    if(is_string($stripSlashesOrReplaceWith)){                        
                        $row[$field] = str_replace('/',$stripSlashesOrReplaceWith,$row[$field]);
                    }else{                        
                        $row[$field] = str_replace('/','',$row[$field]);
                    }
                }    
                $source[$id][$prefix.$field] = urlencode(str_replace(' ','',$row[$field]));
            }
        }
    return $source;
}
function multiFormatEncode($val){
    $out['standard']    = $val;
    $out['uc_first']    = ucfirst($val);
    $out['upper']       = strtoupper($val);
    $out['lower']       = strtolower($val);
    $out['enc_lower']   = urlencode(strtolower($val));
    return $out;    
}
function readMore($string,$minLenght,$maxLenght){
    $string = nl2br(strip_tags($string));
    $length = 1;
    $breakOnLineEnding = false;
    $array = preg_split('//', $string, -1, PREG_SPLIT_NO_EMPTY);
    $outString = '';
    foreach($array as $index=>$char){
        $outString = $outString.$char;
        $length = $length+1;
        if($length>$minLenght)
            $breakOnLineEnding = true;
        if($breakOnLineEnding && $char == '.')
            return array('has_more'=>1,'short'=>$outString,'full'=>$string);
        if($length>=$maxLenght){            
            if(!preg_match('[0-9a-zA-Z]',$char))
                return array('has_more'=>1,'short'=>$outString,'full'=>$string);
            return array('has_more'=>1,'short'=>$outString.'...','full'=>$string);
        }
    }
    return array('has_more'=>0,'short'=>$outString,'full'=>$string);
}
function pageUrl($url,$pageNum){
    
    $url = preg_replace('/\.html$/','',$url);
    $url = preg_replace('/-p[0-9]+$/','',$url);    
    if($pageNum<=1){
        return $url.'.html';
    }
    return $url.'-p'.$pageNum.'.html';
}
function paginate($currPage,$resultCount,$itemsPPOverride=false,$gotoclassname='gotopage',$showpages=6){
    
    $itemsPP = (!$itemsPPOverride)?Cfg::get('items_pp'):$itemsPPOverride;
    
    if($itemsPP<$resultCount)
        $pages = ceil($resultCount/$itemsPP);

    if(!isset($pages)){
        $pages = 0;
    }

    if($pages > $showpages && $currPage>1){
        $output[] = sprintf('<a href="'.pageUrl($_SERVER['REQUEST_URI'],1).'" rel="1" class="paginate action %s">&lt;&lt;eerste</a>',$gotoclassname);
    }
    if($pages > $showpages && $currPage>1){
        $fastbackward = (($currPage-1)<1)?1:($currPage-1);
        $output[] = sprintf('<a href="'.pageUrl($_SERVER['REQUEST_URI'],$fastbackward).'" rel="%1$d" class="paginate action %2$s">&lt;vorige</a> ',$fastbackward,$gotoclassname);
    }
    for($c=1; $c<=$pages; $c++)
        if($c>($currPage-$showpages) && $c < ($currPage+$showpages))
            $output[] = sprintf('<a href="'.pageUrl($_SERVER['REQUEST_URI'],$c).'" rel="%1$d" class="paginate %3$s%2$s">%1$d</a> ',$c,($c==$currPage)?' active':'',$gotoclassname);
        
    $fastforward = (($currPage+1)>=$c)?$c:($currPage+1);
    
    if($pages > $showpages && $fastforward!=$c){
        $output[] = sprintf('<a href="'.pageUrl($_SERVER['REQUEST_URI'],$fastforward).'" rel="%1$d" class="paginate action %2$s">volgende&gt;</a> ',$fastforward,$gotoclassname);
    }
    if($pages > $showpages && $currPage<($c-1)){
        $output[] = sprintf('<a href="'.pageUrl($_SERVER['REQUEST_URI'],($c-1)).'" rel="%1$d" class="paginate action %2$s">laatste&gt;&gt;</a> ',($c-1),$gotoclassname);
    }    
    
    if(isset($output) && is_array($output)){
        return join(' ',$output);
    }
    return;
}
function paginatePrevNextOnly($currPage,$resultCount,$itemsPPOverride=false){
    $itemsPP = (!$itemsPPOverride)?Cfg::get('items_pp'):$itemsPPOverride;
             
    if($currPage>1){
        $prev = $currPage-1;        
        $output['prev'] = pageUrl($_SERVER['REQUEST_URI'],$prev);
    }        
    
    $pages = ceil($resultCount/$itemsPP);
        
    if($pages>$currPage){ 
        $output['next'] = pageUrl($_SERVER['REQUEST_URI'],$currPage+1);        
    }

    if(isset($output)){
        return $output;
    }
    return ;
}


/*
 * Split text in ongeveer even lange delen (met respect voor bestaande html tags)
 * @param string $text de tekst de opgesplitst moet worden.
 * @param array/int hoe de tekst gespleten moet worden, kan een integer (aantal stukken), of een array met procentuele waarden bevatten.
 */
function column_split($text,$parts){
    // Vervang html closing tags voor iets eenvoudig herkenbaars
    $text      = preg_replace('#<([\s]?)/([\s]?)([a-zA-Z0-9]+?)>#',' _close_$3_xclose_ ',$text);
    // Vervang html open tags voor iets eenvoudig herkenbaars
    $text      = preg_replace('#<([\s]?)([a-zA-Z0-9]+)([\s])?(((class|id|alt|title|href)=("|\')?[a-zA-Z0-9:/.]+(\'|"))?)?>#',' _open_$2|$4_xopen_ ',$text);
    $aText     = str_split($text);
    $length    = strlen($text);    
    if(!is_array($parts)){
        // Er is opgegeven in hoeveel (even lange) kollomen de tekst moet worden opgeknipt

        $partLenth = floor($length/$parts);
        for($c=0;$c<$parts;$c++)
            $partsSize[$c+1] = $partLenth;
        $sectLenth = $partsSize[1];
    }else{
        // Er zijn procentuele waarden mee gegeven voor de kollom hoogtes
        foreach($parts as $id =>$percentage){
            $sum = $sum + $percentage;
            $partsSize[$id+1] = ceil((strlen($text)/100)*$percentage);
        }
        if($sum<100){
            // Het eindtotaal komt niet uit op 100%, daarom voegen we een stuk toe aan de laaste kollom
            $percentage = $percentage + (100-$sum);            
            $partsSize[$id+1] = ceil((strlen($text)/100)*$percentage);
        }
        $sectLenth = $partsSize[1];
    }    
    $currPart  = 1;    
    $openTags  = array();
    
    for($i=0;$i<$length;$i++){
        if(in_array($aText[$i],array(' ','<','>'))){            
            if(strpos($currentWord,'_open_')!==false){
                $openCount = $openCount +1; 
                array_push($openTags,$currentWord);                
            }
            if(strpos($currentWord,'_close_')!==false){
                $openCount = $openCount -1;                                
                array_pop($openTags);                
            }            
            $currentWord = '';
        }        
        $currentWord .= $aText[$i];        
        $out[$currPart] .= $aText[$i];        
        if($i>=$sectLenth && in_array($aText[$i],array(' '))){            
            // Close tag toevoegen aan het eind van de huidige sectie/kollom.            
            if(is_array($openTags))              
                foreach(array_reverse($openTags) as $tag){
                    $tag = preg_replace('/\|[a-z=".]+(_xopen)/','$1',$tag);                                                        
                    $out[$currPart] .= str_replace('open','close',$tag);
                }                                                    
            // Open tags toevoegen aan het eind van de huidige sectie/kollom.
            if(is_array($openTags))                
                foreach($openTags as $tag)              
                    $out[1+$currPart] .= $tag;                                                                                           
            if(isset($partsSize[$currPart+1])){
                $currPart       = $currPart + 1;
                if(is_array($parts))
                    $sectLenth = $sectLenth+$partsSize[$currPart];
                else
                    $sectLenth = $sectLenth + $partsSize[$currPart];                
            }
        }                
    } 

    // HTML tags terugzetten
    if(is_array($out))
        foreach($out as $id=>$part){
            $out[$id] = preg_replace('/_open_(\|)?/','<',$out[$id]);
            $out[$id] = preg_replace('/_close_(\|)?/','</',$out[$id]);
            $out[$id] = preg_replace('/(\|)?_x(open|close)_/','>',$out[$id]);
            while(strpos($out[$id],'<br> <br>')){
                $out[$id] = str_replace('<br> <br>','<br>',$out[$id]);
            }
            while(strpos($out[$id],'</br> </br>')){
                $out[$id] = str_replace('</br> </br>','</br>',$out[$id]);
            }                        
        }
   
    #mail('info@nuicart.nl','testtagas',print_r($out,true));
    return $out;
}
