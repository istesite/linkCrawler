<?
   class DatabaseClass {
		
	protected	$dbUser         = '';
	protected	$dbPass         = '';
	protected	$dbHost         = '';
	protected	$dbName         = '';  
	protected	$queryResult	= NULL;
	
	private  	$lastError		= NULL;
	private  	$lastQuery		= NULL;	
	private		$dbo			= NULL; 
	
		function __construct($dbHost, $dbUser, $dbPass, $dbName)
		{
			$this->dbHost = $dbHost;
			$this->dbUser = $dbUser;
			$this->dbPass = $dbPass;
			$this->dbName = $dbName;
			
			$this->connect();
			//return $this->dbo;
		}

      
      private function connect(){  //veritabanı bağlanntısı oluşturur.
         $this->conn = mysql_connect($this->dbHost, $this->dbUser, $this->dbPass)
         or die("Veritabanımızda meydana gelen bir sorun yüzünden geçici bir süreliğine hizmet verememekteyiz");
         
         $this->setCharCollation();
         
         $select_db = @mysql_select_db($this->dbName) or die("Veritabanı seçilemedi");
      }
		
		
		function setCharCollation($names='utf8', $char='utf8', $collation='utf8_general_ci'){
			@mysql_query("SET NAMES 'utf8'");
         @mysql_query("SET CHARACTER SET 'utf8'"); //dil secenekleri
         @mysql_query("SET COLLATION_CONNECTION = 'utf8_general_ci'");
		}
           
           
		function query($sql) {//standart sorguların calistirildigi fonksiyon
			$tur=strtolower(substr($sql,0,3));
			switch ($tur)
			{
				case "sel":
					return mysql_query($sql, $this->conn) ;
				break;
				
				case "ins":
					return mysql_unbuffered_query($sql, $this->conn) ;
				break;
				
				case "upd":
					return mysql_unbuffered_query($sql, $this->conn) ;
				break;
				
				case "del":
					return mysql_unbuffered_query($sql,$this->conn) ;
				break;
				
				default:
					return mysql_query($sql,$this->conn) ;
				break;
			}
			unset($tur);
		}
         
         
		function fetchArray($sql){
			$resultsx=array();
			$sqlQuery = $this->query($sql);
			while( $rows = $this->fetch_array( $sqlQuery ) )
			{
				$resultsx[] = $rows;
			}
			
			if( !is_array($resultsx) )
				return false;
			else
				return $resultsx;
			
			$this->close();
		}
			
			
		function fetch_array($result, $type = MYSQL_BOTH){   //sorgu sonucunun alınmasını sağlar  #  $degisken['alanadi'] veya $degisken[1] gibi kullanılabilir.
			return mysql_fetch_array($result);
		}
		
		
		function fetch_object($result){  //sorgu sonucunun obje olarak verilmesini sağlar  #  $degisken->alanadi olarak kullanilir.
			return mysql_fetch_object($result);
		}
		  
		  
		function fetch_assoc($result){   //sorgu sonucunun alınmasını sağlar  #  $degisken['alanadi'] olarak kullanılır. fetch_array dan daha hızlı çalışır.
			return mysql_fetch_assoc($result);
		}
		  
		  
		function num_rows($result){   //sorgu sonucu dönen değerlerin sayısını verir
			return mysql_num_rows($result);
		}
		  
		  
		function affected_rows(){  //insert, delete, update sorgularını sonucu etkilenen kayıt sayısını verir.
			return mysql_affected_rows();
		}
           
           
		function free_result($result){   //sorgu sonucunun hafızadan temizlenmesini sağlar.
			@mysql_free_result($result);
			unset($result);
		}
		  
		  
		function insert_id(){   //enson eklenen kayıt id nosunu verir.
			return mysql_insert_id();
		}
		  
		  
		function result($result, $index = 0){
			return mysql_result($result, $index);
		}
		
		
		function close(){ //veri tabanı bağlantısını kapatır.
			return mysql_close($this->conn);
		}
		
		
		function clean($var){ //sql cümlelerinin içindeki zararlı karakterleri temizler.
			return mysql_real_escape_string($var);
		}
			
		
		function nextRow($tableName)
		{
			$sql = "SELECT MAX(row) AS maxRow FROM $tableName";
			$value = $this->query($sql);
			if($fetchResult = $this->fetch_assoc($value))
			{
				$newRow = $fetchResult["maxRow"];
				if($newRow%10 == 0)
					return $newRow+10;
				else
				{
					$modRow = 10 - ($newRow % 10);
					return $newRow + 10 + $modRow;	
				}
			}
			else
				return 0;
		}
		
		function getDatabaseName()
		{
			return $this->dbName;
		}
   }
     
//$db = new DatabaseClass; // db isimli Veritabani nesnesi olusuyor.
//$db->connect(); //mysql.php include edildigi anda veritabani baglantisi acilir.
     
?>
