<?php
/**
*	Grab NEW instagram data
**/ 

require dirname(__FILE__) . '/core.php';
if( !class_exists('PrintLog') ) {
	require dirname(dirname(__FILE__)). '/utils/printLog.php';
}
if( !class_exists('Post') ) {
	require dirname(dirname(__FILE__)). '/model/igPost.php';
}
if( !class_exists('IgUser') ) {
	require dirname(dirname(__FILE__)). '/model/igUser.php';
}
if( !class_exists('FbPost') ) {
	require dirname(dirname(__FILE__)). '/model/fbPost.php';
}
if( !class_exists('FbUser') ) {
	require dirname(dirname(__FILE__)). '/model/fbUser.php';
}
if (!class_exists('YtUser')) {
	require dirname(dirname(__FILE__)) . '/model/ytUser.php';
}
if (!class_exists('YtVideo')) {
	require dirname(dirname(__FILE__)) . '/model/ytVideo';
}
if( !class_exists('Influencer') ) {
	require dirname(dirname(__FILE__)). '/model/influencer.php';
}
if( !class_exists('NormalModel') ) {
	require dirname(dirname(__FILE__)). '/model/normal.php';
}
if( !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}
if( !class_exists('RadarScoreCount') ) {
	require dirname(dirname(__FILE__)). '/utils/radarScoreCount.php';
}
if( !class_exists('transferInfluencer') ) {
	require dirname(dirname(__FILE__)). '/transfer/transferInfluencer.php';
}
if( !class_exists('UpdateFbUser') ) {
	require dirname(__FILE__). '/UpdateFbUserClass.php';
}
if( !class_exists('UpdateFbPost') ) {
	require dirname(__FILE__). '/updateFbPost.php';
}
if( !class_exists('WebAPI') ) {
	require dirname(dirname(__FILE__)). '/utils/WebAPI.php';
}
require dirname(dirname(__FILE__)). '/vendor/autoload.php';

class webNewUser extends Core
{	
	private $postModel = null;
	private $userModel = null;
	private $influencerModel = null;
	private $normalModel = null;

	private $print = null;
	const NEW_REGISTER = "newInfluencer";

	const INSERT_TYPE = "NEW_USER";
	private $type = null;

	public function __construct($location = null, $type = null)
	{
		parent::__construct($location);
		$this->postModel =  new Post;
		$this->normalModel = new NormalModel;
		$this->userModel =  new IgUser;
		$this->fbUserModel = new FbUser;
		$this->fbPostModel = new FbPost;
		$this->ytUserModel = new YtUser;
		$this->ytVideoModel = new YtVideo;
		$this->influencerModel = new Influencer;
		$this->WebAPI = new WebAPI;
		$this->radarScoreCount = new RadarScoreCount();
		$this->wdb = new DB("WEB");
		$this->location = $location;
		$this->type = $type;
		if($type == self::NEW_REGISTER){

			$this->wdb = new DB("WEB");
			$this->type = $type;
			$this->printLog = new PrintLog("newRegisterInfluencer", $location);
			$this->transferInfluencer = new TransferInfluencer($location, 'new');
		}else{
			$this->printLog = new PrintLog("newUser", $location);
		}
	}

