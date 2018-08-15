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

        $genTopInfluencer = new GenTopInfluencer($this->location);
        $genTopInfluencer->run();
        $this->printLog->log("INFO", "SUCCESS!\nStart time: ".date("Y-m-d h:i:sa", $this->updateUserStartTime)."\nEnd time: ".date("Y-m-d h:i:sa", time())."\nProducing time: " .((time()-$this->updateUserStartTime)/60). "(m)\n");
    }
}
?>jjj