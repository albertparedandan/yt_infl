<?php 
require dirname(dirname(__FILE__)). '/vendor/autoload.php';

class PrintLog
{
	public $logger = null;

	public function __construct($type = null, $location = null)
    {
        $this->logger = new Katzgrau\KLogger\Logger(dirname(dirname(__FILE__)).'/logs/'.$type."/".date("Ymd", time()), Psr\Log\LogLevel::INFO, array (
			'dateFormat' 	=> 'Y-m-d h:i:sa',
		    'extension' 	=> 'log',
		    'filename'		=>	$location.date("His", time()),
		));
    }

	public function log($type = null, $message = null , $timestamp = null)
	{
		if(is_null($timestamp)){
			$timestamp = time();
		}
		if($type == "TIMEOUT"){
			$this->logger->notice($message);
		}else if($type == "NOTICE"){
			$this->logger->notice($message);
		}else if($type == "ERROR"){
			$this->logger->error($message."\n===================\n");
		}else{
			$this->logger->info($message);
		}
		echo "[".date("Y-m-d h:i:sa", $timestamp )."] [".$type."] ".$message."\n";
	}
}

?>

