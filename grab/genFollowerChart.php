<?php
/**
*	Generate top influncer per day
**/ 
if( !class_exists('PrintLog') ) {
	require dirname(dirname(__FILE__)). '/utils/printLog.php';
}
// if( !class_exists('IgUser') ) {
// 	require dirname(dirname(__FILE__)). '/model/igUser.php';
// }
// if( !class_exists('FbUser') ) {
// 	require dirname(dirname(__FILE__)). '/model/fbUser.php';
// }
// if( !class_exists('transferChart') ) {
// 	require dirname(dirname(__FILE__)). '/transfer/transferChart.php';
// }

if( !class_exists('Influencer') ) {
	require dirname(dirname(__FILE__)). '/model/influencer.php';
}
if( !class_exists('Core') ) {
	require dirname(__FILE__) . '/core.php';
}
if( !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}

class GenFollowerChart extends Core
{
	const LIMIT_NUMBER = 27; 
	private $postModel = null; 
	private $influencerModel = null;
	public $locationId = null;
	public $printLog;
	public $type = null;

	public function __construct($location = null)
	{
		parent::__construct($location);
		$this->db = new DB();
		$this->influencerModel =  new Influencer;
		$this->userFbModel =  new FbUser;
		$this->wdb = new DB("WEB");
		// if($type == "FB"){
		// 	$this->printLog  = new PrintLog("genFollowerGrowthChartFB", $location);
		// }else{
		// 	$this->printLog  = new PrintLog("genFollowerGrowthChartIG", $location);
		// }
		$this->printLog  = new PrintLog("genFollowerGrowthChart", $location);
		$this->updatedTime = time();
		$socialPlatformList = parent::getSocialPlatformList();
		foreach ($socialPlatformList as $key => $platform) {
			if($platform['name'] == "instagram"){
				$this->ig_id = $platform['id'];
			}else if($platform['name'] == "facebook"){
				$this->fb_id = $platform['id'];
			}else if($platform['name'] == "youtube"){
				$this->youtube_id = $platform['id'];
			}
		}
	}

