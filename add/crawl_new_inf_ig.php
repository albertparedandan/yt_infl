<?php
/**
*	Grab execel instagram data
**/ 
// ini_set('memory_limit', '-1');
// ini_set('max_execution_time', 300);

if( !class_exists('PrintLog') ) {
	require dirname(dirname(__FILE__)). '/utils/printLog.php';
}
if( !class_exists('YtVideo') ) {
	require dirname(dirname(__FILE__)). '/model/ytVideo.php';
}
if( !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}
if( !class_exists('Core') ) {
	require dirname(dirname(__FILE__)). '/grab/core.php';
}
if( !class_exists('Emoji') ) {
	require dirname(dirname(__FILE__)). '/library/emoji.php';
}
if( !class_exists('Influencer') ) {
	require dirname(dirname(__FILE__)). '/model/influencer.php';
}
if( !class_exists('YtUser') ) {
	require dirname(dirname(__FILE__)). '/model/ytUser.php';
}
if( !class_exists('NormalModel') ) {
	require dirname(dirname(__FILE__)). '/model/normal.php';
}
if( !class_exists('RadarScoreCount') ) {
	require dirname(dirname(__FILE__)). '/utils/radarScoreCount.php';
}
if( !class_exists('WebAPI') ) {
	require dirname(dirname(__FILE__)). '/utils/WebAPI.php';
}
require dirname(dirname(__FILE__)). '/vendor/autoload.php';

class UpdateChromeIgUser extends Core
{
	const EXCEL_ROW_NUM = 2;
	public $printLog = null;
	public $db = null;
	const INSERT_TYPE = "NEW_USER_BY_GOOGLE_EXTENSION";

	public function __construct()
	{
		parent::__construct("TEST");
		$this->WebAPI = new WebAPI;
		$this->dba = new DB("AUTOMATION");
		$this->db = new DB();
		$this->dbw = new DB("WEB");

		$this->ddb = new DB();
		$this->wdb = new DB("WEB");
		$this->normalModel = new NormalModel;
		$this->postModel = new Post;
		$this->influencerModel = new Influencer;
		$this->userModel = new YtUser;
		$this->radarScoreCount = new RadarScoreCount;
	}

	public function run() 
	{
		$sql = "SELECT * FROM yt_user_temp where type = :type AND subscriberCount >= 0 and locationId <= 4";
		$yt_user_temp = $this->dba->query($sql, [':type'=>'TRANSFER_PENDING']);
		//SELECT * FROM ig_user_temp where type = 'TRANSFER_PENDING'
		
		$infs = $yt_user_temp;
		$this->insertStartTimeLog = time();
		
		$this->identityList = $this->getIdentityList();
		$this->interestList = $this->getInterestList();
		$this->locationList = $this->getLocationList();
		$this->recentDay = time()-(24*60*60*7);
		$this->recent97Day = time()-(24*60*60*97);
		$this->totalApiCount = 0;
		$this->failCount = 0;
		$this->successCount = 0;
		$this->insertDBTime = 0;
		$this->totalInsertDBTime = 0;
		$this->apiCount = 0;
		$this->timeoutForRateLimitCount = 0;
		$this->timeoutForResetDBCount = 0;
		$this->updateUserStartTime = time();
		
		foreach ($infs as $key => $influencer) {
			$this->printLog->log("INFO", "[".($key+1)."]Start ".$influencer['name']."(".$influencer['id'].")");
			$sql = "SELECT * FROM yt_user where name = :name";
			$existed = $this->dbw->query($sql, [':name'=>$influencer['name']]);
			if($existed){
				$this->printLog->log("INFO", "EXISTED USER - yt_user_id = ".$existed['id']);
				$this->printLog->log("INFO", "End[".($key+1)."]======");
				continue;
			}
			$this->printLog->log("INFO", "Transfer yt user temp");
			
			$this->transferToGrabDb($influencer);
			
			
		}
		echo "Success\n";
		$this->printLog->log("INFO", "DONE ======");

		$this->printLog->log("INFO", "All completed! \nStart time: ".date("Y-m-d h:i:sa", $this->insertStartTimeLog)." \nProducing time: ".((time()-$this->insertStartTimeLog)/60)."(m)");
		

		$result = $this->WebAPI->sendEmailForAdmin('edmund.kong@cloudbreakr.com','Crawl new inf from Google Chrome', "All completed!<br> Start time: ".date("Y-m-d h:i:sa", $this->insertStartTimeLog)."<br> End time: ".date("Y-m-d h:i:sa", time())."<br> Success Count: ".$this->successCount."<br> Fail Count: ".$this->failCount."<br>==========================<br>".$userList.$locationCountList);
		echo "\nDone\n";
		return;

	}

