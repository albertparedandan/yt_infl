<?php
/**
*	Generate top influncer per day
**/ 
if( !class_exists('PrintLog') ) {
	require dirname(dirname(__FILE__)). '/utils/printLog.php';
}
if( !class_exists('Influencer') ) {
	require dirname(dirname(__FILE__)). '/model/influencer.php';
}
if( !class_exists('Post') ) {
	require dirname(dirname(__FILE__)). '/model/igPost.php';
}
if (!class_exists('ytVideo')) {
	require dirname(dirname(__FILE__)). '/model/ytVideo.php';
}
if( !class_exists('Core') ) {
	require dirname(__FILE__) . '/core.php';
}
if( !class_exists('transferInfluencer') ) {
	require dirname(dirname(__FILE__)). '/transfer/transferInfluencer.php';
}
if( !class_exists('TransferInfluencerFb') ) {
	require dirname(dirname(__FILE__)). '/transferFb/transferInfluencer.php';
}
if (!class_exists('transferInfluencerYt')) {
	require dirname(dirname(__FILE__)). '/transferYt/transferInfluencer.php';
}
class GenTopPost extends Core
{
	const LIMIT_NUMBER = 27; 
	private $postModel = null; 
	private $influencerModel = null;
	public $locationId = null;
	public $printLog;
	public $type = null;

	public function __construct($location = null, $type = null)
	{
		parent::__construct($location);
		$this->postModel 		= new Post;
		$this->influencerModel 	= new Influencer;
		$this->location = $location;
		$this->type = $type;
		if($type == "FB"){
			$this->printLog  = new PrintLog("genTopPostFB", $location);
		} elseif ($type == "YT") {
			$this->printLog = new PrintLog("genTopPostYT", $location);
		} else{
			$this->printLog  = new PrintLog("genTopPost", $location);
		}
	}

	public function run($inf_id = null) 
	{
		if(!isset($this->locationId)){
			$this->printLog->log("ERROR", "Location not found!");
			return;
		}
		if($this->type == "FB"){
			//gen all
			$this->printLog->log("INFO", "Generate all type top post");
			$this->influencerModel->genTopPost($this->locationId, self::LIMIT_NUMBER, "FB");
			
			//gen identity
			$this->printLog->log("INFO", "Generate top post by identity");
			foreach ($this->identityList as $identity) {
				//$this->influencerModel->genTopPostByIdentity($this->locationId, $identity['id'], self::LIMIT_NUMBER, "FB");
			}
			//gen category
			$this->printLog->log("INFO", "Generate top post by category FB");
			foreach ($this->categoryList as $cat) {
				//$this->influencerModel->genTopPostByCategory($this->locationId, $cat['id'], self::LIMIT_NUMBER, "FB");
			}
			$transferInfluencer = new TransferInfluencerFb($this->location);
			$transferInfluencer->transferTopPost($inf_id);
		} elseif ($this->type == "YT") {
			$this->influencerModel->genTopPost($this->locationId, self::LIMIT_NUMBER, "YT");
			$transferInfluencer = new transferInfluencerYt($this->location);
			$transferInfluencer->transferTopPost($inf_id);
		} else{
			echo 'IG';
			//gen all
			$this->printLog->log("INFO", "Generate all type top post");
			$this->influencerModel->genTopPost($this->locationId, self::LIMIT_NUMBER);

			//gen identity
			$this->printLog->log("INFO", "Generate top post by identity");
			// foreach ($this->identityList as $identity) {
			// 	echo "identity\n";
			// 	//$this->influencerModel->genTopPostByIdentity($this->locationId, $identity['id'], self::LIMIT_NUMBER);
			// }
			//gen category
			$this->printLog->log("INFO", "Generate top post by category IG");
			// foreach ($this->categoryList as $cat) {
			// 	//$this->influencerModel->genTopPostByCategory($this->locationId, $cat['id'], self::LIMIT_NUMBER);
			// }
			$transferInfluencer = new TransferInfluencer($this->location);
			echo $inf_id."\n";
			$transferInfluencer->transferTopPost($inf_id);
		}

		$this->printLog->log("INFO", "Generate top post completed!\n\n");
	
		//$this->postModel->clearTopPost();
		// //gen instagram Top Post
		// foreach ($this->locationList as $location) {
		// 	//gen all
		// 	$this->influencerModel->genIgTopPost($location['id'], self::LIMIT_NUMBER);
		// 	//gen identity
		// 	foreach ($this->identityList as $identity) {
		// 		$this->influencerModel->genIgTopPostByIdentity($location['id'], $identity['id'], self::LIMIT_NUMBER);
		// 	}
		// 	//gen category
		// 	foreach ($this->categoryList as $cat) {
		// 		$this->influencerModel->genIgTopPostByCategory($location['id'], $cat['id'], self::LIMIT_NUMBER);
		// 	}
		// }

	}

}

// $location = null;
// if(isset($argv[1])){
// 	$location = $argv[1]; //HK TW
// }

// $genTopPost = new GenTopPost($location);
// $genTopPost->run();


?>