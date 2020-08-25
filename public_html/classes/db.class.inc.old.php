<?php
/**
 * Db
 * 
 * @author Anton Boutkam
 * @copyright Nui Boutkam
 * @version 2009
 * @access public
 */ 
class Db {
    public $dbh;
    public $database = 'geoip';
    private static $locale;
    static $instance ;
    public static function setLocale($langCode){
        $langCode   = strtolower($langCode);
        $locales = array(
            'es'=>'ca_ES',
            'de'=>'de_DE',
            'fr'=>'fr_FR',
            'pl'=>'pl_PL',
            'nl'=>'nl_NL',
            'no'=>'no_NO',
            'fi'=>'fi_FI',
            'en'=>'en_US'      
        );
        if(isset($locales[$langCode])){
            self::$locale = $locales[$langCode];
            return true;
        }
        
        // pak default
        self::$locale = 'en_US';
        return true;
    }

	/**
	* The constructor has been made pivate so we cannot create a regular instance of this class.
	* This is done so we know for user the class is only instantiated trough the instance method.
	*/
	
	private function __construct() {		
		$this->dbh = mysql_connect(
					Cfg::get('DB_HOST'), 
					Cfg::get('DB_USER'), 
					Cfg::get('DB_PASS')
				) or $this->error();
       
        if(self::$locale){
       		   $this->query(sprintf("SET lc_time_names = '%s';", self::$locale));
		}elseif(Cfg::get('MYSQL_LOCALE')){
				$this->query(sprintf("SET lc_time_names = '%s';", Cfg::get('MYSQL_LOCALE')));
		}		
        
		if ($this->dbh) 		                        
			if (mysql_query('set names utf8', $this->dbh))
				return mysql_select_db($this->database, $this->dbh);
							
		return false;
	}    
	/**
	* Creates an instance of it self but only once. 
	* If called again lateron from other parts of the script, this class will return the already available instance of it self.
	* @return Db - An instance of the db class.
	*/
	static function instance() {		
            if(self::$instance==null){
 		self::$instance = new Db();
            }
            return self::$instance;
  	} 

  	/**
  	 * Returns true when connected, false when not connected
  	 */
  	public function isConnected(){
  		return (is_resource($this->dbh));
  	}
  	public function getAll($tableName, $sort){
        $sQuery = "SELECT * FROM $tableName ORDER BY $sort";
  		return Db::instance()->fetchArray($sQuery);
  	}
	/**
	* Runs a query that normally would not return a result such as an update or delete.
	*
	*/
	public function query($sql, $database=null){         
       $time = microtime();    	   
	   if($database==null)
	       $database = self::getDbName();
	   	   
		if ($database and $this->database != $database) {
			$this->database = $database;
			mysql_select_db($this->database, $this->dbh);
		}
		
		$res = mysql_query($sql, $this->dbh);
		if(isset($_SERVER['IS_DEVEL'])  && mysql_error())
			echo mysql_error();
		
		if( !$res)
			$this->error($sql);
            
        if(Cfg::get('DISP_QUERY')){          
            echo nl2br($sql)."<br>";
            $time = microtime() - $time;
            echo "<strong>Query time: $time</strong><br />";
        }   
		return(mysql_insert_id($this->dbh));
	}

	public function affected_rows($res) {
		if ($res)
			return mysql_affected_rows($res);	
	}

	private function error($sql = null) {
		if ($this->dbh)
			$error = mysql_error($this->dbh);
		
		if($error){
            $msg .= mysql_error()."\n";
            $msg .= "-----------------------"."\n";
            $msg .= $sql."\n";
		    if($_SERVER['IS_DEVEL']){
                echo $msg;
            }else{                
                mail(Cfg::get('ERROR_MAILER'),'DB Error '.Cfg::get('DB_NAME'),$msg.print_r($_SERVER,true));
            }                      
		}
	}
    private static function getDbName(){
        return (isset($_SESSION['testmode']))?Cfg::get('DB_NAME_TEST'):Cfg::get('DB_NAME');
    }
	/**
	* Returns The resulting array from the database.
	* @sql string Te actual query.
	* @datbase string What databse should we use, default is geoip.
	* @return arra The resulting array from the database.
	*/	
	public function fetchArray($sql, $database=null){         
	   $_SESSION['query_counter']++;
        $time = microtime();        
        #echo nl2br($sql)."<br><br>";        	
		if(!$database)
			$database = self::getDbName();
		
		if ($database and $this->database != $database) {
			$this->database = $database;
			mysql_select_db($this->database, $this->dbh);
		}		
                //trigger_error("ASdfasdf");
		$res = mysql_query($sql, $this->dbh);

		if( !$res)
			return $this->error($sql);

		$result = array();
		while ($res and $row = mysql_fetch_array($res,MYSQL_ASSOC)) {
			  foreach($row as $colKey=>$colVal)
			  	$row[$colKey] = stripslashes($colVal);

			  $result[] = $row;
		}
        if(Cfg::get('DISP_QUERY')){
          
            // echo nl2br($sql)."<br>";
            $time = microtime() - $time;
            echo "<strong>Query time: $time</strong><br />";
        }
		return $result;
	}		
	/**
	* Same as fetch array but returns only the first row from the database.
	* @sql string Te actual query.
	* @datbase string What databse should we use, default is geoip.
	* @return Returns The resulting array from the database.
	*/	
	function fetchOneRow($sql, $database=null){
		$data = $this->fetchArray($sql, $database);

        if(isset($data[0])){
            return $data[0];
        }
        return null;
	}
 