	public function run() 
	{
		
		// $this->transferInfluencer->transferByinfId(2063);
		// echo "OOK\n";
		// exit();
		// 0. Clear Insert Error Log
		$this->normalModel->clearInsertErrorLog();

		// 1. Get local popular user ig id
		$sql = "SELECT * FROM new_influencer WHERE locationId = ".$this->locationId." AND status = 'COMPLETED' ORDER BY followers DESC ";
		$localUserData = $this->wdb->query($sql);

		foreach ($localUserData as $key => $user) {
			$localUserData[$key]['newInfluencerId'] = $user['id'];
			$sql = "SELECT * FROM new_influencer_interest WHERE newInfluencerId = ".$user['id'];
			$localUserData[$key]['interest1'] = null;
			$localUserData[$key]['interest2'] = null;
			$localUserData[$key]['interest3'] = null;
			$interests = $this->wdb->getQuery($sql);
			foreach ($interests as $ikey => $interest) {
				$localUserData[$key]['interest'.($ikey+1)] = $interest['interestId'];
			};
		}
		//print_r($localUserData);
		// 2. Get not exist user data
		$popUsersData = parent::getNotExistUser($localUserData);

		$recentDay = time()-(24*60*60*7);
		$recent97Day = time()-(24*60*60*97);
		$totalApiCount = 0;
		$failCount = 0;
		$insertDBTime = 0;
		$totalInsertDBTime = 0;
		$apiCount = 0;
		$timeoutForRateLimitCount = 0;
		$timeoutForResetDBCount = 0;
		$updateUserStartTime = time();
		$totalApiCall = 0;

		foreach ($popUsersData as $key => $user) 
		{
			$insertType = null;
			//print_r($user);
			$totalFollowerCount = 0;
			// if($user['fan_page_fan_count']){
			// 	$totalFollowerCount = $totalFollowerCount + $user['fan_page_fan_count'] ;
			// }
			// if($user['followers']){
			// 	$totalFollowerCount = $totalFollowerCount + $user['followers'] ;
			// }

			if($user['fan_page_id']!=null){
				$user['fbId'] = $user['fan_page_id'];
				$fbUserDetail = parent::getFbUserDetail($user);
				
				$totalFollowerCount = $totalFollowerCount+$fbUserDetail['fanCount'];
			}

			if ($user['ytId'] != null) {
				$ytUserDetail = parent::getYtUserDetail($user);

				$totalFollowerCount = $totalFollowerCount + $ytUserDetail['subscriberCount'];
			}

			if($user['igId']!=null){
				//Wait for Api reset rate limit
				if($apiCount >= 4800){
					$this->printLog->log("TIMEOUT", "TIMEOUT 30(m) Wait for api reset rate limits!\n");
					sleep(60*30);
					$apiCount = 0;
					$timeoutForRateLimitCount += (60*30);
				}

				//Reset Database Connection
				if($insertDBTime >= 150){
					$this->printLog->log("TIMEOUT", "TIMEOUT 30(s) Reset Database Connection!\n");
					$this->normalModel->resetDBConnection();
					$insertDBTime = 0;
					$timeoutForResetDBCount += 30;
				}

				$insertStartTime = time();
				$this->printLog->log("INFO", "[".($key+1)."] Get Igid '".$user['igId']."'", $insertStartTime);

				$this->printLog->log("INFO", "Get user detail from IG API");
				// 3. Get latest user detail from Ig api
				$userDetail = parent::getUserDetail($user, self::INSERT_TYPE);
				// Fail: IG user status private or user not exist.
				if( isset($userDetail->meta) && $userDetail->meta->code == 400){
					
					$this->printLog->log("ERROR", "Get Ig Api Fail! :".$userDetail->meta->error_message);
					
					$this->normalModel->insertErrorLog($user['igId'], $userDetail->meta->code, $userDetail->meta->error_type, $userDetail->meta->error_message);
					
					$failCount++;
					continue;
				}

				// Fail: IG user follower count small than 5000
				echo $userDetail['igId'] ." - ".$userDetail['followerCount']."\n";
				$totalFollowerCount = $totalFollowerCount + $userDetail['followerCount'];
				if(($totalFollowerCount < 5000) && $userDetail['igId'] != '2231490539' ) {
					
					$this->printLog->log("ERROR", "Follower count small than 5000");
					
					$this->normalModel->insertErrorLog($user['igId'], 0, "Follower5000", "Follower count small than 5000 (".$userDetail['followerCount'].")");
					
					$failCount++;
					continue;
				}

				// Fail: reading IG API rate limit over than 5000 per hour
				if( isset($userDetail->code) && $userDetail->code == 429 ){
					
					$this->printLog->log("ERROR", "Get Ig Api Fail! [".$userDetail->error_message."]");
					
					$this->normalModel->insertErrorLog($user['igId'], $userDetail->code , $userDetail->error_type, $userDetail->error_message);

					$failCount++;
					continue;
				}
				// 4. Get User media data from Ig api
				$this->printLog->log("INFO", "Get user post(".$userDetail['postCount'].") from IG API");
				$userDetailWithMedia = parent::getUserMediaData($userDetail, $updateUserStartTime, self::INSERT_TYPE);

				echo "\n";

				$totalMedia = count($userDetailWithMedia['media']);
				$userDetail = $userDetailWithMedia;

				// if( $totalMedia != $userDetail['postCount'] && (($userDetail['postCount'] - $totalMedia) >10) ) {
				// 	$failCount++;
				// 	$error_message = "Api get post count: ".$totalMedia." (Total: ".$userDetail['postCount'].") Not Match.";
				// 	$this->printLog->log("ERROR", $error_message);
					
				// 	$this->normalModel->insertErrorLog($user['igId'], 1, "PostNotMatch", $error_message);
				
				// 	continue;
				// }else{
				// 	$this->printLog->log("INFO", "Post Match. ".$totalMedia." [".$userDetail['postCount']."] Call api: ".$userDetailWithMedia['apiCount']);
				// }

				$apiCount += $userDetailWithMedia['apiCount'];
				$totalApiCount += $userDetailWithMedia['apiCount'];


				$this->printLog->log("INFO", "Count Influencer Power.");
				$userDetail['activeness'] = parent::countActiveness($userDetail);
				$userDetail['newInteraction'] = parent::countInteraction($userDetail);
				$userDetail['engagement'] = parent::countEngagement($userDetail['newInteraction'], $userDetail['followerCount']);
				$userDetail['explosiveness'] = parent::countExplosiveness($userDetail);
				$userDetail['reach'] = parent::countReach($userDetail, "IG");

				$this->printLog->log("INFO", "Count Appeal.");
				$userDetail = parent::countAppeal($userDetail, $updateUserStartTime, null, "IG");
				$this->printLog->log("INFO", "End count appeal.");

				$userDetail['activenessScore'] = $this->radarScoreCount->getActivenessScore($userDetail['activeness']);
				$userDetail['engagementScore'] = $this->radarScoreCount->getEngagementScore($userDetail['engagement']);
				$userDetail['interactionScore'] = $this->radarScoreCount->getInteractionScore($userDetail['newInteraction']);
				$userDetail['explosivenessScore'] =$this->radarScoreCount->getExplosivenessScore($userDetail['explosiveness']);
				$userDetail['reachScore'] = $this->radarScoreCount->getReachScore($userDetail['reach']);
				$userDetail['appealScore'] = $this->radarScoreCount->getAppealScore($userDetail['appeal']);
				$userDetail['infPower'] = round((($userDetail['activenessScore']+$userDetail['interactionScore']+$userDetail['explosivenessScore']+$userDetail['reachScore']+$userDetail['appealScore'])/5), 2);

				// 4. Count Recent Data and Rating
				$this->printLog->log("INFO", "Count Recent Data and Rating");

				$userDetail = parent::countRecentAndRating($userDetail);

				$totalApiCall += $userDetail['apiCount'];

				//$firstLoggedAt = $this->normalModel->getLastUserLog($userDetail['id']);

				// 4.1. Count User follower rising Percentage
				$this->printLog->log("INFO", "Count follower rising percentage");
				$userDetail = parent::countFollowerRisingPercentage($userDetail, $recentDay, null);
				
				// 4.2. Count User interaction rising Percentage
				$this->printLog->log("INFO", "Count interaction rising percentage");
				$userDetail = parent::countInteractionRisingPercentage($userDetail, $recentDay, $recent97Day);

				// 4.3 Count engagement rate
				$this->printLog->log("INFO", "Count Engagement rate");
				$userDetail = parent::countEngagementRate($userDetail, $userDetail['interaction'], $userDetail['followerCount']); 
				
				$userDetail['weeklyLoggedAt'] = $recentDay;

				// 5. Insert data to DB
				$insertDBStartTime = time();
				$this->printLog->log("INFO", "Insert to local database");
				

				$infId = $this->insertData($userDetail, $this->type);
				if(!$infId){
					$this->printLog->log("ERROR", "Save to Database fail");
					$failCount++;
					continue;
				}
				$insertType = true;
				$insertDBTime += time()-$insertDBStartTime;
				$totalInsertDBTime += $insertDBTime;

				$this->printLog->log("INFO", "[".$user['igId']."] completed! \nProducing time: ".((time()-$insertStartTime)/60)."(m)\nInsert DB time: ".(time()-$insertDBStartTime)."(s)\n");
			}

			if($user['fan_page_id']!=null){
				$this->printLog->log("INFO","Get FbId '".$user['fan_page_id']."'");
				echo "grab fb";
				print_r($user);
				
				if($totalFollowerCount<5000){

					$this->printLog->log("ERROR", "Total fan count small than 5000");
					
					$this->normalModel->insertErrorLog($user['igId'], 0, "Follower5000", "Total Fan count small than 5000 (".$totalFollowerCount.")");
					
					$failCount++;
					continue;
				}
				
				$name = null;
				if(isset($fbUserDetail['name'])){
					$name = $fbUserDetail['name'];
				}

				$fb_user_id = $this->fbUserModel->insert($user['fan_page_id'], $name);
				echo "\n";
				echo "fb_user_id:".$fb_user_id."\n";
				if($insertType){
					
					//$infId = $this->influencerModel->getInfByIgId($user['igId']);
					$this->printLog->log("INFO","Update Influencer infID:[".$infId."]");
					$this->influencerModel->updateFbId($user['fan_page_id'], $fb_user_id, $infId );
				}else{

					$this->printLog->log("INFO","New Influencer");
					//$user['username'] = $fbUserDetail['username'];
					$user['name'] = $fbUserDetail['name'];
					$user['profilePic'] = $fbUserDetail['profilePic'];

					$user['location_id'] = $user['locationId'];
					$user['identity_id'] = $user['identityId'];
					$user['bio']		 = $fbUserDetail['about'];
					$user['verified'] = true;
					$infId = $this->influencerModel->insert($user, null, $fb_user_id);

					$interests = array();
					if($user['interest1']){
						array_push($interests, $user['interest1']);
					}
					if($user['interest2']){
						array_push($interests, $user['interest2']);
					}
					if($user['interest3']){
						array_push($interests, $user['interest3']);
					}
					
					echo "Insert influencer interest\n";
					foreach ($interests as $key => $interest) {
						$this->influencerModel->insertInfluencerInterest($infId, $interest);
					}
				}

				echo "inf id:".$infId."\n";
				if($infId){
					$updateFbUser = new UpdateFbUser($this->location);
					$updateFbUser->run($infId);


					$updateFbPost = new UpdateFbPost($this->location);
					$updateFbPost->run($infId);
					echo "Success\n";
				}
			}

			if ($user['ytId'] != null) {
				$this->printLog->log("INFO","Get YtId '".$user['ytId']."'");
				echo "grab_fb";
				print_r($user);

				if ($totalFollowerCount < 5000) {
					$this->printLog->log("ERROR", "Total fan count smaller than 5000");
					$failCount++;
					continue;
				}

				$name = null;
				if (isset($ytUserDetail['name'])) {
					$name = $ytUserDetail['name'];
				}

				$yt_user_id = $this->ytUserModel->insert($user['ytId'], $name);
				if ($insertType) {
					$this->printLog->log("INFO","Update Influencer infID:[".$infId."]");
					$this->influencerModel->updateYtId($user['ytId'], $yt_user_id, $infId);
				}
				else {
					$this->printLog->log("INFO","New Influencer");
					$user['name'] = $ytUserDetail['name'];
					$user['profilePic'] = $ytUserDetail['profilePic'];

					$user['location_id'] = $user['locationId'];
					$user['identity_id'] = $user['identityId'];
					$user['bio'] = $ytUserDetail['bio'];
					$user['verified'] = true;
					$infId = $this->influencerModel->insert($user, null, $yt_user_id);

					$interests = array();
					if ($user['interest1']) {
						array_push($interests, $user['interest1']);
					}
					if ($user['interest2']) {
						array_push($interests, $user['interest2']);
					}
					if ($user['interest3']) {
						array_push($interests, $user['interest3']);
					}

					echo "Insert Influencer Interest\n";
					foreach ($interests as $key => $interest) {
						$this->influencerModel->insertInfluencerInterest($infId, $interest);
					}
				}

				echo "inf id:" .$infId. "\n";
				if ($infId) {
					$updateYtUser = new UpdateYtUser();
					$updateYtUser->run($infId);

					$updateYtVideo = new UpdateYtVideo();
					$updateYtVideo->run($infId);
					echo "Success\n";
				}

			}

			//if($this->type == self::NEW_REGISTER){
			$this->updateUserStatus($user);
			//}
			$this->printLog->log("INFO","Send email to [".$user['email']."]");
			$this->printLog->log("INFO","locationId:[".$user['locationId']."]");
			$this->printLog->log("INFO","username:[".$user['username']."]");
			$this->printLog->log("INFO","infId:[".$infId."]");
			$result = $this->WebAPI->sendEmailForInfluencerSuccess($user['locationId'],$user['email'],$user['username'],$infId,'e56897ad805b9f6cfe964af97d2cee97');
			$this->printLog->log("INFO",json_encode($result));
			if(isset($result->type)){
				$this->printLog->log("INFO","Success send email [".$user['email']."]");
				echo "Success\n";
			}else{
				$this->printLog->log("INFO","Fail send email [".$user['email']."]");
				echo "Fail\n";
			}
			$this->printLog->log("INFO","\n===================\n");

		}
		
		//Log start time, end time, error num, success num, repeat num. 
		$this->printLog->log("SUCCESS", "Grab new data completed! \nsuccess row: ".(count($popUsersData)-$failCount)."\nfail row: ".$failCount."\nStart time is: ".date("Y-m-d h:i:sa", $this->startTimestamp)."\nEnd time is: ".date("Y-m-d h:i:sa", time())."\nProducing time: ".((time()-$this->startTimestamp)/60)."(m)\nCall Api: ".$totalApiCount."\nTimeout for reset rate limits: ".($timeoutForRateLimitCount/60/60)."(h)\nTimeout for reset DB: ".($timeoutForResetDBCount/60)."(m)\nTotal inserst DB time: ".$totalInsertDBTime.".\n");

		return;
	}