	public function genChart($user) 
	{
		$day7 = ($this->updatedTime - (7*24*60*60));
		$day30 = ($this->updatedTime - (30*24*60*60));
		$platform_ig_id = $this->ig_id;
		$platform_fb_id = $this->fb_id;
		$platform_yt_id = $this->yt_id;
		
		$this->clearFollowerChart($user['id']);
		//$this->clearInteractionChart($user['id']);

		if($user['fb_user_id']){
			$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(`loggedAt`), '%d%m%y') as 'Date' FROM `fb_update_user_log` WHERE fb_user_id = ".$user['fb_user_id']." GROUP BY Date ORDER BY loggedAt DESC Limit 90";
			echo $sql."\n";
			$fbResult = $this->db->readQuery($sql);
			if(count($fbResult)==0){
				$this->printLog->log("INFO", "No Log Data\n\n");
				return;
			}
		}
		if($user['ig_user_id']){
			$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(`loggedAt`), '%d%m%y') as 'Date',  updatedFollowerCount AS fanCount FROM `ig_update_user_log` WHERE ig_user_id = ".$user['ig_user_id']." GROUP BY Date ORDER BY loggedAt DESC Limit 90";
			//echo $sql."\n";
			$igResult = $this->db->readQuery($sql);
			if(count($igResult)==0){
				$this->printLog->log("INFO", "No Log Data\n\n");
				return;
			}
		}
		// if($user['fb_user_id']){
		// 	$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(`loggedAt`), '%d%m%y') as 'Date' FROM `fb_update_user_log` WHERE fb_user_id = ".$user['id']." AND postCount90 !='' GROUP BY Date ORDER BY loggedAt ASC Limit 90";
		// 	//echo $sql."\n";
		// 	$fbResult = $this->db->query($sql);
		// 	if(count($fbResult)==0){
		// 		$this->printLog->log("INFO", "No Log Data\n\n");
		// 		return;
		// 	}
		// }
		
		// if($user['ig_user_id']){
		// 	$sql = "SELECT *, DATE_FORMAT(FROM_UNIXTIME(`loggedAt`), '%d%m%y') as 'Date',  updatedFollowerCount AS fanCount FROM `ig_update_user_log` WHERE ig_user_id = ".$user['ig_user_id']." AND postCount90 !='' GROUP BY Date ORDER BY loggedAt ASC Limit 90";
		// 	///echo $sql."\n";
		// 	$igResult = $this->db->query($sql);
		// 	if(count($igResult)==0){
		// 		$this->printLog->log("INFO", "No Log Data\n\n");
		// 		return;
		// 	}
		// }
		if($user['fb_user_id']&&$user['ig_user_id']){

			$this->printLog->log("INFO", "FB&IG!");
			// $createLogDay = $fbResult[0]['loggedAt'];

			// $result = $fbResult;
			// if($fbResult[0]['loggedAt'] < $igResult[0]['loggedAt']){
			// 	$createLogDay = $igResult[0]['loggedAt'];
			// 	$result = $igResult;
			// }
			foreach ($igResult as $ig) {
				foreach ($fbResult as $fb) {
					if($ig['Date'] == $fb['Date']){
						echo $ig['fanCount'].' - '.$fb['fanCount']."\n";
						$this->insertChartData($user['id'], $platform_ig_id, $ig);
						$this->insertChartData($user['id'], $platform_fb_id, $fb);

						// if($ig['postCount90'] == $fb['postCount90'] && $ig['postCount90'] != ''){
						// 	$interaction = 0;
						// 	$interaction = round((($ig['likeCount90']+$ig['commentCount90'])/$ig['postCount90']),2);

						// 	$this->insertFbChartData($user['id'], $platform_ig_id, $ig['loggedAt'], $interaction);

						// 	$interaction = 0;
						// 	$interaction = round((($fb['likeCount90']+$fb['commentCount90'])/$fb['postCount90']),2);

						// 	$this->insertFbChartData($user['id'], $platform_fb_id, $fb['loggedAt'], $interaction);
						// }
						continue 2;
					}
				}
			}

		}else if($user['ig_user_id']){
			//$createLogDay = $igResult[0]['loggedAt'];
			foreach ($igResult as $key => $value) {
				$this->insertChartData($user['id'], $platform_ig_id, $value);

				// if($value['postCount90'] != ''){
				// 	$interaction = 0;
				// 	$interaction = round((($value['likeCount90']+$value['commentCount90'])/$value['postCount90']),2);
				// 	$this->insertFbChartData($user['id'], $platform_ig_id, $value['loggedAt'], $interaction);
				// }
			}
		}else if($user['fb_user_id']){
			foreach ($fbResult as $key => $value) {
				$this->insertChartData($user['id'], $platform_fb_id, $value);
			}
		}
		$this->printLog->log("INFO", "Generate follower chart completed!\n");
	}

	public function clearFollowerChart($inf_id)
	{
		$sql = "DELETE FROM followers_performance
				WHERE `infId` = '".$inf_id."'";
		$this->db->runQuery($sql);
	}

	public function insertChartData($inf_id, $platform_id, $value){

		$sql = "INSERT INTO followers_performance (
								`infId`,
								`platform`,
								`performanceTime`,
								`value`
							) VALUES(
								:infId,
								:platform,
								:performanceTime,
								:value
							)";

		$this->db->runQuery($sql, [
				':infId' 			=> $inf_id,
				':platform' 		=> $platform_id,
				':performanceTime' 	=> $value['loggedAt'],
				':value' 			=> $value['fanCount']
			]);
	}

	public function clearInteractionChart($inf_id)
	{
		$sql = "DELETE FROM interactions_performance
				WHERE `infId` = '".$inf_id."'";

		$this->db->runQuery($sql);
	}

	public function insertFbChartData($inf_id, $platform_id, $loggedAt, $value){

		$sql = "INSERT INTO interactions_performance (
								`infId`,
								`platform`,
								`performanceTime`,
								`value`
							) VALUES(
								:infId,
								:platform,
								:performanceTime,
								:value
							)";

		$this->db->runQuery($sql, [
				':infId' 			=> $inf_id,
				':platform' 		=> $platform_id,
				':performanceTime' 	=> $loggedAt,
				':value' 			=> $value
			]);
	}

	public function run(){
		// echo $location['key'];
		// exit();
		// $users = $this->influencerModel->getAllInfUser($this->locationId);
		// //echo count($users);
		// $this->printLog->log("INFO", "Start Generate follower chart!\n");
		// foreach ($users as $key => $user) {
			
		// 	$this->printLog->log("INFO", "[".$user['id']."] Influencer Chart!");
		// 	$this->genChart($user);
		// 	$this->transfer($user['id']);
		// }
		

		$this->printLog->log("INFO", "Start Generate follower chart!\n");
		$this->influencerModel->fetchAllInfUser($this->locationId, function($key = 0, $user = null){
			$this->printLog->log("INFO", "[".$user['id']."] Influencer Chart!");
			$this->genChart($user);
			$this->transfer($user['id']);
		});

		// $this->influencerModel->fetchInfUser2( 6222,function($key = 0, $user = null){
		// 	$this->printLog->log("INFO", "[".$user['id']."] Influencer Chart!");
		// 	$this->genChart($user);
		// 	$this->transfer($user['id']);
		// });
		$this->printLog->log("INFO", "Generate Chart Completed!");
	}

	public function transfer($infId){
		$result = $this->influencerModel->getFollowerPerformance($infId);
		//print_r($result);
		$sql = "DELETE FROM followers_performance
				WHERE `infId` = '".$infId."'";

		$this->wdb->runQuery($sql);
		foreach ($result as $key => $value) {
			$sql = "INSERT INTO followers_performance (
								`infId`,
								`platform`,
								`performanceTime`,
								`value`
							) VALUES(
								:infId,
								:platform,
								:performanceTime,
								:value
							)";
			$args = [
					':infId' 			=> $infId,
					':platform' 		=> $value['platform'],
					':performanceTime' 	=> $value['performanceTime'],
					':value' 			=> $value['value']
				];
			$this->wdb->runQuery($sql, $args);
		}
	}
}


$location = null;
if(isset($argv[1])){
	$location = $argv[1]; //HK TW
}

$genFollowerChart = new GenFollowerChart($location);
$genFollowerChart->run();


?>