    function store($table,$keyValArray,$data){
        if($keyValArray['id']!='new' && Db::instance()->keyExists($table,$keyValArray)){                 
            $res = Db::instance()->update($table,$data, $keyValArray);
            if(count($keyValArray)==1)
                return current($keyValArray);
            return $res;
        }else{            
            return Db::instance()->insert($table,$data);
        }
    }
    function keyExists($table,$keyValArray){
        foreach($keyValArray as $key=>$val){
            $where[] = sprintf('%s="%s"',$key,$val);
        }
        $result = Db::instance()->fetchOneRow($sql = sprintf('-- %s SELECT * FROM %s WHERE %s LIMIT 1',__METHOD__.PHP_EOL,$table,implode(' AND ',$where)), self::getDbName());    
        return $result;
    }
    
    function find($table, $keyValArr,$limit = null){
        $sql[]      = sprintf("-- %s",__METHOD__);
        $sql[]      = sprintf("SELECT * FROM %s", $table);
        $sql[]      = "WHERE ";
        foreach($keyValArr as $key=>$val){
            $flds[] = sprintf('%s="%s"',$key,addslashes($val));
        }
        $sql[]      = implode(" AND ",$flds);
        if($limit){
            $sql[]  = sprintf("LIMIT %d",$limit);
        }	
        $sql        = implode(PHP_EOL, $sql);

        $result     = $this->fetchArray($sql,$dbName);

        if($limit==1 && isset($result[0])){
            return $result[0];
        }

        return $result;   
    }
    /**
     * Automatic query generation
     */ 
    function insert($table,$dataArray,$dbName=null){
           $tableRows = $this->getTableCols($table,$dbName,true);

           if(is_array($tableRows)){
               foreach($dataArray as $colName=>$colData){   
                    if(isset($tableRows[$colName])){
                        
                        $from[] = '`'.$colName.'`';
                        // Some strict                         
                        if(strpos($tableRows[$colName],'float')===0){                            
                            $size   = str_replace(array('float','(',')'),'',$tableRows[$colName]);
                            $size   = explode(',',$size);
                            $tpl    = "%.{$size[1]}f";
                            $data[] = sprintf($tpl,$colData);//.' '.$size.' '.$tpl ; 
                        }elseif(strpos($tableRows[$colName],'int')===0){
                            $data[] = sprintf("%d",$colData);//.' '.$tableRows[$colName]; 
                        }elseif(strpos($tableRows[$colName],'timestamp')===0){
                            if(empty($colData)){
                                $data[] = "NULL";
                            }else{
                                $data[] = "'".addslashes((string)$colData)."'";//.' '.$tableRows[$colName];//."' /* $colName $tableRows[$colName] */"; //''
                            }    
                        }else{                            
                            $data[] = "'".addslashes((string)$colData)."'";//.' '.$tableRows[$colName];//."' /* $colName $tableRows[$colName] */"; //''    
                        }
                        
                    }
               }
               $sql = sprintf("-- %s\nINSERT IGNORE INTO `%s`
                                (%s)
                                VALUE
                                (%s)", 
                                __METHOD__,
                                addslashes($table),
                                implode(",".PHP_EOL,$from),
                                implode(",".PHP_EOL."",$data));                     

                return $this->query($sql,$dbName);                   
            }        
    }
    /**
     * Db::update()
     * Automatic query generation
     * 
     * @param mixed $tableName
     * @param mixed $data key/value pairs
     * @param mixed $where key/value pairs
     * @param mixed $dbName
     * @return void
     */
    public function update($tableName,$data,$whereFields, $dbName=null){        
        
        $tableRows = $this->getTableCols($tableName,$dbName);
                
        if(is_array($data) && is_array($whereFields)){
            $start = sprintf("-- %s
                            UPDATE IGNORE %s
                            SET ",
                            __METHOD__,
                            $tableName);
            foreach($tableRows as $fieldName){
                if(isset($data[$fieldName])){
                    $fld[] = sprintf('`%s`="%s"',$fieldName,quote($data[$fieldName]));
                }
            }
            foreach($whereFields as $field=>$val){
                $where[] = sprintf('`%s`="%s"',$field,$val);
            }
            $sql[]  = $start;
            $sql[]  = implode(",".PHP_EOL,$fld);
            $sql[]  = " WHERE ";
            $sql[]  = implode(" AND ".PHP_EOL,$where);
            $sql    = implode("",$sql);        
            
            // echo nl2br($sql);
            
            return $this->query($sql,$dbName);
        }
        
    }    
    public function getTableCols($table,$dbName=null,$includeDatatype=false){
        $sql    = sprintf("DESC %s ",addslashes($table));
        $data   = $this->fetchArray($sql,$dbName);
                
        foreach($data as $field){
            if(!$includeDatatype){
                $cols[] = $field['Field'];
            }else{
                $cols[$field['Field']] = $field['Type'];
            }
        }
        return $cols;
    }

}