	private function insertData($user, $type = null) 
	{
		// 5.1 Insert ig_user data
		echo "Insert ig user\n";
		if(is_null($user['identity_id'])){
			return;
		}
		$ig_user_id = $this->userModel->insert($user);
		echo $ig_user_id."<<<< ig_user_id\n";
		if(!is_numeric($ig_user_id) || $ig_user_id == 0){
			return;
		}
		// 5.2 Insert influencer data
		echo "Insert influencer\n";
		if($this->type == self::NEW_REGISTER){
			$user['verified'] = true;
		}
		$influencer_id = $this->influencerModel->insert($user, $ig_user_id);
		
		if(!is_numeric($influencer_id) || $influencer_id == 0){
			return;
		}
		// 5.2.1 Insert influencer interest data
		$interests = array();
		if($user['interest1']){
			array_push($interests, $user['interest1']);
		}
		if($user['interest2']){
			array_push($interests, $user['interest2']);
		}
		if($user['interest3']){
			array_push($interests, $user['interest3']);
		}
		
		echo "Insert influencer interest\n";
		foreach ($interests as $key => $interest) {
			$this->influencerModel->insertInfluencerInterest($influencer_id, $interest);
		}
		//
		echo "Insert post\n";
		// 5.3 Insert User Media data to server
		foreach ($user['media'] as $key => $media) {
			$this->postModel->insert($ig_user_id, $media);
		}
		
		if($this->type == self::NEW_REGISTER){
			$user['id'] = $ig_user_id; 
        	echo "updatePost List\n";
			$this->updatePostList($user);

        	echo "transfer\n";
			$this->transferInfluencer->transferByinfId($influencer_id);
        	// echo "update new influencer\n";
        	// $sql = "UPDATE new_influencer AS ninf 
		       //  	set 
			      //   	ninf.status = :status, 
			      //   	ninf.updatedAt = :updatedAt
		       //  	where ninf.id = :id";
        	// $this->wdb->query($sql, [':id' => $user['newInfluencerId'], ':status' => 'FULL_COMPLETED', ':updatedAt' => time()]);
    
        	// echo "update user\n";

        	// $sql = "UPDATE user AS user 
		       //  	set 
			      //   	user.type = :type, 
			      //   	user.updatedAt = :updatedAt
		       //  	where user.id = :id";
        	// $this->wdb->query($sql, [':id' => $user['newInfluencerId'], ':type' => 'INFLUENCER', ':updatedAt' => time()]);
		}
		return $influencer_id;
	}

