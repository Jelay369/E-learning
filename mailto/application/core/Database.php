<?php 
class Database{
	
	private $query,$nombreParam, $lastTable;
	private static $conn,$host,$base,$user,$pass,$port ;
		// bd = new PDO("mysql:host=localhost;dbname=nom","user","pass")
		// initialisation
	public static function init($hote,$base,$port,$user,$pass) {
		self::$host=htmlspecialchars(trim($hote)) ;
		// self::$port=htmlspecialchars(trim($port)) ;
		self::$base=htmlspecialchars(trim($base)) ;
		self::$user=htmlspecialchars(trim($user)) ;
		self::$pass=htmlspecialchars(trim($pass)) ;

	}
	public static function db() {
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	);
		//var_dump(self::$host,self::$port,self::$base,self::$user,self::$pass);die;

		$url ="mysql:host=".self::$host.";port=".self::$port.";dbname=".self::$base;
		if(!isset(self::$conn)) {
			self::$conn = new PDO($url,self::$user,self::$pass,$options);
		}
		return self::$conn ;

	}
		// execution du requete
	public function execute($value=null) {

			// tester s'il y des trous dans les requetes
			// si c'est une requete de selection
		if (strpos($this->query,"SELECT")>-1) {

			if (strpos($this->query,"?")>-1 ){
				$req = self::db()->prepare($this->query) ;
				$req->execute($value) ;
			}
			else {
				$req = self::db()->query($this->query) ;
			}

			
			return $req->fetchAll(PDO::FETCH_OBJ);
		}
		else {
			if (strpos($this->query,"?")>-1 ){
				$req = self::db()->prepare($this->query) ;
				$req->execute($value) ;
			}
			else {
				self::db()->query($this->query) ;
			}
			return 0 ;
		}
	}
	public function getQuery() {
		return $this->query ;
	}
	public function select($table){
		$table=htmlspecialchars(trim($table)) ;
		$this->query = "SELECT * FROM ".$table ;
		$this->lastTable =$table ;
		return $this ;
	}
	public function where($id,$operateur){
		$this->query .= " WHERE ".$id."".$operateur."?";

		return $this ;
	}
	public function and($id,$operateur) {
		$this->query .= " AND ".$id."".$operateur."?";

		return $this ;
	}
	public function or($id,$operateur) {
		$this->query .= " OR ".$id."".$operateur."?";

		return $this ;
	}
		// insertion 
	public function insert($table) {
		$this->query="INSERT INTO ".$table ;
		return $this ;
	}
	// mise à jours 
	public function update($table) {
		$this->query="UPDATE ".$table ;
		return $this ;
	}
	public function parametters($fields) {
		if (strpos($this->query,"INSERT")>-1){
			// insertion 
			$listeFields="(" ;
			$valeur="VALUES (" ;
			for ($i=0; $i <count($fields) ; $i++) { 
				$listeFields.=$fields[$i]."," ;
				$valeur.="?," ;
			}

			// manala ny ',' farany
			$listeFields=trim($listeFields,",").")" ;

			$valeur=trim($valeur,",").")" ;
			$this->query.=" ".$listeFields ;
			$this->query.=" ".$valeur ;
		}
		else if (strpos($this->query,"UPDATE")>-1){
			$listeFields="SET " ;
			for ($i=0; $i <count($fields); $i++) { 
				$listeFields.=$fields[$i]."=?," ;
			}
			$listeFields=trim($listeFields,",") ;
			$this->query.=" ".$listeFields ;
		}
		
		return $this ;
	}

		// suppréssion 
	public function delete($table) {
		$table=htmlspecialchars(trim($table)) ;
		$this->query=" DELETE FROM ".$table;
		return $this ;
	}
	public function order($param) {
		$this->query.=" ORDER BY " ;
		$listeOrder="" ;
		foreach ($param as $key => $value) {
			$listeOrder.=$key. " ".$value."," ;
		}
		$listeOrder=trim($listeOrder,",") ;
		$this->query.=$listeOrder ;
		return $this ;
	}
	public function inner($table2,$id1) {
		$table2=htmlspecialchars(trim($table2)) ;
		$id1= htmlspecialchars(trim($id1)) ;
		
		$this->query.=" INNER JOIN ".$table2." ON ".$this->lastTable.".".$id1."=".$table2.".".$id1 ;
		$this->lastTable=$table2;
		return $this;
	}
	// inner join id différent
	public function innerD($table2,$id1,$id2) {
		$table2=htmlspecialchars(trim($table2)) ;
		$id1= htmlspecialchars(trim($id1)) ;
		
		$this->query.=" INNER JOIN ".$table2." ON ".$this->lastTable.".".$id1."=".$table2.".".$id2 ;
		$this->lastTable=$table2;
		return $this;
	}
	public function sum($table,$fields) {
		$fields = htmlspecialchars(trim($fields)) ;
		$table = htmlspecialchars(trim($table)) ;
		$this->query="SELECT SUM(".$fields.") AS somme FROM ".$table ;
		return $this ;
	}
	// selection avec paramètre
	public function selectParam($table,$listeFields) {
		$reqBase ="SELECT " ;
		for ($i=0; $i <count($listeFields) ; $i++) { 
			$reqBase.=$listeFields[$i]."," ;
		}
		$this->query = trim($reqBase,",")." FROM ".$table;
		return $this ;


	}

	public function limit(int $limit){
		$this->query .= " LIMIT ".$limit;
		return $this;
	}

	public function customSelect($requete) {
		$req = self::db()->query($requete) ;
		return $req->fetchAll(PDO::FETCH_OBJ);
	}
	
	
	

}
	Database::init("localhost",'wmkjpwwb_tutoinfo_mailto',"3306",'wmkjpwwb_mailto','wGoGBGP7o;;L7Bw7PG');
?>
