<?php
class Statistics{
    public static function getTopExitPagesLastXDays($numOfDays=7,$resultCount=10){
		$sql = 		'SELECT 
							COUNT(exitpage) quantity, 
							exitpage 
						FROM conversion 
						WHERE 
							DATE(start) > DATE_SUB(NOW(), INTERVAL '.$numOfDays.' DAY) 
						AND exitpage NOT LIKE "%statistics.html"
						GROUP BY exitpage ORDER BY quantity DESC LIMIT '.$resultCount;
		$out = fetchArray($sql,__METHOD__);
		return $out;
	}    
    public static function getLatestQueries(){
        $sql = sprintf('
            SELECT * 
            FROM 
                stats_search ss,
                stats_query sq
            WHERE
                ss.fk_query=sq.id
            ORDER BY ss.id DESC
            LIMIT 10');        
        return fetchArray($sql,__METHOD__);
    }

    public static function getMostPopularQueries(){
        $sql = sprintf('
            SELECT * 
            FROM 
                stats_query sq
            ORDER BY sq.hits DESC
            LIMIT 10');        
        return fetchArray($sql,__METHOD__);
    }
    public static function getLastGoogleQueries($itemsPP){
        
        $sql = "SELECT 
                        id,
                        ip,
                        referer_url,
                        start
                        FROM 
                        conversion 
                        WHERE 
                        (referer_url LIKE '%google.nl%' OR referer_url LIKE '%google.be%')
                        AND referer_url LIKE '%q=%'
                        ORDER BY id DESC LIMIT $itemsPP";
        $out = fetchArray($sql,__METHOD__);
        if(!empty($out)){
            foreach($out as $id=>$row){
                $url_parts = array();
                parse_str($row['referer_url'], $url_parts);                
                
                if($url_parts['q']){
                    $out[$id]['query'] = $url_parts['q'];
                }else if($url_parts['oq']){
                    $out[$id]['query'] = $url_parts['oq'];
                }else{
                    $out[$id]['query_alt'] = 'De query is door Google niet doorgegeven';                    
                }
                $out[$id]['other'] = $url_parts;
                
            }
        }
        
        return $out;
    }
    
}