	public function updateUserStatus($user){
		echo "update new influencer\n";
    	$sql = "UPDATE new_influencer AS ninf 
	        	set 
		        	ninf.status = :status, 
		        	ninf.updatedAt = :updatedAt
	        	where ninf.id = :id";
    	$this->wdb->query($sql, [':id' => $user['newInfluencerId'], ':status' => 'FULL_COMPLETED', ':updatedAt' => time()]);

    	echo "update user\n";

    	$sql = "UPDATE user AS user 
	        	set 
		        	user.type = :type, 
		        	user.updatedAt = :updatedAt
	        	where user.id = :id";
    	$this->wdb->query($sql, [':id' => $user['newInfluencerId'], ':type' => 'INFLUENCER', ':updatedAt' => time()]);
	}

	public function updatePostList($user)
	{
		// 5.1 remove user popular and latest list
		$this->postModel->clearUserPostList($user['id']);
		if(isset($user['popularPosts'])){
			foreach ($user['popularPosts'] as $key => $post) {
				$this->postModel->updatePopPostList($user['id'], $user['location_id'], $post, $user['postListUpdatedAt']);
			}
		}
		if(isset($user['latestPosts'])){
			foreach ($user['latestPosts'] as $post) {

				$this->postModel->updateLatestPostList($user['id'], $user['location_id'], $post ,$user['postListUpdatedAt']);
			}
		}
	}
}	
$location = null;
$type = null;
if(isset($argv[1])){
	$location = $argv[1]; //HK TW
}
if(isset($argv[2])){
	$type = $argv[2]; //HK TW
}
// HK newInfluencer
$newUser = new webNewUser($location, $type);
$newUser->run();

?>