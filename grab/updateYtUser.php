<?php
require dirname(dirname(__FILE__)). '/vendor/autoload.php';

if( !class_exists('Core') ) {
	require dirname(__FILE__) . '/core.php';
}
if( !class_exists('PrintLog') ) {
	require dirname(dirname(__FILE__)). '/utils/printLog.php';
}
if( !class_exists('YtUser') ) {
	require dirname(dirname(__FILE__)). '/model/ytUser.php';
}
if( !class_exists('YtVideo') ) {
	require dirname(dirname(__FILE__)). '/model/ytVideo.php';
}
if( !class_exists('Influencer') ) {
	require dirname(dirname(__FILE__)). '/model/influencer.php';
}
if( !class_exists('NormalModel') ) {
	require dirname(dirname(__FILE__)). '/model/normal.php';
}
if( !class_exists('YoutubeApi') ) {
	require dirname(dirname(__FILE__)). '/utils/youtubeAPI.php';
}
if( !class_exists('TransferInfluencerYt') ) {
	require dirname(dirname(__FILE__)). '/transferYt/transferInfluencer.php';
}
if( !class_exists('RadarScoreCount') ) {
	require dirname(dirname(__FILE__)). '/utils/radarScoreCount.php';
}
if( !class_exists('GenTopInfluencer') ) {
	require dirname(__FILE__). '/genTopInfluencer.php';
}

class UpdateYtUser extends Core
{
    const RECENT_DAY = 7;
	const RECENT_97DAY = 97;
	private $postModel = null;
	private $userModel = null;
	private $influencerModel = null;
	private $normalModel = null;

	private $print = null;
	private $youtubeApi = null;

    const INSERT_TYPE = "NEW_YT_USER";
    
    public function __construct($location = null)
    {
        parent::__construct($location);

        $this->location = $location;
        $this->normalModel = new NormalModel;
        $this->userModel = new YtUser;
        $this->postModel = new YtVideo;
        $this->influencerModel = new Influencer;
        $this->printLog = new PrintLog("updateYtUser", $location);
        $this->radarScoreCount = new RadarScoreCount();
        $this->youtubeApi = new YoutubeApi();
    }

    public function run($infId) {
        $this->recentDay = time() -(24*60*60*self::RECENT_DAY);
        $this->recent97Day = time()-(24*60*60*self::RECENT_97DAY);

        //clear instert error log
        $this->normalModel->clearYtInsertErrorLog();
        $this->updateUserStartTime = time();
        $this->successCount = 0;
        $this->failCount = 0;
        
        $infId = null;
        if (!$infId) {
            $this->userModel->fetchAllYtUser($this->locationId, array($this, 'updateUser'));
        } else {
            $this->userModel->fetchYtUserByInfId($infId, array($this, 'updateUser'));
        }

        $this->transferInfluencer = new TransferInfluencerYt($this->location);
        $transferInfluencer->run($infId);

        /* $genTopInfluencer = new GenTopInfluencer($this->location);
        $genTopInfluencer->run();
        $this->printLog->log("INFO", "SUCCESS!\nStart time: ".date("Y-m-d h:i:sa", $this->updateUserStartTime)."\nEnd time: ".date("Y-m-d h:i:sa", time())."\nProducing time: " .((time()-$this->updateUserStartTime)/60). "(m)\n"); */
    }

    public function updateUser($key = 0, $user = null) 
    {
        $userDetail = parent::getYtUserDetail($user);

        if (!$userDetail) {
            $this->printLog->log("ERROR", "Get Yt Api Fail");
            $this->updateYtErrorLog($user['ytId'], 400, 'GET_API_FAILURE', 'Get api failure');
            $this->failCount++;
        }

        $userDetail['inf_id'] = $inf_id;
        $userDetail = parent::getYtUserMediaData($userDetail, $this->updateUserStartTime, "UPDATE_USER");
        $userDetail['old'] = $user;

        $userDetail['activeness'] = parent::countActiveness($userDetail);
		$userDetail['interaction'] = parent::countInteraction($userDetail);
		$userDetail['explosiveness'] = parent::countExplosiveness($userDetail);
		$userDetail['engagement'] = parent::countEngagement($userDetail['interaction'], $userDetail['subscriberCount']);
        $userDetail['reach'] = parent::countReach($userDetail);
        
        $firstLoggedAt = $this->normalModel->getLastUserLog($userDetail['id'], "YT");
        $userDetail = parent::countAppeal($userDetail, $this->updateUserStartTime, $firstLoggedAt);

        $userDetail['activenessScore'] = $this->radarScoreCount->getActivenessScore($userDetail['activeness'],'YT');
		$userDetail['interactionScore'] = $this->radarScoreCount->getInteractionScore($userDetail['interaction'],'YT');
		$userDetail['explosivenessScore'] = $this->radarScoreCount->getExplosivenessScore($userDetail['explosiveness'],'YT');
		$userDetail['reachScore'] = $this->radarScoreCount->getReachScore($userDetail['reach'],'YT');
		$userDetail['appealScore'] = $this->radarScoreCount->getAppealScore($userDetail['appeal'],'YT');

		$userDetail['engagementScore'] = $this->radarScoreCount->getEngagementScore($userDetail['engagement']);
		$userDetail['infPower'] = round((($userDetail['engagementScore']+$userDetail['interactionScore']+$userDetail['explosivenessScore']+$userDetail['reachScore']+$userDetail['appealScore'])/5), 2);
    
        $userDetail = parent::countFollowerRisingPercentage($userDetail, $this->recentDay, $firstLoggedAt, "FB");
		$userDetail = parent::countInteractionRisingPercentage($userDetail, $this->recentDay, $this->recent97Day, "FB");
		$userDetail = parent::countEngagementRate($userDetail, $userDetail['oldInteraction'], $userDetail['fanCount']); 

        $this->successCount++;
        $this->saveUpdatedUser($userDetail,	$this->updateUserStartTime);
		$this->transferInfluencer->run($inf_id);
    }

    public function saveUpdatedUser($user, $startTime) 
    {
        $this->printLog->log("INFO", "Update Yt User");
        $this->userModel->update($user);
        $this->printLog->log("INFO", "Update Yt Post");
        $this->saveUserPost($user, $startTime);
        $this->printLog->log("INFO", "Update Yt Post Log");
        $this->userModel->updateUserLog($user);
    }

    public function saveUserPost($user, $startTime)
    {
        $recentDay = $startTime-(24*60*60*97);

		$userOldPost = $this->postModel->getPostByYtId($user['id'], $recentDay);
		
        $userOldPostCount = count($userOldPost);
        
        foreach ($user['media'] as $key => $newMedia) {
            if ($userOldPostCount != 0) {
                foreach ($userOldPost as $old_key => $oldMedia) {
                    if ($oldMedia['yt_post_id'] == $newMedia['yt_post_id']) {
                        $this->postModel->update($oldMedia['id'], $newMedia);
                        continue 2;
                    } elseif ($old_key == ($userOldPostCount-1)) {
                        $this->postModel->insert($user['id'], $newMedia);
                    }
                }
            } else {
                $this->postModel->insert($user['id'], $newMedia);
            }
        }
    }
}
?>