	public function transferToGrabDb($user)
	{
		// $user_interest = null;
		// if(isset($user['interest'])){
		// 	$user_interest = $user['interest'];
		// }
		$sql = "SELECT interest_id FROM yt_user_temp_interest WHERE yt_user_temp_id = :id";
		$user['interests'] = $this->dba->query($sql, [':id' => $user['id']]);

		/* $sql = "SELECT language_id FROM ig_user_temp_language WHERE ig_user_temp_id = :id";
		$user['languages'] = $this->dba->query($sql, [':id' => $user['id']]); */

		

		//insert;
		if(!$user['locationId'] && !$user['identityId'] && !isset($user['interest']) ){
			$this->printLog->log("INFO", $user['id']." no location, no identityid, no interest");
			$this->failCount++;
			return;
		}

		if($user['locationId'] >=4  && $user['locationId']<= 1){
			$this->printLog->log("INFO", $user['id']." locationId != 1,2,3,4 ");
			$this->failCount++;
			return;
		}

		if($user['identityId'] == 20 || $user['identityId'] == 21 || $user['identityId'] == 22){
			$this->printLog->log("INFO", $user['id']." identityId = 20,21,22");
			$this->failCount++;
			return;
		}

		//Reset Database Connection
		if($this->insertDBTime >= 150){
			$this->printLog->log("TIMEOUT", "TIMEOUT 30(s) Reset Database Connection!\n");
			//$this->normalModel->resetDBConnection();
			$this->dba = new DB("AUTOMATION");
			$this->db = new DB();
			$this->dbw = new DB("WEB");

			$this->ddb = new DB();
			$this->wdb = new DB("WEB");
			$this->insertDBTime = 0;
			$this->timeoutForResetDBCount += 30;
		}

		$this->insertStartTime = time();
		echo  "Transfer: ".$user['id']."\n";
		$userDetail = parent::getYtUserDetail($user, self::INSERT_TYPE);

		if(!$userDetail){
			$this->printLog->log("ERROR", "Get Yt Api Fail!");
			$this->failCount++;
			return;
		}

		// Fail: IG user status private or user not exist

		// Fail: IG user follower count small than 5000
		// echo $userDetail['igId'] ." - ".$userDetail['followerCount']."\n";
		// if((isset($userDetail['followerCount']) && $userDetail['followerCount'] < 5000) && $userDetail['igId'] != '2231490539' ) {
			
		// 	$this->printLog->log("ERROR", "Follower count small than 5000 FollowerCount:".$userDetail['followerCount']);
			
		// 	$this->normalModel->insertErrorLog($user['igId'], 0, "Follower5000", "Follower count small than 5000 (".$userDetail['followerCount'].")");
			
		// 	$this->failCount++;
		// 	return;
		// }

		// Fail: reading IG API rate limit over than 5000 per hour
		if( isset($userDetail->code) && $userDetail->code == 429 ){
			
			$this->printLog->log("ERROR", "Get Ig Api Fail! [".$userDetail->error_message."]");
			
			$this->normalModel->insertErrorLog($user['igId'], $userDetail->code , $userDetail->error_type, $userDetail->error_message);

			$this->failCount++;
			return;
		}
		// 4. Get User media data from Ig api
		$this->printLog->log("INFO", "Get user post(".$userDetail['postCount'].") from IG API");
		
		$userDetailWithMedia = parent::getUserMediaData($userDetail, $this->updateUserStartTime, self::INSERT_TYPE);
		
		$userDetail = $userDetailWithMedia;
		echo "\n";
		//$totalMedia = count($userDetailWithMedia['media']);
		/* $this->apiCount += $userDetailWithMedia['apiCount'];
		$this->totalApiCount += $userDetailWithMedia['apiCount']; */


		$this->printLog->log("INFO", "Count Influencer Power.");
		$userDetail['activeness'] = parent::countActiveness($userDetail);
		$userDetail['newInteraction'] = parent::countInteraction($userDetail);
		$userDetail['explosiveness'] = parent::countExplosiveness($userDetail);
		$userDetail['reach'] = parent::countReach($userDetail, "YT");
		$userDetail['engagement'] = parent::countEngagement($userDetail['newInteraction'], $userDetail['subscriberCount']);

		$this->printLog->log("INFO", "Count Appeal.");
		//$this->firstLoggedAt = $this->normalModel->getLastUserLog($userDetail['id']);
		$userDetail = parent::countAppeal($userDetail, $this->updateUserStartTime, null, "IG");
		$this->printLog->log("INFO", "End count appeal.");

		$userDetail['activenessScore'] = $this->radarScoreCount->getActivenessScore($userDetail['activeness']);

		$userDetail['engagementScore'] = $this->radarScoreCount->getEngagementScore($userDetail['engagement']);
		$userDetail['interactionScore'] = $this->radarScoreCount->getInteractionScore($userDetail['newInteraction']);
		$userDetail['explosivenessScore'] =$this->radarScoreCount->getExplosivenessScore($userDetail['explosiveness']);
		$userDetail['reachScore'] = $this->radarScoreCount->getReachScore($userDetail['reach']);
		$userDetail['appealScore'] = $this->radarScoreCount->getAppealScore($userDetail['appeal']);
		$userDetail['infPower'] = round((($userDetail['engagementScore']+$userDetail['interactionScore']+$userDetail['explosivenessScore']+$userDetail['reachScore']+$userDetail['appealScore'])/5), 2);

		// 4. Count Recent Data and Rating
		$this->printLog->log("INFO", "Count Recent Data and Rating");
		$userDetail = parent::countRecentAndRating($userDetail);

		//$totalApiCall += $userDetail['apiCount'];

		//$this->firstLoggedAt = $this->normalModel->getLastUserLog($userDetail['id']);

		// 4.1. Count User follower rising Percentage
		$this->printLog->log("INFO", "Count follower rising percentage");
		$userDetail = parent::countFollowerRisingPercentage($userDetail, $this->recentDay);
		
		// 4.2. Count User interaction rising Percentage
		$this->printLog->log("INFO", "Count interaction rising percentage");
		$userDetail = parent::countInteractionRisingPercentage($userDetail, $this->recentDay, $this->recent97Day);

		// 4.3 Count engagement rate
		$this->printLog->log("INFO", "Count Engagement rate");
		$userDetail = parent::countEngagementRate($userDetail, $userDetail['interaction'], $userDetail['subscriberCount']); 
		
		$userDetail['weeklyLoggedAt'] = $this->recentDay;
		//print_r($userDetail);

		// // 4. Count Recent Data and Rating
		// $this->printLog->log("INFO", "Count Recent Data and Rating");
		// $userData = parent::countRecentAndRating($userDetailWithMedia);
		
		// // 4.1. Count User follower rising Percentage
		// $userData = parent::countFollowerRisingPercentage($userData, $this->recentDay);

		// // 4.2. Count User interaction rising Percentage
		// $userData = parent::countInteractionRisingPercentage($userData, $this->recentDay, $this->recent97Day);
		
		// $userData['weeklyLoggedAt'] = $this->recentDay;

		// 5. Insert data to DB
		$insertDBStartTime = time();
		$this->printLog->log("INFO", "Insert to local database");
	
		$this->insertData($userDetail);
		$this->insertDBTime += time()-$insertDBStartTime;
		$this->totalInsertDBTime += $this->insertDBTime;

		$this->printLog->log("INFO", "[".$user['ytId']."] completed! \nProducing time: ".((time()-$this->insertStartTime)/60)."(m)\nInsert DB time: ".(time()-$insertDBStartTime)."(s)\n===================\n");
		

		$sql = "UPDATE `yt_user_temp` SET
						`updatedAt` = :updatedAt,
						`type` = :type,
						`message` = :message

			WHERE `ytId`= :ytId";

		$this->dba->query($sql, [
									':ytId' => $user['ytId'],
									':updatedAt' => time(),
									':type' => "FULL_COMPLETED",
									':message' => ""
								]);
		echo "DONE\n\n";
		$this->successCount++;
	}


