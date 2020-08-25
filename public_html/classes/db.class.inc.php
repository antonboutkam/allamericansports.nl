<?php
/**
 * Db
 *
 * Standaard database abstractieklasse  
 * 
 * @package SharedClasses     
 * @author Anton Boutkam
 * @version 2014
 * @access public
 */ 
class Db {
    public $dbh;
    public $database = 'allamericansports';
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
        self::$locale = 'nl_NL';        
        return true;
    }

	/**
	* The constructor has been made pivate so we cannot create a regular instance of this class.
	* This is done so we know for user the class is only instantiated trough the instance method.
	*/
	
	private function __construct() {
        $this->database = Cfg::get('DB_NAME');
        $this->dbh = mysqli_connect(Cfg::get('DB_HOST'),Cfg::get('DB_USER'),Cfg::get('DB_PASS'),Cfg::get('DB_NAME')) or $this->error();

        if(!self::$locale){
            self::$locale = Cfg::get('MYSQL_LOCALE');       		   
		}
		$sSetEncoding = sprintf("SET lc_time_names = '%s';", self::$locale);


        $this->query($sSetEncoding);
        $this->query('SET CHARSET utf8');

		return false;
	}    
	/**
	* Creates an instance of it self but only once. 
	* If called again lateron from other parts of the script, this class will return the already available instance of it self.
	* @return Db instance of the db class.
	*/
	static function instance() {		
        if(self::$instance==null){
 		    self::$instance = new Db();
        }
        return self::$instance;
  	} 
    public static function dbstr($str){
        return mysqli_real_escape_string(self::instance()->dbh,$str);
    }
  	/**
  	 * Returns true when connected, false when not connected
  	 */
  	public function isConnected(){
  		return (is_resource($this->dbh));
  	}
  	public function getAll($tableName, $sort){
  		return Db::instance()->fetchArray(sprintf("SELECT * FROM %s ORDER BY %s", $tableName,$sort));
  	}
	/**
	* Runs a query that normally would not return a result such as an update or delete.
	*
	*/
	public function query($sql, $database=null){
        
	   if($database==null)
	       $database = self::getDbName();
	   	   
		if (is_null($database)==false && $database != "" && $this->database != $database) {
			$this->database = $database;
			mysqli_select_db($this->dbh,$this->database);
		}
		
		$res = mysqli_query($this->dbh,$sql);

        if(mysqli_error($this->dbh)){
            $aMessage = array();
            $aMessage[] = "There was an error in the query:";
            $aMessage[] = mysqli_error($this->dbh);
            $aMessage[] = '----- query ---';
            $aMessage[] = $sql;
            $aDebugBacktrace = debug_backtrace();
            $aMessage[] = '----- backtrace ---';
            foreach($aDebugBacktrace as $aDebugItem){
                $aMessage[] = $aDebugItem['file'].':'.$aDebugItem['line'];
            }
            $sMessage = join(PHP_EOL, $aMessage);

            if(isset($_SERVER['IS_DEVEL']) || true){
                echo $sMessage;
                mail('anton@nui-boutkam.nl', 'ALLAMERICANSPORTS ERROR', $sMessage);
                throw new DbException(join(PHP_EOL, $sMessage));
            }else{
                $sMessage = join(PHP_EOL, $aMessage);
                mail('anton@nui-boutkam.nl', 'ALLAMERICANSPORTS ERROR', $sMessage);
                throw new DbException("There was an error in the query");
            }
        }
		return(mysqli_insert_id($this->dbh));
	}

	public function affected_rows($res) {
        if ($res)
            return mysqli_affected_rows($res);
	}

	private function error($sql = null) {
        if ($this->dbh && isset($_SERVER['IS_DEVEL']) ||  $_SERVER['SHOW_MYSQL_ERRORS']){
            $error = mysqli_error($this->dbh);
            echo mysqli_error($this->dbh);
            echo "|-----------------------";
            echo $sql;
            throw new DbException('Mysql Error, zie hierboven');
        }
	}
    private static function getDbName(){
        return (isset($_SESSION['testmode']) && $_SESSION['testmode'])?Cfg::get('DB_NAME_TEST'):Cfg::get('DB_NAME');
    }
	/**
	* Returns The resulting array from the database.
	* @sql string Te actual query.
	* @datbase string What databse should we use, default is geoip.
	* @return array|null
	*/	
	public function fetchArray($sql, $database=null)
    {
        if(!isset($_SESSION['query_count'])){
            $_SESSION['query_count'] = 0;
        }
        if(isset($_SERVER['IS_DEVEL'])){
            $_SESSION['query_count']++;
           // echo "<h1>Query ".$_SESSION['query_count']."</h1>";
           // echo nl2br($sql)."<Br><Br>";
        }
		if(!$database)
			$database = self::getDbName();

		if ($database and $this->database != $database) {
			$this->database = $database;
			mysqli_select_db($this->dbh,$this->database);
		}

		$res = mysqli_query($this->dbh, $sql);

		if( !$res){
			return $this->error($sql);
        }
		$result = array();
		while ($res and $row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			  foreach($row as $colKey=>$colVal){
			  	$row[$colKey] = stripslashes($colVal);
              }
			  $result[] = $row;
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

        if(!isset($data[0])){
            return null;
        }
        return $data[0];
	}
 
    function store($table,$keyValArray,$data){
        if($keyValArray['id']!='new' && Db::instance()->keyExists($table,$keyValArray)){                            
            $res = Db::instance()->update($table,$data, $keyValArray);
            if(count($keyValArray)==1){
                return current($keyValArray);
            }
            return $res;
        }else{
            return Db::instance()->insert($table,$data);
        }
        
    }
    function keyExists($table,$keyValArray){
        foreach($keyValArray as $key=>$val){
            $where[] = sprintf('%s="%s"',$key,$val);
        }
        $sql = sprintf('-- %s 
                        SELECT * FROM %s WHERE %s LIMIT 1',__METHOD__.PHP_EOL,$table,implode(' AND ',$where));
        #echo nl2br($sql)."<br><br>";
        $result = Db::instance()->fetchOneRow($sql, self::getDbName());    
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

        $result     = $this->fetchArray($sql);

        if($limit==1 && isset($result[0])){
            return $result[0];
        }

        return $result;   
    }
    /**
     * Automatic query generation
     */ 
    function insert($table,$dataArray,$dbName=null){
       $tableRows = $this->getTableCols($table,$dbName);       
       $dataTypesTmp = $this->getTableCols($table,$dbName,'TypeOnly');
       
       $dataTypes = array();
       if(!empty($tableRows)){
            foreach($tableRows as $id=>$fieldName){
                $dataTypes[$fieldName] = $dataTypesTmp[$id];                
            }
       } 

       if(is_array($tableRows)){
           foreach($dataArray as $colName=>$colData){                   
                if(in_array($colName,$tableRows)){
                    $from[] = '`'.$colName.'`';
                    // echo $dataTypes[$colName]." - ".$colName." - ".$colData."<Br>";
                    if($colData || $colData==='0'){
                        if(strtolower(trim($colData))=='now()'){
                            $data[] = "NOW()";
                        }else if($dataTypes[$colName] == 'float'){
                            if(is_numeric($colData)){
                                $data[] = $colData;
                            }else{
                                $data[] = 0;
                            }
                        }else if($dataTypes[$colName] == 'date' && self::$locale == 'nl_NL' && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{4})/',$colData,$matches)){
                            $date =  "'".$matches['3'].'-'.$matches['2'].'-'.$matches['1']."'";;                            
                            $data[] = $date;
                        }else if($dataTypes[$colName] == 'date' && self::$locale == 'nl_NL' && preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$colData,$matches)){                                                        
                            $date =  "'".$matches['1'].'-'.$matches['2'].'-'.$matches['3']."'";;                            
                            $data[] = $date;
                        }else if($dataTypes[$colName] == 'timestamp' && self::$locale == 'nl_NL' && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{4})/',$colData,$matches)){
                            $date =  "'".$matches['3'].'-'.$matches['2'].'-'.$matches['1']."'";;                            
                            $data[] = $date;
                        }else if($dataTypes[$colName] == 'datetime' && self::$locale == 'nl_NL' && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{4})/',$colData,$matches)){
                            $date =  "'".$matches['1'].'-'.$matches['3'].'-'.$matches['2']."'";;                            
                            $data[] = $date;
                        }else if(strtolower(trim($colData))=='null' || strtolower(trim($colData))=="'null'" ){
                            $data[] = "NULL";
                        }else{
                            $data[] = "'".quote($colData)."'";
                        }
                    }else{   
                        if($colData === 0 && $dataTypes[$colName] == 'float'){ //@todo integer moe hier ooit aan toegevoegd worden
                            $data[] = '0';    
                        }else if($dataTypes[$colName] == 'timestamp') {
                            $data[] = 'NULL';
                        }else if($dataTypes[$colName] == 'datetime') {
                            $data[] = 'NULL';
                        }else if($dataTypes[$colName] == 'tinyint') {
                            $data[] = 0;
                        }else if($dataTypes[$colName] == 'float') {
                            $data[] = 0.000;
                        }else {
                            $data[] = '\' \'';
                        }                        
                    }
                }
            }

            $sql = sprintf("-- %s
                            INSERT INTO `%s`
                            (%s)
                            VALUE
                            (%s)", 
                            __METHOD__,
                            addslashes($table),
                            implode(",".PHP_EOL,$from),
                            implode(",".PHP_EOL." ",$data)); 

            $rand = rand(0,9999);

            $this->query($sql,$dbName);                                                                                                   
            return mysqli_insert_id($this->dbh);
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
        $dataTypes = $this->getTableCols($tableName,$dbName,'TypeOnly');
        
        if(is_array($data) && is_array($whereFields)){
            $start = sprintf("-- %s
                            UPDATE IGNORE %s
                            SET ",
                            __METHOD__,
                            $tableName);
             
            foreach($tableRows as $id => $fieldName){
                if(isset($data[$fieldName])){
                    if(($dataTypes[$id] == 'date' || $dataTypes[$id] == 'timestamp') && self::$locale == 'nl_NL' && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{4})/',$data[$fieldName],$matches)){                    
                        $data[$fieldName] = $matches['3'].'-'.$matches['2'].'-'.$matches['1'];                                                     
                    }
                }
                
                if(isset($data[$fieldName])){                                   
                    if(strtolower($data[$fieldName])=='null'){
                        $fld[] = sprintf('`%s` = NULL',$fieldName);
                    }elseif(strtolower($data[$fieldName])=='now()'){
                        $fld[] = sprintf('`%s`= now()',$fieldName,addslashes($data[$fieldName]));
                    }elseif(isset($data[$fieldName])){
                        $fld[] = sprintf('`%s`="%s"',$fieldName,addslashes($data[$fieldName]));
                    }
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

            return $this->query($sql,$dbName);
        }
        
    }    
    /**
     * Db::getTableCols()
     * 
     * @param mixed $table
     * @param mixed $dbName
     * @param string $outField [Field|TypeOnly] (TypeOnly = datatype)
     * @return
     */
    public function getTableCols($table,$dbName=null,$outField='Field'){
        $sql    = sprintf("DESC %s ",addslashes($table));
		
        $data   = $this->fetchArray($sql,$dbName);
		
        foreach($data as $field){
            if($outField == 'TypeOnly'){
                $cols[] = preg_replace('/\([0-9a-zA-Z \',]+\)/','',$field['Type']);
            }else{
                $cols[] = $field['Field'];
            }
        }
        
        return $cols;
    }
    

}