<?php

class DB extends PDO
{
	/*** mysql config ***/
	private $username = 'cloudbreakr';
	//private $hostname = 'crawl.c2cryczu3cau.ap-southeast-1.rds.amazonaws.com'; //54.254.226.195
	private $hostname = 'crawl.cloudbreakr.com';

	//private $crawl_read_hostname = "crawl-read.cbfboizfnqgg.ap-northeast-1.rds.amazonaws.com";
	// private $hostname = 'crawl.cloudbreakr.com'; //54.254.226.195
	private $password = 'xW9d*7Fn&UdM';

	private $dbName = 'cloudbreakr_db2';

	private $new_dbName = 'myDB';
	private $web_hostname = '192.168.64.2';
	private $web_username = 'cloudbreakr';
	private $web_password = 'cloudbreakr';

	private $AUTOHostname = 'automation.c2cryczu3cau.ap-southeast-1.rds.amazonaws.com';
	private $AUTOusername = 'cloudbreakr';
	private $AUTOpassword = 'adv1whereaut02o18';
	private $automation_dbName = 'cloudbreakr_automation';


	private $pixnet_dbName = 'cloudbreakr_pixnet';

	public $db = null;
	public $webDb = null;
	private $type = null;

	public function __construct($type = null)
	{
		$this->initDB($type);
	}

	public function initDB($type = null)
	{

		try{
			$this->db = new PDO("mysql:host=$this->hostname;dbname=$this->dbName", $this->username, $this->password);

			//$this->crawl_db = new PDO("mysql:host=$this->crawl_read_hostname;dbname=$this->dbName", $this->username, $this->password);

			if($type == 'WEB'){
				//$this->db = new PDO("mysql:host=$this->web_hostname;dbname=$this->web_dbName", $this->web_username, $this->web_password);
				$this->db = new PDO("mysql:host=$this->web_hostname;dbname=$this->new_dbName;charset=utf8", $this->web_username, $this->web_password);

				$this->db->query("SET NAMES utf8");
			}else if($type == "NEW"){
				$this->db = new PDO("mysql:host=$this->hostname;dbname=$this->new_dbName;charset=utf8", $this->username, $this->password);

				$this->db->query("SET NAMES utf8");
			}else if($type == "PIXNET"){
				$this->db = new PDO("mysql:host=$this->hostname;dbname=$this->pixnet_dbName;charset=utf8", $this->username, $this->password);

				$this->db->query("SET NAMES utf8");
			}else if($type =="AUTOMATION"){
				$this->db = new PDO("mysql:host=$this->AUTOHostname;dbname=$this->automation_dbName;charset=utf8", $this->AUTOusername, $this->AUTOpassword);

				//$this->db->query("SET NAMES latin1");
			}
			// }else if($type == "NEWWEB"){
			// 	$this->db = new PDO("mysql:host=$this->web_hostname;dbname=$this->new_dbName;charset=utf8", $this->web_username, $this->web_password);

			// 	$this->db->query("SET NAMES utf8");
			// }
		}catch(PDOException $e){
			echo __LINE__.' '.$e->getMessage();
		}
	}

	public function resetDBConnection()
	{
		$this->db = NULL;

		sleep(30);

		$this->initDB();
	}

	public function __destruct()
	{
		$this->db = NULL;
	}

	public function insertQuery($sql)
	{
		try{
			$this->db->exec($sql) or print_r($this->db->errorInfo());
			return $this->db->lastInsertId();
		}
		catch(PDOException $e)
		{
			echo __LINE__.$e->getMessage();
		}
	}

	public function runQuery($sql, $args = null){
		try{
			$sth = $this->db->prepare($sql);
			
			$sth->execute($args);
			return $this->db->lastInsertId();
		}
		catch(PDOException $e)
		{

			echo __LINE__.$e->getMessage();
		}
		catch(Exception $e){
			echo __LINE__.$e->getMessage();
		}
	}


	// public function runQuery($sql){
	// 	try{
	// 		if(($count = $this->db->exec($sql)) === false){
	// 			print_r($this->db->errorInfo());
	// 		}
	// 		return $count;
	// 	}
	// 	catch(PDOException $e)
	// 	{
	// 		echo __LINE__.$e->getMessage();
	// 	}
	// }

	public function readQuery($sql, $args = null) {
		try{
			$sth = $this->db->prepare($sql);

			$sth->execute($args);
	        return $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
		catch(PDOException $e)
		{
			echo __LINE__.$e->getMessage();
		}
	}
	public function query($sql, $args = null) {
		try{
			$sth = $this->db->prepare($sql);

			$sth->execute($args);
	        return $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
		catch(PDOException $e)
		{
			echo __LINE__.$e->getMessage();
		}
	}

	public function fetchDb($sql, $args = null, callable $callback){

		try{
			$sth = $this->db->prepare($sql);

			$sth->execute($args);
			$key = 0;
	        while($user = $sth->fetch(PDO::FETCH_ASSOC)){
	        	$callback($key, $user);
	        	$key++;
	        };

	        $sth = null;
	        return;
		}catch(PDOException $e)
		{
			echo __LINE__.$e->getMessage();
		}
	}

  	public function updateQuery($sql, $args = null){
		try{
			$sth = $this->db->prepare($sql);
			$sth->execute($args);
			return $sth->rowCount();
		}
		catch(PDOException $e)
		{
			echo __LINE__.$e->getMessage();
		}
	}
	public function getJoinQuery($sql){
		$result = $this->db->query($sql);
		return $result;
	}

	public function getQuery($sql){

		$result = $this->db->query($sql);
		return $result->fetchAll();

	 //    $stmt->setFetchMode(PDO::FETCH_ASSOC);
		// return $stmt; // Returns an associative array that can be diectly accessed or looped through with While or Foreach
	}


}

?>