	public function insertData($user)
	{
		$this->printLog->log("INFO", "Insert Yt user");
		
		/* $user['language_id'] = $user['firstLangId']; */
		$yt_user_id = $this->userModel->insert($user);
		echo $yt_user_id."<<<< yt_user_id\n";

		// 5.2 Insert influencer data
		$this->printLog->log("INFO", "Insert Influencer");
		$user['sourceFrom'] = "AUTO_MANUAL";
		$influencer_id = $this->influencerModel->insert($user, $yt_user_id);
		echo "inf id:".$influencer_id."\n";
		if(!is_numeric($influencer_id) || $influencer_id == 0){
			return;
		}
		if(isset($user['interests'])){
			foreach ($user['interests'] as $key => $interest) {
				echo "interest: ".$interest['interest_id']."\n";
				$this->influencerModel->insertInfluencerInterest($influencer_id, $interest['interest_id']);
			}
		}

		/* if(isset($user['languages'])){
			foreach ($user['languages'] as $key => $language) {
				echo "language: ".$interest['language_id']."\n";
				$this->influencerModel->insertInfluencerLanguage($influencer_id, $language['language_id']);
			}
		} */
		$this->printLog->log("INFO", "Insert Post");
		// 5.3 Insert User Media data to server
		foreach ($user['media'] as $key => $media) {
			//echo '-';
			echo $key."\n";
			$this->postModel->insert($yt_user_id, $media);
		}
		//echo "inserted\n";
	}


}	


$updateChromeIgUser = new UpdateChromeIgUser();
$updateChromeIgUser->run();

?>