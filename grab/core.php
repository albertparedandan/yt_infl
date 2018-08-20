<?php

/**
 *	Grab NEW instagram data
 **/
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '300');
set_time_limit(0);

if (!class_exists('InstagramAPI')) {
	require dirname(dirname(__FILE__)) . '/utils/InstagramAPI.php';
}
if (!class_exists('FacebookApi')) {
	require dirname(dirname(__FILE__)) . '/utils/facebookAPI.php';
}
if (!class_exists('ScoreCounter')) {
	require dirname(dirname(__FILE__)) . '/utils/scoreCounter.php';
}
if (!class_exists('Post')) {
	require dirname(dirname(__FILE__)) . '/model/igPost.php';
}
if (!class_exists('IgUser')) {
	require dirname(dirname(__FILE__)) . '/model/igUser.php';
}
if (!class_exists('NormalModel')) {
	require dirname(dirname(__FILE__)) . '/model/normal.php';
}
if (!class_exists('FbUser')) {
	require dirname(dirname(__FILE__)) . '/model/fbUser.php';
}
if (!class_exists('YtUser')) {
	require dirname(dirname(__FILE__)) . '/model/ytUser.php';
}
if (!class_exists('YtVideo')) {
	require dirname(dirname(__FILE__)) . '/model/ytVideo.php';
}
if (!class_exists('Emoji')) {
	require dirname(dirname(__FILE__)) . '/library/emoji.php';
}
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
//use PHPExcel;

class Core
{
	const RECENT_NUM = 90;
	const FACEBOOK = 'facebook';
	const INSTAGRAM = 'instagram';
	const YOUTUBE = 'youtube';
	const DATE_FORMAT = 'Y-m-d H:i:s';

	protected $igAPI;
	protected $facebookAPI;
	protected $tyoutubeAPI;
	protected $db;
	public $identityList;
	public $interestList;
	public $locationList;
	public $categoryList;
	public $YtCategoryList;
	public $startTimestamp;
	public $recentDay;
	public $locationId = null;

	private $postModel = null;
	private $userModel = null;
	private $normalModel = null;

	public $facebookPattern = '/(?:(?:http|https):\/\/)?(?:www.)?facebook.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[?\w\-]*\/)?(?:profile.php\?id=(?=\d.*))?([\w\-]*)?/u';
	public $youtubePattern = '/((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/u';
	public $instagramPattern = '/((?:https?:)?\/\/)?((?:www|m)\.)?((?:instagram\.com))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/u';
	public $emailPattern = '/\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/u';

	public function __construct($location = null)
	{

		$this->igAPI = new InstagramAPI($location);
		$this->facebookAPI = new FacebookApi();
		$this->youtubeAPI = new YoutubeApi();
		$this->normalModel = new NormalModel;
		if (!is_null($location) && $location != 'TEST') {
			$this->locationId = $this->getLocationIdByKey($location);
		}
		date_default_timezone_set("Asia/Hong_Kong");
		$this->startTimestamp = time();
		$this->recentDay = $this->startTimestamp - (24 * 60 * 60 * self::RECENT_NUM);
		$this->identityList = $this->getIdentityList();
		$this->interestList = $this->getInterestList();
		$this->locationList = $this->getLocationList();
		$this->categoryList = $this->getCategoryList();
		$this->YtCategoryList = $this->getYtCategoryList();
		$this->userModel = new IgUser;
		$this->postModel = new Post;
		$this->fbUserModel = new FbUser;
		$this->ytUserModel = new YtUser;
		$this->ytVideoModel = new YtVideo;
	}

	public function getLocationIdByKey($key)
	{
		return $this->normalModel->getLocationIdByKey($key);
	}

	public function setLocation($locId)
	{
		$this->normalModel->setLocation($locId);
	}

	public function stripAllSpace($str)
	{
		return str_replace(' ', '', $str);
	}

	public function getInterestList()
	{
		return $this->normalModel->getInterestList();
	}

	public function getCategoryList()
	{
		return $this->normalModel->getCategoryList();
	}

	public function getYtCategoryList()
	{
		return $this->normalModel->getYtCategoryList();
	}

	public function getIdentityList()
	{
		return $this->normalModel->getIdentityList();
	}

	public function getLocationList()
	{
		return $this->normalModel->getLocationList();
	}

	public function getSocialPlatformList()
	{
		return $this->normalModel->getSocialPlatformList();
	}
	public function getAllUser($type = null)
	{
		if ($type) {
			return $this->normalModel->getAllUser(true);
		} else {
			return $this->normalModel->getAllUser(false);
		}
	}
	public function getAllWebUser()
	{
		return $this->normalModel->getWebUser();
	}


	public function getTempUser($location_id = null, $startNum = null, $limitNum = null)
	{
		return $this->normalModel->getTempUser($location_id, $startNum, $limitNum);
	}

	public function getNotExistUser($pop_ig_user)
	{
		$popUsersData = [];
		$isExistedUser = $this->getAllUser("ALL");
		foreach ($pop_ig_user as $user) {

			// 2.1 Check server exist user
			foreach ($isExistedUser as $existedUser) {

				if ($user['igId'] != null && $user['igId'] == $existedUser['igId']) {
					echo $user['igId'] . "\n";
					echo "haveig\n";
					continue 2;
				}
				if (isset($existedUser['fbId']) && $user['fan_page_id'] != null && $user['fan_page_id'] == $existedUser['fbId']) {
					echo "havefb\n";
					continue 2;
				}
				if ((isset($existedUser['ytId'])) && $user['ytId'] == $existedUser['ytId']) {
					echo "haveyt\n";
					continue 2;
				}
			}
			array_push($popUsersData, $user);

		}
		return $popUsersData;
	}

	public function getNotExistFbUser($fd_user)
	{
		$popUsersData = [];
		$isExistedUser = $this->fbUserModel->getAllFbUser($this->locationId);

		foreach ($fd_user as $user) {
			// 2.1 Check server exist user
			foreach ($isExistedUser as $existedUser) {

				if ($user['fbId'] == $existedUser['fbId']) {
					//TODO: Log existed ig user
					continue 2;
				}
			}
			array_push($popUsersData, $user);
		}
		return $popUsersData;
	}

	public function getNotExistYtUser($yt_user)
	{
		$popUsersData = [];
		$isExistedUser = $this->ytUserModel->getAllYtUser($this->locationId);

		foreach ($yt_user as $user) {
			foreach ($isExistedUser as $existedUser) {
				if ($user['ytId'] == $existedUserp['ytId']) {
					continue 2;
				}
			}
			array_push($popUsersData, $user);
		}
		return $popUsersData;
	}

	public function is_url_exist($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($code == 200) {
			$status = true;
		} else {
			$status = false;
		}
		curl_close($ch);
		return $status;
	}

	public function getUserDetail($user, $type = null)
	{

		// 2.2 Grap user data from instagram API
		$user_arr = null;
		$userToken = $this->userModel->getIgUserToken($user['igId']);

		if ($type == "NEW_USER" && $user['igToken']) {
			if ($user['ig_business_id']) {
				$userDataResult = $this->igAPI->getUserDataByGraphApiUserToken($userToken, $user['ig_business_id']);

				$userData = new stdClass();
				$userData->data = new stdClass();
				$userData->data->ig_business_id = $userDataResult->id;
				$userData->data->id = $userDataResult->ig_id;
				$userData->data->bio = isset($userDataResult->biography) ? $userDataResult->biography : '';
				$userData->data->username = $userDataResult->username;
				$userData->data->profile_picture = $userDataResult->profile_picture_url;
				$userData->data->full_name = isset($userDataResult->name) ? $userDataResult->name : '';
				$userData->data->username = $userDataResult->username;
				$userData->data->website = isset($userDataResult->website) ? $userDataResult->website : '';
				$userData->data->counts = new stdClass();
				$userData->data->counts->followed_by = $userDataResult->followers_count;
				$userData->data->counts->follows = $userDataResult->follows_count;
				$userData->data->counts->media = $userDataResult->media_count;

			} else {
				$userData = $this->igAPI->getUserDataByUserToken($userToken);
			}
		} else if ($userToken) {
			$userData = $this->igAPI->getUserDataByUserToken($userToken);
		} else {
			$userData = $this->igAPI->getUserData($user['igId'], $type);
		}
		$this->normalModel->insertApiLog($user['igId'], json_encode($userData));
		if (isset($userData->data)) {

			$userData = $userData->data;
			$profilePic = $userData->profile_picture;
			//$profilePic320 = str_replace("/s150x150/","/s320x320/",$userData->profile_picture);
		
			// if( $this->is_url_exist($profilePic320)){
			// 	$profilePic = $profilePic320;
			// }

			//print_r($user);

			$user_arr = array(
				'igId' => $userData->id,
				'username' => $userData->username,
				'bio' => addslashes($userData->bio),
				'website' => urlencode($userData->website),
				'profilePic' => urlencode($profilePic),
				'name' => addslashes(trim($userData->full_name)),
				'followerCount' => $userData->counts->followed_by,
				'followingCount' => $userData->counts->follows,
				'postCount' => $userData->counts->media,
				'apiCount' => 1,
				'emailFromAbout' => '',
				'igToken' => isset($userToken) ? $userToken : null,
				'firstLangId' => isset($user['firstLangId']) ? $user['firstLangId'] : ''
			);
			if ($type == 'NEW_USER') {
				$user_arr['identity_id'] = $user['identityId'];
				$user_arr['interest1'] = $user['interest1'];
				$user_arr['interest2'] = $user['interest2'];
				$user_arr['interest3'] = $user['interest3'];
				$user_arr['location_id'] = $user['locationId'];
				$user_arr['gender'] = $user['gender'];
				$user_arr['isGroup'] = isset($user['isGroup']) ? $user['isGroup'] : '';
				$user_arr['facebook'] = $user['facebook'];
				$user_arr['youtube'] = $user['youtube'];
				$user_arr['twitter'] = isset($user['twitter']) ? $user['twitter'] : '';
				$user_arr['website'] = (isset($user['website']) && $user['website']) ? $user['website'] : $userData->website;
				$user_arr['email'] = $user['email'];
				$user_arr['phone'] = $user['phone'];
				$user_arr['contact_person'] = isset($user['contactPerson']) ? $user['contactPerson'] : '';
				$user_arr['isClaimed'] = null;
				$user_arr['ethnicity'] = isset($user['ethnicity']) ? $user['ethnicity'] : null;

				if (isset($userData->ig_business_id) && $userData->ig_business_id) {
					$user_arr['ig_business_id'] = $userData->ig_business_id;
				}
				if (isset($user['newInfluencerId']) && $user['newInfluencerId']) {
					$user_arr['newInfluencerId'] = $user['newInfluencerId'];
					$user_arr['igToken'] = $user['igToken'];
				}
			} else if ($type == "UPDATE_USER") {
				$user_arr['ig_user_id'] = $user['ig_user_id'];
				$user_arr['inf_id'] = $user['inf_id'];
				$user_arr['id'] = $user['id'];
				$user_arr['old'] = $user;
				$user_arr['tagCount'] = $user['tagCount'];
				$user_arr['likeCount'] = $user['likeCount'];
				$user_arr['commentCount'] = $user['commentCount'];
				$user_arr['firstPostDate'] = $user['firstPostDate'];
				$user_arr['createdAt'] = $user['createdAt'];
				$user_arr['sourceFrom'] = $user['sourceFrom'];
				$user_arr['facebook'] = $user['facebook'];
				$user_arr['youtube'] = $user['youtube'];
				$user_arr['email'] = $user['email'];
				$user_arr['identity_id'] = $user['identityId'];
				$user_arr['location_id'] = $user['locationId'];
				$user_arr['ethnicity'] = isset($user['ethnicity']) ? $user['ethnicity'] : null;
			} else if ($type == "NEW_USER_BY_AUTOMATION") {
				$user_arr['identity_id'] = $user['identityId'];
				$user_arr['location_id'] = $user['locationId'];
				$user_arr['facebook'] = $user['facebook'];
				$user_arr['youtube'] = $user['youtube'];
				$user_arr['website'] = isset($user['external_url']) ? $user['external_url'] : null;
				$user_arr['interests'] = isset($user['interests']) ? $user['interests'] : null;
				$user_arr['email'] = $user['email'];
				$user_arr['gender'] = isset($user['gender']) ? $user['gender'] : null;
				$user_arr['twitter'] = null;
				$user_arr['contact_person'] = null;
				$user_arr['isGroup'] = null;
				$user_arr['isClaimed'] = null;
				$user_arr['phone'] = null;
				$user_arr['ethnicity'] = isset($user['ethnicity']) ? $user['ethnicity'] : null;

			} else {

				$user_arr['ig_user_id'] = $user['ig_user_id'];
				$user_arr['inf_id'] = $user['inf_id'];
				$user_arr['id'] = $user['id'];
				$user_arr['location_id'] = $user['locationId'];
				$user_arr['ethnicity'] = isset($user['ethnicity']) ? $user['ethnicity'] : null;
			}

			return $user_arr;
		}


		//2.2.1
		//$userData = $this->igAPI->getUserDataByUsername($user[''])


		return $userData;
	}

	public function getFbUserDetail($user)
	{

		// 2.2 Grap user data from instagram API
		$user_arr = null;
		$userData = $this->facebookAPI->getUserData($user['fbId']);
		//print_r($userData);
		//$this->normalModel->insertApiLog($user['igId'], json_encode($userData));

		if (isset($userData->id)) {
			$id = null;
			if (isset($user['id'])) {
				$id = $user['id'];
			}
			if (isset($userData->emails)) {
				$email = $userData->emails[0];
			} else if (isset($user->email)) {
				$email = $user->email;
			} else {
				$email = null;
			}
			$user_arr = array(
				'id' => $id,
				'fbId' => $userData->id,
				'coverPic' => (isset($userData->cover) ? $userData->cover->source : null),
				'profilePic' => (isset($userData->picture) ? $userData->picture->data->url : null),
				'name' => addslashes(trim($userData->name)),
				'username' => (isset($userData->username) ? addslashes(trim($userData->username)) : null),
				'category' => addslashes($userData->category),
				'about' => (isset($userData->about) ? $userData->about : null),
				'email' => $email,
				'phone' => (isset($userData->phone) ? $userData->phone : null),
				'website' => (isset($userData->website) ? $userData->website : null),
				'birthday' => (isset($userData->birthday) ? $userData->birthday : null),
				'bio' => (isset($userData->bio) ? addslashes($userData->bio) : null),
				'fanCount' => $userData->fan_count,
				'link' => $userData->link
			);

			return $user_arr;
		} else {
			return null;
		}

		return $userData;
	}

	public function getYtUserDetail($user)
	{
		$user_arr = null;
		$userData = $this->youtubeAPI->getUserDataById($user['ytId']);

		if (isset($userData['ytId'])) {
			$id = null;
			if (isset($user['id'])) {
				$id = $user['id'];
			}
			/* if (isset($userData->emails)) {
				$email = $userData->emails[0];
			}
			else if (isset($user->email)) {
				$email = $user->email;
			} */
			else {
				$email = null;
			}
			$user_arr = array(
				'id' => $id,
				'ytId' => $userData['ytId'],
				'name' => (isset($userData['name'])),
					//'email'				=>(isset($userData->email)),
				'bio' => (isset($userData['bio'])),
				'profilePic' => (isset($userData['profilePic'])),
				'postCount' => (isset($userData['postCount'])),
				'subscriberCount' => (isset($userData['subscriberCount'])),
				'viewCount' => (isset($userData['viewCount'])),
				'publishedAt' => $userData['publishedAt'],
				'country' => (isset($userData['country'])),
				'playlistId' => (isset($userData['playlistId'])),
				'topicDetails' => (isset($userData['topicDetails'])),
				'topicCateg' => (isset($userData['topicCateg']))
			);
			return $user_arr;
		} else {
			return null;
		}
		return $userData;
	}



	// public function getUserMediaData( $user, $recentDay = null, $type = null, $recentDay2 = null ) {

	// 	try {
	// 		$userMedia = $this->igAPI->getUserMediaData($user['igId'], null, $type);
	// 		echo ".";
	// 		if(!isset($user['apiCount'])){
	// 			$user['apiCount'] = 0;
	// 		}
	// 		$user['apiCount']++;
	// 		if( isset($userMedia->meta) && $userMedia->meta->code == 400){
	// 			return $userMedia;
	// 		}
	// 		if( isset($userMedia->code) && $userMedia->code == 429 ){
	// 			return $userMedia;
	// 		}
	// 		$userMediaData = $userMedia->data;

	// 		$hasMoreMedia = false;
	// 		if( $userMedia  && !( isset($recentDay) && $recentDay >= $userMedia->data[count($userMedia->data)-1]->created_time)  ) {
	// 			$hasMoreMedia = true;
	// 		}

	// 		// 3.1 Load more media data
	// 		$countLoadApi = 0;
	// 		while ( $hasMoreMedia ) {
	// 			$userMedia = $this->igAPI->getUserMediaData($user['igId'], $userMedia->pagination->next_max_id, $type);

	// 			$this->normalModel->insertApiPostLog($user['igId'], json_encode($userMedia));
	// 			echo ".";
	// 			$user['apiCount']++;
	// 			if(isset($userMedia->data) && is_array($userMedia->data)){
	// 				$userMediaData = array_merge($userMediaData, $userMedia->data);
	// 			}
	// 			$countLoadApi = $countLoadApi +1;

	// 			if(isset($userMedia->pagination->next_max_id)){
	// 				$hasMoreMedia = true;
	// 				if( isset($recentDay) && $recentDay >= $userMedia->data[count($userMedia->data)-1]->created_time ) {
	// 					$hasMoreMedia = false;
 // 					}
	// 			}else{

	// 				$hasMoreMedia = false;
	// 			}
	// 		}
	// 		//$this->normalModel->insertApiPostLog($user['igId'], json_encode($userMedia));
	// 		//echo $countLoadApi.' <--- </br>';


	// 		$user['media'] = $userMediaData;

	// 		if(isset($recentDay)){
	// 			$recentPosts = [];

	// 			$latestPosts = [];
	// 			$popularPosts = [];
	// 			foreach ($userMediaData as $media) {
	// 				if($type === Post::GEN_POST_LIST){

	// 					if(count($latestPosts) < 10){
	// 						$media->score = $media->comments->count + $media->likes->count;
	// 						array_push($latestPosts, (array) $media);

	// 					}
	// 					if(count($popularPosts) < 10 || $recentDay2 < $media->created_time){

	// 						$media->score = $media->comments->count + $media->likes->count;
	// 						array_push($popularPosts, (array) $media);
	// 					}
	// 					array_push($recentPosts, $media);

	// 				}else if($recentDay < $media->created_time){
	// 					//echo  $media->created_time.'</br>';
	// 					array_push($recentPosts, $media);
	// 				}

	// 			}
	// 			if($type === Post::GEN_POST_LIST){

	// 				//Sort popular post by score
	// 				usort($popularPosts, function($item1, $item2){
	// 				    if ($item1['score'] == $item2['score']) return 0;
	// 				    return ($item1['score'] < $item2['score']) ? 1 : -1;
	// 				});


	// 				$user['latestPosts'] = $latestPosts;
	// 				$user['popularPosts'] = array_slice($popularPosts,0,10);
	// 				$user['postListUpdatedAt'] = time();
	// 				$user['media97day'] = $recentPosts;
	// 			}else{
	// 				$user['media'] = $recentPosts;
	// 			}

	// 		}else if($type === "NEW_USER"){
	// 			$recentPosts = [];
	// 			$latestPosts = [];
	// 			$popularPosts = [];

	// 			$recentDay = time()-(24*60*60*7);
	// 			$recent97Day = time()-(24*60*60*97);
	// 			foreach ($userMediaData as $media) {

	// 				if(count($latestPosts) < 10){
	// 					$media->score = $media->comments->count + $media->likes->count;
	// 					array_push($latestPosts, (array) $media);

	// 				}
	// 				if(count($popularPosts) < 10 || $recentDay < $media->created_time){

	// 					$media->score = $media->comments->count + $media->likes->count;
	// 					array_push($popularPosts, (array) $media);
	// 				}
	// 				if($recent97Day < $media->created_time){
	// 					array_push($recentPosts, $media);
	// 				}
	// 			}

	// 			//Sort popular post by score
	// 			usort($popularPosts, function($item1, $item2){
	// 			    if ($item1['score'] == $item2['score']) return 0;
	// 			    return ($item1['score'] < $item2['score']) ? 1 : -1;
	// 			});


	// 			$user['latestPosts'] = $latestPosts;
	// 			$user['popularPosts'] = array_slice($popularPosts,0,10);
	// 			$user['postListUpdatedAt'] = time();
	// 			$user['media97day'] = $recentPosts;
	// 		}
	// 	} catch (Exception $e) {
	//         print $e->getMessage();
	//     }
	//
	// 	return $user;
	// }

	public function getPattern()
	{
		$a = '\xc0-\xd6' . '\xd8-\xf6' . '\xf8-\xff' . '\x{0100}-\x{024f}' . '\x{0253}-\x{0254}' . '\x{0256}-\x{0257}' . '\x{0259}' . '\x{025b}' . '\x{0263}' . '\x{0268}' . '\x{026f}' . '\x{0272}' . '\x{0289}' . '\x{028b}' . '\x{02bb}' . '\x{0300}-\x{036f}' . '\x{1e00}-\x{1eff}';
		$b = '\x{0400}-\x{04ff}' . '\x{0500}-\x{0527}' . '\x{2de0}-\x{2dff}' . '\x{a640}-\x{a69f}' . '\x{0591}-\x{05bf}' . '\x{05c1}-\x{05c2}' . '\x{05c4}-\x{05c5}' . '\x{05c7}' . '\x{05d0}-\x{05ea}' . '\x{05f0}-\x{05f4}' . '\x{fb12}-\x{fb28}' . '\x{fb2a}-\x{fb36}' . '\x{fb38}-\x{fb3c}' . '\x{fb3e}' . '\x{fb40}-\x{fb41}' . '\x{fb43}-\x{fb44}' . '\x{fb46}-\x{fb4f}' . '\x{0610}-\x{061a}' . '\x{0620}-\x{065f}' . '\x{066e}-\x{06d3}' . '\x{06d5}-\x{06dc}' . '\x{06de}-\x{06e8}' . '\x{06ea}-\x{06ef}' . '\x{06fa}-\x{06fc}' . '\x{06ff}' . '\x{0750}-\x{077f}' . '\x{08a0}' . '\x{08a2}-\x{08ac}' . '\x{08e4}-\x{08fe}' . '\x{fb50}-\x{fbb1}' . '\x{fbd3}-\x{fd3d}' . '\x{fd50}-\x{fd8f}' . '\x{fd92}-\x{fdc7}' . '\x{fdf0}-\x{fdfb}' . '\x{fe70}-\x{fe74}' . '\x{fe76}-\x{fefc}' . '\x{200c}-\x{200c}' . '\x{0e01}-\x{0e3a}' . '\x{0e40}-\x{0e4e}' . '\x{1100}-\x{11ff}' . '\x{3130}-\x{3185}' . '\x{A960}-\x{A97F}' . '\x{AC00}-\x{D7AF}' . '\x{D7B0}-\x{D7FF}' . '\x{FFA1}-\x{FFDC}';
		$c = '\x{30A1}-\x{30FA}\x{30FC}-\x{30FE}' . '\x{FF66}-\x{FF9F}' . '\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}' . '\x{FF41}-\x{FF5A}' . '\x{3041}-\x{3096}\x{3099}-\x{309E}' . '\x{3400}-\x{4DBF}' . '\x{4E00}-\x{9FFF}' . $this->unichr(173824) . '-' . $this->unichr(177983) . $this->unichr(177984) . '-' . $this->unichr(178207) . $this->unichr(194560) . '-' . $this->unichr(195103) . '\x{3003}\x{3005}\x{303B}';
		$d = $a . $b . $c;
		$e = '\x{0041}-\x{005A}\x{0061}-\x{007A}\x{00AA}\x{00B5}\x{00BA}\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}' . '\x{00F8}-\x{0241}\x{0250}-\x{02C1}\x{02C6}-\x{02D1}\x{02E0}-\x{02E4}\x{02EE}\x{037A}\x{0386}' . '\x{0388}-\x{038A}\x{038C}\x{038E}-\x{03A1}\x{03A3}-\x{03CE}\x{03D0}-\x{03F5}\x{03F7}-\x{0481}' . '\x{048A}-\x{04CE}\x{04D0}-\x{04F9}\x{0500}-\x{050F}\x{0531}-\x{0556}\x{0559}\x{0561}-\x{0587}' . '\x{05D0}-\x{05EA}\x{05F0}-\x{05F2}\x{0621}-\x{063A}\x{0640}-\x{064A}\x{066E}-\x{066F}' . '\x{0671}-\x{06D3}\x{06D5}\x{06E5}-\x{06E6}\x{06EE}-\x{06EF}\x{06FA}-\x{06FC}\x{06FF}\x{0710}' . '\x{0712}-\x{072F}\x{074D}-\x{076D}\x{0780}-\x{07A5}\x{07B1}\x{0904}-\x{0939}\x{093D}\x{0950}' . '\x{0958}-\x{0961}\x{097D}\x{0985}-\x{098C}\x{098F}-\x{0990}\x{0993}-\x{09A8}\x{09AA}-\x{09B0}' . '\x{09B2}\x{09B6}-\x{09B9}\x{09BD}\x{09CE}\x{09DC}-\x{09DD}\x{09DF}-\x{09E1}\x{09F0}-\x{09F1}' . '\x{0A05}-\x{0A0A}\x{0A0F}-\x{0A10}\x{0A13}-\x{0A28}\x{0A2A}-\x{0A30}\x{0A32}-\x{0A33}' . '\x{0A35}-\x{0A36}\x{0A38}-\x{0A39}\x{0A59}-\x{0A5C}\x{0A5E}\x{0A72}-\x{0A74}\x{0A85}-\x{0A8D}' . '\x{0A8F}-\x{0A91}\x{0A93}-\x{0AA8}\x{0AAA}-\x{0AB0}\x{0AB2}-\x{0AB3}\x{0AB5}-\x{0AB9}\x{0ABD}' . '\x{0AD0}\x{0AE0}-\x{0AE1}\x{0B05}-\x{0B0C}\x{0B0F}-\x{0B10}\x{0B13}-\x{0B28}\x{0B2A}-\x{0B30}' . '\x{0B32}-\x{0B33}\x{0B35}-\x{0B39}\x{0B3D}\x{0B5C}-\x{0B5D}\x{0B5F}-\x{0B61}\x{0B71}\x{0B83}' . '\x{0B85}-\x{0B8A}\x{0B8E}-\x{0B90}\x{0B92}-\x{0B95}\x{0B99}-\x{0B9A}\x{0B9C}\x{0B9E}-\x{0B9F}' . '\x{0BA3}-\x{0BA4}\x{0BA8}-\x{0BAA}\x{0BAE}-\x{0BB9}\x{0C05}-\x{0C0C}\x{0C0E}-\x{0C10}' . '\x{0C12}-\x{0C28}\x{0C2A}-\x{0C33}\x{0C35}-\x{0C39}\x{0C60}-\x{0C61}\x{0C85}-\x{0C8C}' . '\x{0C8E}-\x{0C90}\x{0C92}-\x{0CA8}\x{0CAA}-\x{0CB3}\x{0CB5}-\x{0CB9}\x{0CBD}\x{0CDE}' . '\x{0CE0}-\x{0CE1}\x{0D05}-\x{0D0C}\x{0D0E}-\x{0D10}\x{0D12}-\x{0D28}\x{0D2A}-\x{0D39}' . '\x{0D60}-\x{0D61}\x{0D85}-\x{0D96}\x{0D9A}-\x{0DB1}\x{0DB3}-\x{0DBB}\x{0DBD}\x{0DC0}-\x{0DC6}' . '\x{0E01}-\x{0E30}\x{0E32}-\x{0E33}\x{0E40}-\x{0E46}\x{0E81}-\x{0E82}\x{0E84}\x{0E87}-\x{0E88}' . '\x{0E8A}\x{0E8D}\x{0E94}-\x{0E97}\x{0E99}-\x{0E9F}\x{0EA1}-\x{0EA3}\x{0EA5}\x{0EA7}' . '\x{0EAA}-\x{0EAB}\x{0EAD}-\x{0EB0}\x{0EB2}-\x{0EB3}\x{0EBD}\x{0EC0}-\x{0EC4}\x{0EC6}' . '\x{0EDC}-\x{0EDD}\x{0F00}\x{0F40}-\x{0F47}\x{0F49}-\x{0F6A}\x{0F88}-\x{0F8B}\x{1000}-\x{1021}' . '\x{1023}-\x{1027}\x{1029}-\x{102A}\x{1050}-\x{1055}\x{10A0}-\x{10C5}\x{10D0}-\x{10FA}\x{10FC}' . '\x{1100}-\x{1159}\x{115F}-\x{11A2}\x{11A8}-\x{11F9}\x{1200}-\x{1248}\x{124A}-\x{124D}' . '\x{1250}-\x{1256}\x{1258}\x{125A}-\x{125D}\x{1260}-\x{1288}\x{128A}-\x{128D}\x{1290}-\x{12B0}' . '\x{12B2}-\x{12B5}\x{12B8}-\x{12BE}\x{12C0}\x{12C2}-\x{12C5}\x{12C8}-\x{12D6}\x{12D8}-\x{1310}' . '\x{1312}-\x{1315}\x{1318}-\x{135A}\x{1380}-\x{138F}\x{13A0}-\x{13F4}\x{1401}-\x{166C}' . '\x{166F}-\x{1676}\x{1681}-\x{169A}\x{16A0}-\x{16EA}\x{1700}-\x{170C}\x{170E}-\x{1711}' . '\x{1720}-\x{1731}\x{1740}-\x{1751}\x{1760}-\x{176C}\x{176E}-\x{1770}\x{1780}-\x{17B3}\x{17D7}' . '\x{17DC}\x{1820}-\x{1877}\x{1880}-\x{18A8}\x{1900}-\x{191C}\x{1950}-\x{196D}\x{1970}-\x{1974}' . '\x{1980}-\x{19A9}\x{19C1}-\x{19C7}\x{1A00}-\x{1A16}\x{1D00}-\x{1DBF}\x{1E00}-\x{1E9B}' . '\x{1EA0}-\x{1EF9}\x{1F00}-\x{1F15}\x{1F18}-\x{1F1D}\x{1F20}-\x{1F45}\x{1F48}-\x{1F4D}' . '\x{1F50}-\x{1F57}\x{1F59}\x{1F5B}\x{1F5D}\x{1F5F}-\x{1F7D}\x{1F80}-\x{1FB4}\x{1FB6}-\x{1FBC}' . '\x{1FBE}\x{1FC2}-\x{1FC4}\x{1FC6}-\x{1FCC}\x{1FD0}-\x{1FD3}\x{1FD6}-\x{1FDB}\x{1FE0}-\x{1FEC}' . '\x{1FF2}-\x{1FF4}\x{1FF6}-\x{1FFC}\x{2071}\x{207F}\x{2090}-\x{2094}\x{2102}\x{2107}' . '\x{210A}-\x{2113}\x{2115}\x{2119}-\x{211D}\x{2124}\x{2126}\x{2128}\x{212A}-\x{212D}' . '\x{212F}-\x{2131}\x{2133}-\x{2139}\x{213C}-\x{213F}\x{2145}-\x{2149}\x{2C00}-\x{2C2E}' . '\x{2C30}-\x{2C5E}\x{2C80}-\x{2CE4}\x{2D00}-\x{2D25}\x{2D30}-\x{2D65}\x{2D6F}\x{2D80}-\x{2D96}' . '\x{2DA0}-\x{2DA6}\x{2DA8}-\x{2DAE}\x{2DB0}-\x{2DB6}\x{2DB8}-\x{2DBE}\x{2DC0}-\x{2DC6}' . '\x{2DC8}-\x{2DCE}\x{2DD0}-\x{2DD6}\x{2DD8}-\x{2DDE}\x{3005}-\x{3006}\x{3031}-\x{3035}' . '\x{303B}-\x{303C}\x{3041}-\x{3096}\x{309D}-\x{309F}\x{30A1}-\x{30FA}\x{30FC}-\x{30FF}' . '\x{3105}-\x{312C}\x{3131}-\x{318E}\x{31A0}-\x{31B7}\x{31F0}-\x{31FF}\x{3400}-\x{4DB5}' . '\x{4E00}-\x{9FBB}\x{A000}-\x{A48C}\x{A800}-\x{A801}\x{A803}-\x{A805}\x{A807}-\x{A80A}' . '\x{A80C}-\x{A822}\x{AC00}-\x{D7A3}\x{F900}-\x{FA2D}\x{FA30}-\x{FA6A}\x{FA70}-\x{FAD9}' . '\x{FB00}-\x{FB06}\x{FB13}-\x{FB17}\x{FB1D}\x{FB1F}-\x{FB28}\x{FB2A}-\x{FB36}\x{FB38}-\x{FB3C}' . '\x{FB3E}\x{FB40}-\x{FB41}\x{FB43}-\x{FB44}\x{FB46}-\x{FBB1}\x{FBD3}-\x{FD3D}\x{FD50}-\x{FD8F}' . '\x{FD92}-\x{FDC7}\x{FDF0}-\x{FDFB}\x{FE70}-\x{FE74}\x{FE76}-\x{FEFC}\x{FF21}-\x{FF3A}' . '\x{FF41}-\x{FF5A}\x{FF66}-\x{FFBE}\x{FFC2}-\x{FFC7}\x{FFCA}-\x{FFCF}\x{FFD2}-\x{FFD7}' . '\x{FFDA}-\x{FFDC}';
		$f = '\x{0300}-\x{036F}\x{0483}-\x{0486}\x{0591}-\x{05B9}\x{05BB}-\x{05BD}\x{05BF}' . '\x{05C1}-\x{05C2}\x{05C4}-\x{05C5}\x{05C7}\x{0610}-\x{0615}\x{064B}-\x{065E}\x{0670}' . '\x{06D6}-\x{06DC}\x{06DF}-\x{06E4}\x{06E7}-\x{06E8}\x{06EA}-\x{06ED}\x{0711}\x{0730}-\x{074A}' . '\x{07A6}-\x{07B0}\x{0901}-\x{0903}\x{093C}\x{093E}-\x{094D}\x{0951}-\x{0954}\x{0962}-\x{0963}' . '\x{0981}-\x{0983}\x{09BC}\x{09BE}-\x{09C4}\x{09C7}-\x{09C8}\x{09CB}-\x{09CD}\x{09D7}' . '\x{09E2}-\x{09E3}\x{0A01}-\x{0A03}\x{0A3C}\x{0A3E}-\x{0A42}\x{0A47}-\x{0A48}\x{0A4B}-\x{0A4D}' . '\x{0A70}-\x{0A71}\x{0A81}-\x{0A83}\x{0ABC}\x{0ABE}-\x{0AC5}\x{0AC7}-\x{0AC9}\x{0ACB}-\x{0ACD}' . '\x{0AE2}-\x{0AE3}\x{0B01}-\x{0B03}\x{0B3C}\x{0B3E}-\x{0B43}\x{0B47}-\x{0B48}\x{0B4B}-\x{0B4D}' . '\x{0B56}-\x{0B57}\x{0B82}\x{0BBE}-\x{0BC2}\x{0BC6}-\x{0BC8}\x{0BCA}-\x{0BCD}\x{0BD7}' . '\x{0C01}-\x{0C03}\x{0C3E}-\x{0C44}\x{0C46}-\x{0C48}\x{0C4A}-\x{0C4D}\x{0C55}-\x{0C56}' . '\x{0C82}-\x{0C83}\x{0CBC}\x{0CBE}-\x{0CC4}\x{0CC6}-\x{0CC8}\x{0CCA}-\x{0CCD}\x{0CD5}-\x{0CD6}' . '\x{0D02}-\x{0D03}\x{0D3E}-\x{0D43}\x{0D46}-\x{0D48}\x{0D4A}-\x{0D4D}\x{0D57}\x{0D82}-\x{0D83}' . '\x{0DCA}\x{0DCF}-\x{0DD4}\x{0DD6}\x{0DD8}-\x{0DDF}\x{0DF2}-\x{0DF3}\x{0E31}\x{0E34}-\x{0E3A}' . '\x{0E47}-\x{0E4E}\x{0EB1}\x{0EB4}-\x{0EB9}\x{0EBB}-\x{0EBC}\x{0EC8}-\x{0ECD}\x{0F18}-\x{0F19}' . '\x{0F35}\x{0F37}\x{0F39}\x{0F3E}-\x{0F3F}\x{0F71}-\x{0F84}\x{0F86}-\x{0F87}\x{0F90}-\x{0F97}' . '\x{0F99}-\x{0FBC}\x{0FC6}\x{102C}-\x{1032}\x{1036}-\x{1039}\x{1056}-\x{1059}\x{135F}' . '\x{1712}-\x{1714}\x{1732}-\x{1734}\x{1752}-\x{1753}\x{1772}-\x{1773}\x{17B6}-\x{17D3}\x{17DD}' . '\x{180B}-\x{180D}\x{18A9}\x{1920}-\x{192B}\x{1930}-\x{193B}\x{19B0}-\x{19C0}\x{19C8}-\x{19C9}' . '\x{1A17}-\x{1A1B}\x{1DC0}-\x{1DC3}\x{20D0}-\x{20DC}\x{20E1}\x{20E5}-\x{20EB}\x{302A}-\x{302F}' . '\x{3099}-\x{309A}\x{A802}\x{A806}\x{A80B}\x{A823}-\x{A827}\x{FB1E}\x{FE00}-\x{FE0F}' . '\x{FE20}-\x{FE23}';
		$g = '\x{0030}-\x{0039}\x{0660}-\x{0669}\x{06F0}-\x{06F9}\x{0966}-\x{096F}\x{09E6}-\x{09EF}' . '\x{0A66}-\x{0A6F}\x{0AE6}-\x{0AEF}\x{0B66}-\x{0B6F}\x{0BE6}-\x{0BEF}\x{0C66}-\x{0C6F}' . '\x{0CE6}-\x{0CEF}\x{0D66}-\x{0D6F}\x{0E50}-\x{0E59}\x{0ED0}-\x{0ED9}\x{0F20}-\x{0F29}' . '\x{1040}-\x{1049}\x{17E0}-\x{17E9}\x{1810}-\x{1819}\x{1946}-\x{194F}\x{19D0}-\x{19D9}' . '\x{FF10}-\x{FF19}';
		$h = $e . $f . $d;
		$i = $g . '_';
		$j = $h . $i;
		$k = '[' . $h . ']';
		$l = '[' . $j . ']';
		$m = '^|$|[^&\/' . $j . ']';
		$n = '[#\x{FF03}]';
		$result = '(' . $m . ')(' . $n . ')(' . $l . '*' . $k . $l . '*)';
		return $result;
	}
	public function unichr($u)
	{
		return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
	}

	public function getUserMediaData($user, $updateTime = null, $type = null, $date = null)
	{

		if ($type == Post::GEN_POST_LIST) {
			$recentDay = $updateTime - (24 * 60 * 60 * 97);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			$recent30Day = $updateTime - (24 * 60 * 60 * 30);
			$recent90Day = $updateTime - (24 * 60 * 60 * 90);
			$recent97Day = $updateTime - (24 * 60 * 60 * 97);

		} else if ($type === "NEW_USER" || $type === "NEW_USER_BY_AUTOMATION") {
			$recentDay = $updateTime - (24 * 60 * 60 * 97);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			$recent30Day = $updateTime - (24 * 60 * 60 * 30);
			$recent90Day = $updateTime - (24 * 60 * 60 * 90);
			$recent97Day = $updateTime - (24 * 60 * 60 * 97);

		} else {
			$recentDay = $updateTime - (24 * 60 * 60 * 7);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			if ($date) {
				$recentDay = $date;
			}
		}
		echo $recentDay . "\n";
		try {
			if (!isset($user['apiCount'])) {
				$user['apiCount'] = 0;
			}

			$igToken = null;
			if (isset($user['igToken']) && $user['igToken']) {
				$igToken = $user['igToken'];
			}

			if ($igToken) {
				if ($user['ig_business_id']) {
					$userMedia = $this->igAPI->getUserMediaDataByGraphApiUserToken($igToken, $user['ig_business_id'], null, $type);
					$userMedia = $userMedia->media;
				} else {
					$userMedia = $this->igAPI->getUserMediaDataByUserToken($igToken, null, $type);
				}

				if (isset($userMedia->meta) && $userMedia->meta->code == 400) {
					$user['igToken'] = null;
					$userMedia = $this->igAPI->getUserMediaData($user['igId'], null, $type);
				}
			} else {
				$userMedia = $this->igAPI->getUserMediaData($user['igId'], null, $type);
			}

			echo ".";

			$user['apiCount']++;
			if (isset($userMedia->meta) && $userMedia->meta->code == 400) {
				return $userMedia;
			}
			if (isset($userMedia->code) && $userMedia->code == 429) {
				return $userMedia;
			}
			$userMediaData = $userMedia->data;

			$hasMoreMedia = false;
			//10-4-2018 no more data
			if ($igToken) {
				if ($user['ig_business_id']) {

					if ($userMedia && !(isset($recentDay) && $recentDay >= strtotime($userMedia->data[count($userMedia->data) - 1]->timestamp))) {
						$hasMoreMedia = true;
					}
				} else {
					if ($userMedia && !(isset($recentDay) && $recentDay >= $userMedia->data[count($userMedia->data) - 1]->created_time)) {
						$hasMoreMedia = true;
					}
				}

				// 3.1 Load more media data
				$countLoadApi = 0;
				if (isset($userMedia->pagination->next_max_id) || isset($userMedia->paging->next)) {
					$hasMoreMedia = true;
				} else {
					$hasMoreMedia = false;
				}
				while ($hasMoreMedia) {
					if (isset($userMedia->pagination->next_max_id)) {
						$userMedia = $this->igAPI->getUserMediaDataByUserToken($igToken, $userMedia->pagination->next_max_id, $type);
						$userMedia = $userMedia->media;

						$this->normalModel->insertApiPostLog($user['igId'], json_encode($userMedia));
						echo ".";
						$user['apiCount']++;
						if (isset($userMedia->data) && is_array($userMedia->data)) {
							$userMediaData = array_merge($userMediaData, $userMedia->data);
						}
						$countLoadApi = $countLoadApi + 1;

						if (isset($userMedia->pagination->next_max_id)) {
							$hasMoreMedia = true;
							if (isset($recentDay) && $recentDay >= $userMedia->data[count($userMedia->data) - 1]->created_time) {
								//echo ">>".$recentDay. ' --- ' .$userMedia->data[count($userMedia->data)-1]->created_time;
								$hasMoreMedia = false;
							}
						} else {

							$hasMoreMedia = false;
						}
					} else if (isset($userMedia->paging->next) && $user['ig_business_id']) {
						$userMedia = $this->igAPI->getUserMediaDataByGraphApiUserToken($igToken, $user['ig_business_id'], $userMedia->paging->next, $type);

						//$this->normalModel->insertApiPostLog($user['igId'], json_encode($userMedia));
						echo ".";
						$user['apiCount']++;
						if (isset($userMedia->data) && is_array($userMedia->data)) {
							$userMediaData = array_merge($userMediaData, $userMedia->data);
						}
						$countLoadApi = $countLoadApi + 1;

						if (isset($userMedia->paging->next)) {
							$hasMoreMedia = true;

							if (isset($recentDay) && $recentDay >= strtotime($userMedia->data[count($userMedia->data) - 1]->timestamp)) {
								//echo ">>".$recentDay. ' --- ' .$userMedia->data[count($userMedia->data)-1]->created_time;
								$hasMoreMedia = false;
							}
						} else {

							$hasMoreMedia = false;
						}
					}
				}
			}
			print_r($userMediaData);
		
			//$userMediaData = $userMedia->data;

			echo "\n";

			$recentPosts = [];
			$recent97Posts = [];
			$latestPosts = [];
			$recent7Posts = [];
			$popularPosts = [];
			$recentPostsByDate = [];
			//97
			$postCount97 = 0;
			$likeCount97 = 0;
			$commentCount97 = 0;

			$postCount7 = 0;
			$likeCount7 = 0;
			$commentCount7 = 0;

			$postCount30 = 0;
			$likeCount30 = 0;
			$commentCount30 = 0;

			$postCount90 = 0;
			$likeCount90 = 0;
			$commentCount90 = 0;

			$explosivenessPostId = null;
			$explosivenessPostScore = 0;
			$explosivenessPostLike = 0;
			$explosivenessPostComment = 0;

			//$user['media'] = $userMediaData;
			foreach ($userMediaData as $kk => $media) {
				//echo $recentDay.' - '.$media->created_time."\n";
				//&& count($recentPosts)>10

				if ($kk > 10 && ($recentDay >= $media->created_time)) {
					continue;
				}
				if ($user['ig_business_id']) {
					$tags = [];
					$tags = $this->postModel->getHashTag($media->caption);

					$pictureUrl = $media->media_url;
					$videoUrl = '';
					if ($media->media_type == "VIDEO") {
						$pictureUrl = $media->thumbnail_url;
						$videoUrl = $media->media_url;
					}

					$updateMedia = array(
						'ig_post_id' => $media->id,
						'content' => (isset($media->caption)) ? $media->caption : '',
						'postDate' => strtotime($media->timestamp),
						'link' => (isset($media->permalink)) ? $media->permalink : '',

						'pictureUrl' => $pictureUrl,
						'pictureUrlStandard' => $pictureUrl,
						'videoUrl' => $videoUrl,
						'likeCount' => $media->like_count,
						'commentCount' => $media->comments_count,
						'tagCount' => count($tags),
						'usersInPhotoCount' => 0,
						'usersInPhoto' => [],
						'tags' => $tags,
					);

				} else {

					$updateMedia = array(
						'ig_post_id' => $media->id,
						'content' => (isset($media->caption->text)) ? $media->caption->text : '',
						'postDate' => $media->created_time,
						'link' => (isset($media->link)) ? $media->link : '',

						'pictureUrl' => (isset($media->images->low_resolution->url)) ? $media->images->low_resolution->url : '',
						'pictureUrlStandard' => (isset($media->images->standard_resolution->url)) ? $media->images->standard_resolution->url : '',
						'videoUrl' => (isset($media->videos->low_resolution->url)) ? $media->videos->low_resolution->url : '',

						'likeCount' => $media->likes->count,
						'commentCount' => $media->comments->count,
						'tagCount' => (isset($media->tags)) ? count($media->tags) : 0,
						'usersInPhotoCount' => count($media->users_in_photo),
						'usersInPhoto' => (isset($media->users_in_photo)) ? $media->users_in_photo : 0,
						'tags' => (isset($media->tags)) ? $media->tags : 0,
					);
				}
				if ($type != "NEW_USER" && $type != "NEW_USER_BY_AUTOMATION") {
					$updateMedia['ig_user_id'] = $user['id'];
				}
				$score = $updateMedia['likeCount'] + $updateMedia['commentCount'];
				$updateMedia['score'] = $score;

				if ($type == Post::GEN_POST_LIST || $type === "NEW_USER" || $type === "NEW_USER_BY_AUTOMATION") {
					//echo $updateMedia['postDate']."  -> ". $recent97Day."\n";
					if ($updateMedia['postDate'] > $recent97Day) {
						$postCount97++;
						$likeCount97 += $updateMedia['likeCount'];
						$commentCount97 += $updateMedia['commentCount'];
						echo "$postCount97 " . $postCount97 . "\n";
					}

					if ($updateMedia['postDate'] > $recent90Day) {
						$postCount90++;
						$likeCount90 += $updateMedia['likeCount'];
						$commentCount90 += $updateMedia['commentCount'];
					}
				}

				if ($updateMedia['postDate'] > $recent7Day) {
					$postCount7++;
					$likeCount7 += $updateMedia['likeCount'];
					$commentCount7 += $updateMedia['commentCount'];
				}

				if (isset($recent30Day) && $updateMedia['postDate'] > $recent30Day) {
					$postCount30++;
					$likeCount30 += $updateMedia['likeCount'];
					$commentCount30 += $updateMedia['commentCount'];


					if ($score > $explosivenessPostScore) {
						$explosivenessPostLike = $updateMedia['likeCount'];
						$explosivenessPostComment = $updateMedia['commentCount'];
						$explosivenessPostScore = $score;
						$explosivenessPostId = $updateMedia['ig_post_id'];
					}
				}

				if (count($latestPosts) < 10) {
					$updateMedia['score'] = $updateMedia['commentCount'] + $updateMedia['likeCount'];
					array_push($latestPosts, (array)$updateMedia);
				}

				if (count($popularPosts) < 10 || $updateMedia['postDate'] > $recent7Day) {
					$updateMedia['score'] = $updateMedia['commentCount'] + $updateMedia['likeCount'];
					array_push($popularPosts, (array)$updateMedia);
				}

				if (($type == Post::GEN_POST_LIST || $type === "NEW_USER" || $type === "NEW_USER_BY_AUTOMATION") && $updateMedia['postDate'] > $recent97Day) {
					array_push($recent97Posts, $updateMedia);
				}
				if ($updateMedia['postDate'] > $recent7Day) {
					array_push($recent7Posts, $updateMedia);
				}
				if ($date && $updateMedia['postDate'] > $date) {
					array_push($recentPostsByDate, $updateMedia);
				}
				array_push($recentPosts, $updateMedia);
				$userMediaData[$kk] = null;
			}
			echo 'postCount7 ' . $postCount7;
			echo "\n";
			// echo 'postCount30 '.$postCount30;
			// echo "\n";

			if ($type == Post::GEN_POST_LIST || $type === "NEW_USER" || $type === "NEW_USER_BY_AUTOMATION") {
				echo 'postCount90 ' . $postCount90;
				echo "\n";
				echo 'postCount97 ' . $postCount97;
				echo "\n";
				$user['postCount97'] = $postCount97;
				$user['likeCount97'] = $likeCount97;
				$user['commentCount97'] = $commentCount97;

				$user['postCount90'] = $postCount90;
				$user['likeCount90'] = $likeCount90;
				$user['commentCount90'] = $commentCount90;


				$user['media97day'] = $recent97Posts;
				$user['media'] = $recent97Posts;
				if ($type === "NEW_USER" || $type === "NEW_USER_BY_AUTOMATION") {
					$user['media97day'] = $recent97Posts;
					$user['media'] = $recentPosts;
				}

			} else {
				if ($date) {
					$user['media'] = $recentPostsByDate;
				} else {
					$user['media'] = $recent7Posts;
				}
			}

			$user['postCount7'] = $postCount7;
			$user['likeCount7'] = $likeCount7;
			$user['commentCount7'] = $commentCount7;

			if (isset($recent30Day)) {
				$user['postCount30'] = $postCount30;
				$user['likeCount30'] = $likeCount30;
				$user['commentCount30'] = $commentCount30;

				$user['explosivenessPostLike'] = $explosivenessPostLike;
				$user['explosivenessPostComment'] = $explosivenessPostComment;
				$user['explosivenessPostScore'] = $explosivenessPostScore;
				$user['explosivenessPostId'] = $explosivenessPostId;
			}

			//Sort popular post by score
			usort($popularPosts, function ($item1, $item2) {
				if ($item1['score'] == $item2['score']) return 0;
				return ($item1['score'] < $item2['score']) ? 1 : -1;
			});

			$user['latestPosts'] = $latestPosts;

			$user['popularPosts'] = array_slice($popularPosts, 0, 10);
			$user['postListUpdatedAt'] = $updateTime;

		} catch (Exception $e) {
			print $e->getMessage();
		}
		return $user;
	}

	public function getFbUserMediaData($user, $updateTime, $type = null)
	{

		if ($type == "UPDATE_USER") {
			$recentDay = $updateTime - (24 * 60 * 60 * 97);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			$recent30Day = $updateTime - (24 * 60 * 60 * 30);
			$recent90Day = $updateTime - (24 * 60 * 60 * 90);
		} else {
			$recentDay = $updateTime - (24 * 60 * 60 * 30);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			$recent30Day = $updateTime - (24 * 60 * 60 * 30);
		}

		try {
			if (!isset($user['apiCount'])) {
				$user['apiCount'] = 0;
			}
			$userMedia = $this->facebookAPI->getUserMediaData($user['fbId'], null);

			if (!isset($userMedia->data)) {
				return $userMedia;
			}

			$userMediaData = $userMedia->data;

			$hasMoreMedia = false;
			if ($userMedia && !(isset($recentDay) && $recentDay >= strtotime($userMedia->data[count($userMedia->data) - 1]->created_time)) && isset($userMedia->paging->next)) {
				$hasMoreMedia = true;
			}
			
			//print_r($userMedia);exit();

		
			// 3.1 Load more media data
			$countLoadApi = 0;
			while ($hasMoreMedia) {
				if (isset($userMedia->paging->next)) {

					$userMedia = $this->facebookAPI->getUserMediaData($user['fbId'], $userMedia->paging->next);
					
					//$this->normalModel->insertApiPostLog($user['igId'], json_encode($userMedia));
					echo ".";
					$user['apiCount']++;
					if (isset($userMedia->data) && is_array($userMedia->data)) {
						$userMediaData = array_merge($userMediaData, $userMedia->data);
					}
					$countLoadApi = $countLoadApi + 1;

					if (isset($userMedia->paging->next)) {
						$hasMoreMedia = true;
						if (isset($recentDay) && $recentDay >= strtotime($userMedia->data[count($userMedia->data) - 1]->created_time)) {
							$hasMoreMedia = false;
						}
					} else {

						$hasMoreMedia = false;
					}
				}
			}
			echo "\n";
			//$this->normalModel->insertApiPostLog($user['igId'], json_encode($userMedia));
			//echo $countLoadApi.' <--- </br>';

			$recentPosts = [];
			$latestPosts = [];
			$popularPosts = [];
			$recent97Posts = array();
			//97
			$postCount97 = 0;
			$likeCount97 = 0;
			$commentCount97 = 0;
			$shareCount97 = 0;
			$postCount7 = 0;
			$likeCount7 = 0;
			$commentCount7 = 0;
			$shareCount7 = 0;
			$postCount30 = 0;
			$likeCount30 = 0;
			$commentCount30 = 0;
			$shareCount30 = 0;
			$postCount90 = 0;
			$likeCount90 = 0;
			$commentCount90 = 0;
			$shareCount90 = 0;

			$explosivenessPostId = null;
			$explosivenessPostScore = 0;
			$explosivenessPostLike = 0;
			$explosivenessPostComment = 0;
			$explosivenessPostShare = 0;
			foreach ($userMediaData as $media) {
				$from = isset($media->from) ? $media->from : new stdClass;
				$fromId = isset($from->id) ? $from->id : null;
				if (($fromId != $user['fbId'] || !isset($media->status_type)) || ($recentDay >= strtotime($media->created_time) && count($recentPosts) > 10)) {
					continue;
				}
				$content = null;
				if (isset($media->message)) {
					$content = $media->message;
				}
				$tags = [];
				preg_match_all('/' . $this->getPattern() . '/u', $content, $tags);
				if (isset($tags[0])) {
					$tags = $tags[0];
				}
				$updateMedia = array(
					'fb_user_id' => $user['id'],
					'fb_post_id' => $media->id,
					'content' => (isset($media->message)) ? $media->message : '',
					'postDate' => strtotime($media->created_time),
					'link' => (isset($media->link)) ? $media->link : '',
					'postType' => (isset($media->status_type)) ? $media->status_type : '',
					'pictureUrl' => (isset($media->full_picture)) ? $media->full_picture : '',
					'likeCount' => $media->reactions->summary->total_count,
					'commentCount' => $media->comments->summary->total_count,
					'shareCount' => (isset($media->shares->count)) ? $media->shares->count : 0,
					'mentionCount' => (isset($media->message_tags)) ? count($media->message_tags) : 0,
					'mentions' => (isset($media->message_tags)) ? $media->message_tags : 0,
					'tagCount' => count($tags),
					'tags' => $tags,
				);

				$score = $updateMedia['likeCount'] + $updateMedia['commentCount'] + $updateMedia['shareCount'];
				$updateMedia['score'] = $score;

				if ($type == "UPDATE_USER") {
					$postCount97++;
					$likeCount97 += $updateMedia['likeCount'];
					$commentCount97 += $updateMedia['commentCount'];
					$shareCount97 += $updateMedia['shareCount'];

					if ($updateMedia['postDate'] > $recent90Day) {
						$postCount90++;
						$likeCount90 += $updateMedia['likeCount'];
						$commentCount90 += $updateMedia['commentCount'];
						$shareCount90 += $updateMedia['shareCount'];
					}
				}

				if ($updateMedia['postDate'] > $recent7Day) {
					$postCount7++;
					$likeCount7 += $updateMedia['likeCount'];
					$commentCount7 += $updateMedia['commentCount'];
					$shareCount7 += $updateMedia['shareCount'];
				}

				if ($updateMedia['postDate'] > $recent30Day) {
					$postCount30++;
					$likeCount30 += $updateMedia['likeCount'];
					$commentCount30 += $updateMedia['commentCount'];
					$shareCount30 += $updateMedia['shareCount'];


					if ($score > $explosivenessPostScore) {
						$explosivenessPostLike = $updateMedia['likeCount'];
						$explosivenessPostComment = $updateMedia['commentCount'];
						$explosivenessPostShare = $updateMedia['shareCount'];
						$explosivenessPostScore = $score;
						$explosivenessPostId = $updateMedia['fb_post_id'];
					}

				}



				if (count($latestPosts) < 10) {
					$updateMedia['score'] = $updateMedia['commentCount'] + $updateMedia['likeCount'] + $updateMedia['shareCount'];
					array_push($latestPosts, (array)$updateMedia);
				}

				if (count($popularPosts) < 10 || $updateMedia['postDate'] > $recent7Day) {
					$updateMedia['score'] = $updateMedia['commentCount'] + $updateMedia['likeCount'] + $updateMedia['shareCount'];
					array_push($popularPosts, (array)$updateMedia);
				}
				if ($updateMedia['postDate'] > $recentDay) {
					array_push($recent97Posts, $updateMedia);
				}

				array_push($recentPosts, $updateMedia);
			}


			echo 'postCount7 ' . $postCount7;
			echo "\n";
			echo 'postCount30 ' . $postCount30;
			echo "\n";
			// echo 'postCount90 '.$postCount90;
			// echo "\n";
			// echo 'postCount97 '.$postCount97;
			// echo "\n";

			$user['postCount7'] = $postCount7;
			$user['likeCount7'] = $likeCount7;
			$user['commentCount7'] = $commentCount7;
			$user['shareCount7'] = $shareCount7;

			$user['postCount30'] = $postCount30;
			$user['likeCount30'] = $likeCount30;
			$user['commentCount30'] = $commentCount30;
			$user['shareCount30'] = $shareCount30;

			$user['explosivenessPostLike'] = $explosivenessPostLike;
			$user['explosivenessPostComment'] = $explosivenessPostComment;
			$user['explosivenessPostShare'] = $explosivenessPostShare;
			$user['explosivenessPostScore'] = $explosivenessPostScore;
			$user['explosivenessPostId'] = $explosivenessPostId;


			if ($type == "UPDATE_USER") {
				echo 'postCount90 ' . $postCount90;
				echo "\n";
				echo 'postCount97 ' . $postCount97;
				echo "\n";
				$user['postCount97'] = $postCount97;
				$user['likeCount97'] = $likeCount97;
				$user['commentCount97'] = $commentCount97;
				$user['shareCount97'] = $shareCount97;

				$user['postCount90'] = $postCount90;
				$user['likeCount90'] = $likeCount90;
				$user['commentCount90'] = $commentCount90;
				$user['shareCount90'] = $shareCount90;

				$user['media97day'] = $recent97Posts;
				$user['media'] = $recent97Posts;

			} else {
				$user['media'] = $recent97Posts;
			}

			//Sort popular post by score
			usort($popularPosts, function ($item1, $item2) {
				if ($item1['score'] == $item2['score']) return 0;
				return ($item1['score'] < $item2['score']) ? 1 : -1;
			});

			$user['latestPosts'] = $latestPosts;
			$user['popularPosts'] = array_slice($popularPosts, 0, 10);
			$user['postListUpdatedAt'] = $updateTime;
			return $user;
		} catch (Exception $e) {
			print $e->getMessage();
		}

		return $user;
	}

	public function getYtUserMediaData($user, $updateTime, $type = null)
	{
		if ($type == "UPDATE_USER") {
			$recentDay = $updateTime - (24 * 60 * 60 * 97);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			$recent30Day = $updateTime - (24 * 60 * 60 * 30);
			$recent90Day = $updateTime - (24 * 60 * 60 * 90);
		} else {
			$recentDay = $updateTime - (24 * 60 * 60 * 30);
			$recent7Day = $updateTime - (24 * 60 * 60 * 7);
			$recent30Day = $updateTime - (24 * 60 * 60 * 30);
		}

		try {
			$userMedia = $this->youtubeAPI->getYtUserMediaData($user['ytId']);
			if (!isset($userMedia)) {
				return $userMedia;
			}

			$recentPosts = [];
			$latestPosts = [];
			$popularPosts = [];
			$recent97Posts = array();
			//97
			$postCount97 = 0;
			$viewCount97 = 0;
			$commentCount97 = 0;
			$likeCount97 = 0;
			$dislikeCount97 = 0;

			$postCount7 = 0;
			$viewCount7 = 0;
			$commentCount7 = 0;
			$likeCount7 = 0;
			$dislikeCount7 = 0;

			$postCount30 = 0;
			$viewCount30 = 0;
			$commentCount30 = 0;
			$likeCount30 = 0;
			$dislikeCount30 = 0;

			$postCount90 = 0;
			$viewCount90 = 0;
			$commentCount90 = 0;
			$likeCount90 = 0;
			$dislikeCount90 = 0;

			$explosivenessPostId = null;
			$explosivenessPostScore = 0;
			$explosivenessPostLike = 0;
			$explosivenessPostComment = 0;
			$explosivenessPostView = 0;

			date_default_timezone_set("Asia/Hong_Kong");
			foreach ($userMedia as $media) {
				$updateMedia = array(
					'yt_user_id'			=> $user['ytId'],
					'yt_video_id'			=> $media['videoId'],
					'desc'					=> $media['description'],
					'pictureUrl'			=> $media['pictureUrl'],
					'postDate'				=> strtotime($media['postDate']),
					'viewCount'				=> $media['viewCount'],
					'likeCount'				=> $media['likeCount'],
					'dislikeCount'			=> $media['dislikeCount'],
					'commentCount'			=> $media['commentCount'],
					'videoDuration'			=> $media['videoDuration'],
					'tags'					=> $media['tags'],
					'categId'				=> $media['categId']
				);
				// clarify forumula with Rudy
				$score = $updateMedia['likeCount'] + $updateMedia['commentCount'];
				$updateMedia['score'] = $score;

				if ($type == "UPDATE_USER") {
					$postCount97++;
					$viewCount97 += $updateMedia['viewCount'];
					$likeCount97 += $updateMedia['likeCount'];
					$commentCount97 += $updateMedia['commentCount'];
					$dislikeCount97 += $updateMedia['dislikeCount'];

					if ($updateMedia['postDate'] > $recent90Day) {
						$postCount90++;
						$likeCount90 += $updateMedia['likeCount'];
						$commentCount90 += $updateMedia['commentCount'];
						$dislikeCount90 += $updateMedia['dislikeCount'];
					}
				}

				if ($updateMedia['postDate'] > $recent7Day) {
					$postCount7++;
					$likeCount7 += $updateMedia['likeCount'];
					$commentCount7 += $updateMedia['commentCount'];
					$dislikeCount7 += $updateMedia['dislikeCount'];
				}

				if ($updateMedia['postDate'] > $recent30Day) {
					$postCount30++;
					$likeCount30 += $updateMedia['likeCount'];
					$commentCount30 += $updateMedia['commentCount'];
					$dislikeCount30 += $updateMedia['dislikeCount'];

					if ($score > $explosivenessPostScore) {
						$explosivenessPostLike = $updateMedia['likeCount'];
						$explosivenessPostComment = $updateMedia['commentCount'];
						$explosivenessPostView = $updateMedia['viewCount'];
						$explosivenessPostScore = $score;
						$explosivenessPostId = $updateMedia['yt_video_id'];
					}
				}
				// clarify the formula with Rudy
				if (count($latestPosts) < 10) {
					$updateMedia['score'] = $updateMedia['commentCount'] + $updateMedia['likeCount'];
					array_push($latestPosts, (array)$updateMedia);
				}

				if (count($popularPosts) < 10 || $updateMedia['postDate'] > $recent7Day) {
					$updateMedia['score'] = $updateMedia['commentCount'] + $updateMedia['likeCount'];
					array_push($popularPosts, (array)$updateMedia);
				}
				if ($updateMedia['postDate'] > $recentDay) {
					array_push($recent97Posts, $updateMedia);
				}
				
				array_push($recentPosts, $updateMedia);
			}

			$user['postCount7'] = $postCount7;
			$user['likeCount7'] = $likeCount7;
			$user['commentCount7'] = $commentCount7;
			$user['dislikeCount7'] = $dislikeCount7;

			$user['postCount30'] = $postCount30;
			$user['likeCount30'] = $likeCount30;
			$user['commentCount30'] = $commentCount30;
			$user['dislikeCount30'] = $dislikeCount30;

			$user['explosivenessPostLike'] = $explosivenessPostLike;
			$user['explosivenessPostComment'] = $explosivenessPostComment;
			$user['explosivenessPostView'] = $explosivenessPostView;
			$user['explosivenessPostScore'] = $explosivenessPostScore;
			$user['explosivenessPostId'] = $explosivenessPostId;

			if ($type == "UPDATE_USER") {
				$user['postCount97'] = $postCount97;
				$user['likeCount97'] = $likeCount97;
				$user['commentCount97'] = $commentCount97;
				$user['dislikeCount97'] = $dislikeCount97;

				$user['postCount90'] = $postCount90;
				$user['likeCount90'] = $likeCount90;
				$user['commentCount90'] = $commentCount90;
				$user['dislikeCount90'] = $dislikeCount90;

				$user['media97Day'] = $recent97Posts;
				$user['media'] = $recent97Posts;
			}
			else {
				$user['media'] = $recent97Posts;
			}

			// Sort popular post by score
			usort($popularPosts, function ($item1, $item2) {
				if ($item1['score'] == $item2['score']) return 0;
				return ($item1['score'] < $item2['score']) ? 1 : -1;
			});

			$user['latestPosts'] = $latestPosts;
			$user['popularPosts'] = array_slice($popularPosts, 0, 10);
			$user['postListUpdatedAt'] = $updateTime;
			return $user;
		}
		catch (Exception $e) {
			print $e->getMessage();
		}
		return $user;
	}

	public function countActiveness($user)
	{
		return ($user['postCount30'] / 30);
	}

	public function countInteraction($user)
	{
		$interaction = 0;

		if ($user['postCount30'] != 0) {
			if (!isset($user['shareCount30'])) {
				$user['shareCount30'] = 0;
			}
			$interaction = (($user['likeCount30'] + $user['commentCount30'] + $user['shareCount30']) / $user['postCount30']);
		}
		return $interaction;
	}

	public function countExplosiveness($user)
	{
		$interaction90 = 1;

		if ($user['postCount90'] != 0) {
			if (!isset($user['shareCount90'])) {
				$user['shareCount90'] = 0;
			}
			$interaction90 = (($user['likeCount90'] + $user['commentCount90'] + $user['shareCount90']) / $user['postCount90']);
		}
		$explosiveness = (($user['explosivenessPostScore'] - $interaction90) / $interaction90 * 100);

		return $explosiveness;
	}
	public function countEngagement($interaction, $followerCount)
	{
		return ($interaction / $followerCount) * 100;
	}
	//TODO: count appeal;
	public function countAppeal($user, $startTime, $firstLoggedAt = null, $type = "FB")
	{
		$recentfollowerFieldName = 'updatedFollowerCount';
		$followerFieldName = 'followerCount';
		if ($type === "FB") {
			$recentfollowerFieldName = 'fanCount';
			$followerFieldName = 'fanCount';
		} elseif ($type === "YT") {
			$recentfollowerFieldName = 'subscriberCount';
			$followerFieldName = 'subscriberCount';
		}
		//$startTime = 1488214861;
		//$user[$followerFieldName] = 34014;
		$recent7Day = $startTime - (24 * 60 * 60 * 7);
		$recent14Day = $startTime - (24 * 60 * 60 * 14);
		$recent21Day = $startTime - (24 * 60 * 60 * 21);
		$recent28Day = $startTime - (24 * 60 * 60 * 28);
		// question
		if ($type === "FB" && $firstLoggedAt < 1483817403) {
			$firstLoggedAt = 1483817403;
		}
		$day = (($startTime - $firstLoggedAt) / 60 / 60 / 24);

		$user['followerStartedAt'] = null;
		$user['appeal'] = 0;
		//echo $day."Day<---\n";
		if (!$firstLoggedAt || $day < 1) {
			return $user;
		}

		if ($day < 14) {
			if ($day < 7) {
				$recent = $this->normalModel->getDayBeforeUserLog($user['id'], round($startTime - (24 * 60 * 60 * $day)), $type);
			} else {
				$recent = $this->normalModel->getDayBeforeUserLog($user['id'], $recent7Day, $type);
			}

			$user['followerStartedAt'] = $recent['loggedAt'];
			if ($recent[$recentfollowerFieldName]) {
				$user['appeal'] = (($user[$followerFieldName] - $recent[$recentfollowerFieldName]) / $recent[$recentfollowerFieldName]);
			}
		} else {
			//echo $recent7Day." - day\n";
			$recent7 = $this->normalModel->getDayBeforeUserLog($user['id'], $recent7Day, $type);
			$appealWeek1 = 0;
			if ($recent7[$recentfollowerFieldName]) {
				// echo $user[$followerFieldName]."\n";
				// echo $recent7[$recentfollowerFieldName]."\n";
				$appealWeek1 = (($user[$followerFieldName] - $recent7[$recentfollowerFieldName]) / $recent7[$recentfollowerFieldName]) * 100;
				// echo $appealWeek1."\n";
				// echo "=========\n";
			}
			//echo $appealWeek1 ."<W1 appeal\n";
			$recent14 = $this->normalModel->getDayBeforeUserLog($user['id'], $recent14Day, $type);
			$appealWeek2 = 0;
			if ($recent14[$recentfollowerFieldName]) {

				// echo $recent7[$recentfollowerFieldName]."\n";
				// echo $recent14[$recentfollowerFieldName]."\n";
				$appealWeek2 = (($recent7[$recentfollowerFieldName] - $recent14[$recentfollowerFieldName]) / $recent14[$recentfollowerFieldName]) * 100;
				// echo $appealWeek2."\n";
				// echo "=========\n";
			}
			//echo $appealWeek2 ."<W2 appeal\n";
			$loggedAt = $recent14['loggedAt'];
			$appeal = (($appealWeek1 + $appealWeek2) / 2);

			// echo "Current Follower :".$user[$followerFieldName]."\n";
			// echo "7Day Follower :".$recent7[$recentfollowerFieldName]."\n";
			// echo "14Day Follower :".$recent14[$recentfollowerFieldName]."\n";

			if ($day >= 21) {
				//echo "recent21" ;
				$recent21 = $this->normalModel->getDayBeforeUserLog($user['id'], $recent21Day, $type);
				// print_r($recent21);
				// echo $recentfollowerFieldName." ---<<<";
				// echo $recent21[$recentfollowerFieldName];
				// echo "<<<<<\n\n";

				//echo "21Day Follower :".$recent21[$recentfollowerFieldName]."\n";
				$appealWeek3 = 0;
				if ($recent21[$recentfollowerFieldName]) {
					// echo $recent14[$recentfollowerFieldName]."\n";
					// echo $recent21[$recentfollowerFieldName]."\n";
					$appealWeek3 = (($recent14[$recentfollowerFieldName] - $recent21[$recentfollowerFieldName]) / $recent21[$recentfollowerFieldName]) * 100;
					// echo $appealWeek3."\n";
					// echo "=========\n";
				}
				//echo $appealWeek3 ."<W3 appeal\n";
				$loggedAt = $recent21['loggedAt'];
				$appeal = (($appealWeek1 + $appealWeek2 + $appealWeek3) / 3);
			}
			if ($day >= 28) {
				$recent28 = $this->normalModel->getDayBeforeUserLog($user['id'], $recent28Day, $type);
				//echo "28Day Follower :".$recent28[$recentfollowerFieldName]."\n";
				$appealWeek4 = 0;
				if ($recent28[$recentfollowerFieldName]) {
					// echo $recent21[$recentfollowerFieldName]."\n";
					// echo $recent28[$recentfollowerFieldName]."\n";
					$appealWeek4 = (($recent21[$recentfollowerFieldName] - $recent28[$recentfollowerFieldName]) / $recent28[$recentfollowerFieldName]) * 100;
					// echo $appealWeek4."\n";
					// echo "=========\n";
				}
				//echo $appealWeek4 ."<W4 appeal\n";
				$loggedAt = $recent28['loggedAt'];
				echo "appearl week\n";
				echo $appealWeek1 . "\n";
				echo $appealWeek2 . "\n";
				echo $appealWeek3 . "\n";
				echo $appealWeek4 . "\n";
				echo "============= \n";
				$appeal = (($appealWeek1 + $appealWeek2 + $appealWeek3 + $appealWeek4) / 4);
			}
			$user['followerStartedAt'] = $loggedAt;
			$user['appeal'] = round(($appeal / 100), 11);
		}
		//print_r($user['appeal']);
		echo "===\n";
		// echo $day ."\n";
		// echo $user['followerStartedAt']."\n";
		// echo $user['appeal']."\n";

		//exit();
		// echo $user['appeal']." <- Appeal\n";
		// exit();
		// $totalFollower = $user['followerCount'];
		// $user['followerPercentage'] = 0;
		// $user['weeklyFollowerCount'] = 0;
		// $user['weeklyLoggedAt'] = time();
		// if(!isset($user['createdAt']) && !$firstLoggedAt){
		// 	return $user;
		// }
		// $userCreatedDay = round((time()-$firstLoggedAt)/60/60/24);
		// if($userCreatedDay == 0){
		// 	return $user;
		// }
		// //if( $userCreatedDay<14 && $userCreatedDay!=0 ){
		// if( $userCreatedDay<7 ){
		// 	$recentDay = time()-(24*60*60*$userCreatedDay);
		// }
		// $recent = $this->normalModel->getDayBeforeUserLog($user['id'], $recentDay);
		// $user['weeklyLoggedAt'] = $recentDay;
		// if(is_null($recent)){
		// 	return $user;
		// }
		// $prevLogFollower = $recent['updatedFollowerCount'];

		// //followerPercentage: when user create after 14 day
		// $user['followerPercentage'] = round((($totalFollower - $prevLogFollower)/$prevLogFollower)*100, 3);
		// $user['weeklyFollowerCount'] = ($totalFollower - $prevLogFollower);

		return $user;
	}


	public function countReach($user, $type = "FB")
	{
		$followerFieldName = 'followerCount';
		if ($type === "FB") {
			$followerFieldName = 'fanCount';
		}
		elseif ($type === "YT") {
			$followerFieldName = 'subscriberCount';
		}
		$reach = $user[$followerFieldName];
		return $reach;
	}

	public function countRecentAndRating($user, $type = null)
	{
		echo $user['activenessScore'];
		$first_post_date = null;
		$last_post_date = null;

		$media_like_count = 0;
		$media_comment_count = 0;
		$media_tag_count = 0;
		$media_count = 0; //for checking
		$media_user_count = 0;
		$media_mention_count = 0;


		$recentCount = array(
			'media_like_count' => 0,
			'media_comment_count' => 0,
			'media_tag_count' => 0,
			'media_count' => 0,
		);
		if ($type === 'UPDATE_POST') {

			$recentPosts = $this->postModel->getPostByIgId($user['id'], $this->recentDay);
			foreach ($recentPosts as $mediaKey => $media) {
				if ($mediaKey == 0) {
					$last_post_date = $media['postDate'];
				}
				$recentCount['media_like_count'] = $recentCount['media_like_count'] + $media['likeCount'];
				$recentCount['media_comment_count'] = $recentCount['media_comment_count'] + $media['commentCount'];
				$recentCount['media_tag_count'] = $recentCount['media_tag_count'] + $media['tagCount'];
				$recentCount['media_count']++;
			}
		} else {
			//For insert new user
			foreach ($user['media'] as $mediaKey => $media) {
				if ($mediaKey == 0) {
					$last_post_date = $media['postDate'];
					//print_r($media);
				}
				if ($mediaKey == count($user['media']) - 1) {
					$first_post_date = $media['postDate'];
				}

				$media_like_count += $media['likeCount'];
				$media_comment_count += $media['commentCount'];
				$media_tag_count += $media['tagCount'];
				$media_count++;

				$media_user_count += $media['usersInPhotoCount'];

				if (isset($media['content'])) {
					$media_mention_count += count($this->postModel->getMention($media['content']));
				}

				if ($media['postDate'] > $this->recentDay) {
					$recentCount['media_like_count'] = $recentCount['media_like_count'] + $media['likeCount'];
					$recentCount['media_comment_count'] = $recentCount['media_comment_count'] + $media['commentCount'];
					$recentCount['media_tag_count'] = $recentCount['media_tag_count'] + $media['tagCount'];
					$recentCount['media_count']++;
				}
			}

			$user['media_count_readed'] = $media_count; //for test
			$user['firstPostDate'] = $first_post_date;
			$user['likeCount'] = $media_like_count;
			$user['commentCount'] = $media_comment_count;
			$user['tagCount'] = $media_tag_count;
			$user['userInPhotoCount'] = $media_user_count;
			$user['mentionCount'] = $media_mention_count;
		}
		echo $user['activenessScore'];

		$user['recentDate'] = $this->recentDay;
		$user['lastPostDate'] = $last_post_date;
		$user['recent'] = $recentCount;
		//5. Count rating (Influence Power)
		$user['rating'] = $this->countRating($user);

		$user['updatedAt'] = time();
		return $user;
	}

	public function countRating($user)
	{
		$scoreCount = new ScoreCounter($user['likeCount'], $user['tagCount'], $user['commentCount'], $user['followerCount'], $user['postCount'], $user['lastPostDate'], $user['firstPostDate']);

		return $scoreCount->getScore();
	}

	public function countFollowerRisingPercentage($user, $recentDay, $firstLoggedAt = null, $socialPlatform = "IG")
	{
		$followerCount = 0;
		if ($socialPlatform == "FB") {
			$followerCount = $user['fanCount'];
		} elseif ($socialPlatform == "YT") {
			$followerCount = $user['subscriberCount'];
		}
		else {
			$followerCount = $user['followerCount'];
		}
		$totalFollower = $followerCount;
		$user['followerPercentage'] = 0;
		$user['weeklyFollowerCount'] = 0;
		$user['weeklyLoggedAt'] = time();
		if (!isset($user['createdAt']) && !$firstLoggedAt) {
			return $user;
		}
		$userCreatedDay = round((time() - $firstLoggedAt) / 60 / 60 / 24);
		if ($userCreatedDay == 0) {
			return $user;
		}
	
		//if( $userCreatedDay<14 && $userCreatedDay!=0 ){
		if ($userCreatedDay < 7) {
			$recentDay = time() - (24 * 60 * 60 * $userCreatedDay);
		}
		$recent = $this->normalModel->getDayBeforeUserLog($user['id'], $recentDay, $socialPlatform);

		$user['weeklyLoggedAt'] = $recentDay;
		echo $user['weeklyLoggedAt'];
		echo "<<<weekly logged at\n";
		if (is_null($recent)) {
			return $user;
		}
		$prevLogFollower = $recent['updatedFollowerCount'];
		//followerPercentage: when user create after 14 day
		$user['followerPercentage'] = round((($totalFollower - $prevLogFollower) / $prevLogFollower) * 100, 3);
		$user['weeklyFollowerCount'] = ($totalFollower - $prevLogFollower);
		//print_r($user);
		return $user;
		// }

		// $prev14Day = time()-(24*60*60*14);
		// $recent7 = $this->normalModel->getDayBeforeUserLog($user['id'], $recentDay);
		// $recent14 = $this->normalModel->getDayBeforeUserLog($user['id'], $prev14Day);

		// $prevWeek = $recent7['updatedFollowerCount'] - $recent14['updatedFollowerCount'];
		// $thisWeek = $totalFollower - $recent7['updatedFollowerCount'];
		// $user['followerPercentage'] = 0;

		// if($prevWeek!=0){
		// 	$user['followerPercentage'] = round((($thisWeek - $prevWeek)/$prevWeek)*100, 3);
		// }

		// $user['weeklyFollowerCount'] = $thisWeek;
		// $user['weeklyLoggedAt'] = $recentDay;

		// return $user;
	}

	public function countInteractionRisingPercentage($user, $recentDay, $recent97Day, $socialPlatform = "IG")
	{
		//question
		if ($socialPlatform == "FB") {
			$user['oldInteraction'] = 0;
			if ($user['postCount90']) {
				$user['oldInteraction'] = round((($user['likeCount90'] + $user['commentCount90'] + $user['shareCount90']) / $user['postCount90']), 0);
			}

		} else {
			$user['interaction'] = 0;
			if ($user['postCount90']) {
				$user['interaction'] = round((($user['likeCount90'] + $user['commentCount90']) / $user['postCount90']), 0);
			}
		}
		//echo $user['interaction']."<<<<<<-----interaction";
		//$user['weeklyLoggedAt'] = $recentDay;

		$recent97PostCount = 0;
		$recent97LikeCount = 0;
		$recent97CommentCount = 0;
		$recent97ShareCount = 0;
		$recent7PostCount = 0;
		$recent7LikeCount = 0;
		$recent7CommentCount = 0;
		$recent7ShareCount = 0;

		foreach ($user['media97day'] as $key => $media) {
			if ($recent97Day < $media['postDate'] && $recentDay > $media['postDate']) {
				$recent97PostCount++;
				$recent97LikeCount = $recent97LikeCount + $media['likeCount'];
				$recent97CommentCount = $recent97CommentCount + $media['commentCount'];
				if ($socialPlatform == "FB") {
					$recent97ShareCount = $recent97ShareCount + $media['shareCount'];
				}

			} else if ($recentDay < $media['postDate']) {
				$recent7PostCount++;
				$recent7LikeCount = $recent7LikeCount + $media['likeCount'];
				$recent7CommentCount = $recent7CommentCount + $media['commentCount'];
				if ($socialPlatform == "FB") {
					$recent7ShareCount = $recent7ShareCount + $media['shareCount'];
				}
			}
		}

		$interaction = 0;
		if ($recent7PostCount != 0) {
			if ($socialPlatform == "FB") {
				$interaction = (($recent7LikeCount + $recent7CommentCount + $recent7ShareCount) / $recent7PostCount);
			} else {
				$interaction = (($recent7LikeCount + $recent7CommentCount) / $recent7PostCount);
			}
		}
		$user['weeklyInteraction'] = round($interaction, 0);

		$interaction97 = 0;
		if ($recent97PostCount != 0) {
			if ($socialPlatform == "FB") {
				$interaction97 = (($recent97LikeCount + $recent97CommentCount + $recent97ShareCount) / $recent97PostCount);
			} else {
				$interaction97 = (($recent97LikeCount + $recent97CommentCount) / $recent97PostCount);
			}
		}
		//echo $interaction97."<<<<_----interaction97\n\n";
		$user['interactionPercentage'] = 0;
		if ($recent97PostCount != 0 && $interaction != 0) {
			$user['interactionPercentage'] = round((($interaction - $interaction97) / $interaction97) * 100, 3);
			// echo $interaction."\n";
			// echo $interaction97."\n";
		}

		$user['recent7'] = [
			'postCount' => $recent7PostCount,
			'likeCount' => $recent7LikeCount,
			'commentCount' => $recent7CommentCount
		];

		$user['recent97'] = [
			'postCount' => $recent97PostCount,
			'likeCount' => $recent97LikeCount,
			'commentCount' => $recent97CommentCount
		];
		return $user;

		// if(!isset($user['createdAt'])&& !$firstLoggedAt){
		// 	return $user;
		// }
		// $userCreatedDay = round((time()-$firstLoggedAt)/60/60/24);
		// if( $userCreatedDay<14 && $userCreatedDay!=0 ){
		// 	if( $userCreatedDay<7 ){
		// 		$recentDay = time()-(24*60*60*$userCreatedDay);
		// 	}
		// 	$recent = $this->normalModel->getDayBeforeUserLog($user['id'], $recentDay);
		// 	$user['weeklyLoggedAt'] = $recentDay;
		// 	if(is_null($recent)){
		// 		return $user;
		// 	}

		// 	$likeCount = $user['likeCount'] - $recent['likeCount'];
		// 	$commentCount = $user['commentCount'] - $recent['commentCount'];
		// 	$postCount = $user['postCount'] - $recent['updatedPostCount'];
		// 	if($postCount != 0){
		// 		$user['weeklyInteraction'] = round((($likeCount+$commentCount)/$postCount), 0);
		// 	}

		// 	//$user['interactionPercentage'] = round((($likeCount + ($commentCount))/$postCount)*100, 3);

		// 	return $user;
		// }

		// $prev14Day = time()-(24*60*60*14);
		// $recent = $this->normalModel->getDayBeforeUserLog($user['id'], $recentDay);
		// $recent14 = $this->normalModel->getDayBeforeUserLog($user['id'], $prev14Day);

		// $likeCount = $user['likeCount'] - $recent['likeCount'];
		// $commentCount = $user['commentCount'] - $recent['commentCount'];
		// $postCount = $user['postCount'] - $recent['updatedPostCount'];
		// echo $likeCount."= (".$user['likeCount']." - ".$recent['likeCount'].") \n";
		// echo $commentCount."= (".$user['commentCount']." - ".$recent['commentCount'].") \n";
		// echo $postCount."= (".$user['postCount']." - ".$recent['updatedPostCount'].") \n";
		// echo "---\n";

		// $interaction = 0;
		// if($postCount != 0){
		// 	$interaction = (($likeCount+$commentCount)/$postCount);
		// }
		// $prevWeekLikeCount = $recent['likeCount'] - $recent14['likeCount'];
		// $prevWeekCommentCount = $recent['commentCount'] - $recent14['commentCount'];
		// $prevWeekPostCount = $recent['updatedPostCount'] - $recent14['updatedPostCount'];

		// echo $prevWeekLikeCount."= (".$recent['likeCount']." - ".$recent14['likeCount'].") \n";
		// $prevWeekInteraction = 0;
		// $user['interactionPercentage'] = 0;
		// if($prevWeekPostCount != 0){
		// 	$prevWeekInteraction =(($prevWeekLikeCount+$prevWeekCommentCount)/$prevWeekPostCount);
		// 	$user['interactionPercentage'] = round((($interaction - $prevWeekInteraction)/$prevWeekInteraction)*100, 3);
		// }

		// $user['weeklyInteraction'] = round($interaction, 0);
		// $user['weeklyLoggedAt'] = $recentDay;
		// return $user;





		// $a = $this->normalModel->getDayBeforeUserLog($recentDay);
		// $b = $this->normalModel->getDayBeforeUserLog($recentDay-(24*60*60));
		// $c = $this->normalModel->getDayBeforeUserLog($recentDay-(24*60*60*90));

		// if(is_null($a)||is_null($b)||is_null($c)){
		// 	return $user;
		// }

		// $likeCount = $user['likeCount'] - $a['likeCount'];
		// $commentCount = $user['commentCount'] - $a['commentCount'];
		// $postCount = $user['postCount'] - $a['updatedPostCount'];
		// $interaction = (($likeCount+$commentCount)/$postCount);

		// $user['interaction'] = $interaction;

		// $oldLikeCount = $b['likeCount']-$c['likeCount'];
		// $oldCommentCount = $b['commentCount'] - $c['commentCount'];
		// $oldPostCount = $b['updatedPostCount'] - $c['updatedPostCount'];
		// $oldInteraction = (($oldLikeCount+$oldCommentCount)/$oldPostCount);

		// $user['interactionPercentage'] = (($latestData-$oldInteraction)/$oldInteraction);



		// $a = $this->normalModel->getDayBeforeUserLog($user['id'], $recentDay);

		// if(is_null($a)){
		// 	return $user;
		// }

		// $likeCount = $user['likeCount'] - $a['likeCount'];
		// $commentCount = $user['commentCount'] - $a['commentCount'];
		// $postCount = $user['postCount'] - $a['updatedPostCount'];
		// $user['interaction']  = (($likeCount+$commentCount)/$postCount);

		// $latestInteraction = (($user['likeCount']+$user['commentCount'])/$user['postCount']);
		// $oldInteraction = (($a['likeCount']+$a['commentCount'])/$a['updatedPostCount']);
		// $user['interactionPercentage'] = round((($latestInteraction - $oldInteraction)/$oldInteraction)*100, 3);

		// return $user;
	}

	public function countEngagementRate($user, $interaction, $followerCount)
	{
		$user['engagementRate'] = round(($interaction / $followerCount) * 100, 3);
		echo "engagementRate: " . $user['engagementRate'] . "\n";
		return $user;
	}

	public function defineInstagram($user, $pattern)
	{

	}

	public function defineFacebook($user, $pattern)
	{

		preg_match_all($pattern, urlencode($user['bio']), $matches);
		if ($matches[0]) {
			//print_r($matches[0]);
			$user['facebook'] = urldecode($matches[0][0]);
		}
		if (!isset($user['facebook']) && isset($user['external_url'])) {
			preg_match_all($pattern, $user['external_url'], $matches);
			if ($matches[0]) {
				//print_r($matches[0]);
				$user['facebook'] = urldecode($matches[0][0]);
			}
		}
		return $user;
	}

	public function defineYoutube($user, $pattern)
	{

		preg_match_all($pattern, urlencode($user['bio']), $matches);
		if ($matches[0]) {
			// echo "=============\n";
			// echo $user['bio']."\n";
			// print_r($matches[0]);
			$user['youtube'] = urldecode($matches[0][0]);
		}
		if (!isset($user['youtube']) && isset($user['external_url'])) {
			preg_match_all($pattern, $user['external_url'], $matches);
			if ($matches[0]) {
				//print_r($matches[0]);
				$user['youtube'] = urldecode($matches[0][0]);
			}
		}
		return $user;
	}

	public function defineEmail($user, $pattern)
	{

		preg_match_all($pattern, $user['bio'], $matches);
		if ($matches[0]) {
			// echo "=============\n";
			// echo $user['bio']."\n";
			// print_r($matches[0]);
			$user['email'] = urldecode($matches[0][0]);
		}
		if (!isset($user['email']) && isset($user['external_url'])) {
			preg_match_all($pattern, $user['external_url'], $matches);
			if ($matches[0]) {
				//print_r($matches[0]);
				$user['email'] = urldecode($matches[0][0]);
			}
		}
		return $user;
	}
	public function defineInterest($user, $pattern, $keywordList)
	{
		$matches = null;
		$matchedKeywords = $this->getKeywordByPattern($pattern, $user['bio']);

		if (count($matchedKeywords) > 0) {
			$interestArr = [];
			foreach ($matchedKeywords as $matchKeyword) {
				$interestId = null;
				foreach ($keywordList as $keyword) {
					if (strtolower($keyword['keyword']) == strtolower($matchKeyword)) {
						//echo $matchKeyword."\n";
						$interestId = $keyword['related_id'];
						continue;
					}
				}
				if ($interestId) {
					array_push($interestArr, $interestId);
				}
			}

			$user['interests'] = array_unique($interestArr);
			//print_r($user['interests'] );
		}
		return $user;
	}

	public function defineIdentity($user, $pattern, $keywordList)
	{
		$matches = null;
		$identityId = null;
		$identityIsOther = false;
		$user['identityId'] = null;

		$matchedKeywords = $this->getKeywordByPattern($pattern, $user['bio']);
		if (count($matchedKeywords) > 0) {
			//echo $user['id']."===============\n";
			foreach ($matchedKeywords as $matchKeyword) {

				$matchKeyword = Emoji::Decode($matchKeyword);
				foreach ($keywordList as $keyword) {

					if (strtolower($keyword['keyword']) == strtolower($matchKeyword)) {
						//echo $keyword['keyword']." -> keyword\n";
						$identityId = $keyword['related_id'];
						$user['identityId'] = $identityId;

						// echo "=================\n";
						// echo $matches[0][0]."\n";
						// echo $userList[$key]['identityId']."\n";

						if ($identityId == 14) {
							//echo "other<<\n";
							continue 2;

						} else {
							//echo $user['identityId'] ."<><<";
							return $user;
						}
					}

				}
			}
		}
		//echo $user['identityId'] ."<><<";
		return $user;
	}

	public function defineLocation($user, $pattern, $locationKeyword)
	{
		$matches = null;
		$locationId = null;
		$user['locationId'] = $locationId;
		$user['locationIdBy'] = null;
		// echo "==================== \n";
		// echo $user['id']." :iddd\n";
		// echo $user['bio']."\n";
		//echo $pattern[0];
		//$user['bio'] = "La môd salon: Bassement, Bonaventure Building, 91 Leighton Road, Causeway Bay,HK. Tel:28811092✂️";
		//echo "\n";
		$matchedKeywords = $this->getKeywordByPattern($pattern, $user['bio']);

		if (count($matchedKeywords) > 0) {
			foreach ($matchedKeywords as $key => $matchedKeyword) {
				$matchedKeyword = Emoji::Decode($matchedKeyword);
				//echo $matchedKeyword."\n";
				foreach ($locationKeyword as $keyword) {
					if (strtolower($keyword['keyword']) == strtolower($matchedKeyword)) {
						//echo $matchedKeyword."\n";
						$locationId = $keyword['related_id'];
						continue 2;
					}
				}
			}
			$user['locationId'] = $locationId;
			$user['locationIdBy'] = "KEYWORD";
		}

		if ($locationId == 0) {
			$matchedKeywords = $this->getKeywordByPattern($pattern, $user['full_name']);

			if (count($matchedKeywords) > 0) {
				foreach ($matchedKeywords as $key => $matchedKeyword) {
					$matchedKeyword = Emoji::Decode($matchedKeyword);
					//echo $matchedKeyword."\n";
					foreach ($locationKeyword as $keyword) {
						if (strtolower($keyword['keyword']) == strtolower($matchedKeyword)) {
							//echo $matchedKeyword."\n";
							$locationId = $keyword['related_id'];
							continue 2;
						}
					}
				}
				$user['locationId'] = $locationId;
				$user['locationIdBy'] = "KEYWORD";
			}

		}

		// echo $user['locationId'];
		// echo $user['locationIdBy'];
		// exit();
		// if($matches[0]){
		// 	print_r($matches[0]);
		// 	echo $matches[0][0]."->Keyword \n";
		// 	foreach ($locationKeyword as $keyword) {
		// 		if($keyword['keyword'] == $matches[0][0]){
		// 			$locationId = $keyword['related_id'];
		// 			continue;
		// 		}
		// 	}
		// 	$user['locationId'] = $locationId;
		// 	$user['locationIdBy'] = "KEYWORD";
		// }
		if ($locationId == 0) {
			if ($this->isJapanese($user['bio']) || $this->isJapanese($user['full_name'])) {
				//echo "A\n";
				$user['locationId'] = 5;
			} else if (preg_match('/[\x{AC00}-\x{D7A3}]/u', $user['bio']) > 0 || preg_match('/[\x{3130}-\x{318F}]/u', $user['bio']) > 0 ||
				preg_match('/[\x{AC00}-\x{D7A3}]/u', $user['full_name']) > 0 || preg_match('/[\x{3130}-\x{318F}]/u', $user['full_name']) > 0) {
				// echo "=====================>\n";
				// echo $user['bio']."\n\n";
				//echo "B\n";
				$user['locationId'] = 6;
			} else if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $user['bio']) > 0 || preg_match('/[\x{4e00}-\x{9fa5}]/u', $user['full_name']) > 0) {
			
				//echo "C\n";
				$user['locationId'] = 10; //ASPC

			} else {
				return $user;
			}
			$user['locationIdBy'] = "LANGUAGE";
		}		
		// echo "location id By: ".$user['locationIdBy']."\n";

		// echo "location id: ".$user['locationId']."\n";
		return $user;
	}
	public function getKeywordByPattern($pattern, $content)
	{
		$matchedKeywords = [];

		$matches = null;
		preg_match_all($pattern[0], $content, $matches, PREG_OFFSET_CAPTURE);
		//print_r($matches[0]);
		foreach ($matches[0] as $value) {
			$matchedKeywords[$value[1]] = $value[0];
		}
		if (isset($pattern[1])) {
			preg_match_all($pattern[1], $content, $matches, PREG_OFFSET_CAPTURE);
			foreach ($matches[0] as $value) {
				$matchedKeywords[$value[1]] = $value[0];
			}
		}
		if (isset($pattern[2])) {
			preg_match_all($pattern[2], $content, $matches, PREG_OFFSET_CAPTURE);
			foreach ($matches[0] as $value) {
				$matchedKeywords[$value[1]] = $value[0];
			}
		}
		ksort($matchedKeywords);
		return $matchedKeywords;
	}
	public function setPattern($keywords)
	{
		$pattern = '/\b(';
		$pattern2 = '/\b(';
		$pattern_zh = '/(';
		$numKeyword = [];
		$textKeyword = [];
		$zhKeyword = [];

		$returnPattern = [];
		foreach ($keywords as $key => $keyword) {
			if (substr($keyword['keyword'], 0, 1) == ".") {
				continue;
			}
			//$keyword['keyword'] = addslashes($keyword['keyword']);
			if (is_numeric($keyword['keyword'])) {
				array_push($numKeyword, $keyword['keyword']);

			} else if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $keyword['keyword']) > 0) {
				array_push($zhKeyword, $keyword['keyword']);

			} else {
				//ENG
				// array_push($textKeyword, strtoupper($keyword['keyword']));
				// array_push($textKeyword, strtolower($keyword['keyword']));
				// array_push($textKeyword, ucfirst($keyword['keyword']));
				// array_push($textKeyword, ucwords($keyword['keyword']));

				if (substr($keyword['keyword'], 0, 1) == "\\") {
					array_push($zhKeyword, $keyword['keyword']);
				} else {
					// array_push($textKeyword, strtoupper($keyword['keyword']));
					// array_push($textKeyword, strtolower($keyword['keyword']));
					// array_push($textKeyword, ucfirst($keyword['keyword']));
					// array_push($textKeyword, ucwords($keyword['keyword']));
					array_push($textKeyword, $keyword['keyword']);
				}
			}
		}
		foreach ($textKeyword as $key => $keyword) {
			$keyText = Emoji::Decode($keyword);
			if (substr($keyText, 0, 1) == "+") {
				$keyText = "\\" . $keyText;
			}
			if ($key != count($textKeyword) - 1) {
				$pattern .= $keyText . "|";
			} else {
				$pattern .= $keyText;
			}
		}

		$pattern .= ')\b/i';
		array_push($returnPattern, $pattern);
		//print_r($numKeyword);
		if (count($zhKeyword) > 1) {
			foreach ($zhKeyword as $key => $keyword) {
				$keyText = Emoji::Decode($keyword);
				if (substr($keyText, 0, 1) == "+") {
					$keyText = "\\" . $keyText;
				}
				if ($key != count($zhKeyword) - 1) {
					$pattern_zh .= $keyText . "|";
				} else {
					$pattern_zh .= $keyText;
				}
			}
			$pattern_zh .= ')/';
			// print_r($pattern);
			// echo "\n";
			// print_r($pattern2);
			// echo "\n";
			array_push($returnPattern, $pattern_zh);
		}

		if (count($numKeyword) > 1) {
			foreach ($numKeyword as $key => $keyword) {
				$keyText = Emoji::Decode($keyword);
				if (substr($keyText, 0, 1) == "+") {
					$keyText = "\\" . $keyText;
				}
				if ($key != count($numKeyword) - 1) {
					$pattern2 .= $keyText . "|";
				} else {
					$pattern2 .= $keyText;
				}
			}
			$pattern2 .= ')/';
			array_push($returnPattern, $pattern2);
		}
		return $returnPattern;
	}

	protected function getHashTagsFromString($str)
	{
		$REGEX = '/(#)(.+?(?=#|\s|$))/';
		$mentions = [];
		preg_match_all($REGEX, $str, $matches, PREG_SET_ORDER, 0);
		foreach ($matches as $key => $value) {
			array_push($mentions, $value[2]);
		}
		return $mentions;
	}

	public function isKanji($str)
	{
		return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
	}

	public function isHiragana($str)
	{
		return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
	}

	public function isKatakana($str)
	{
		return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
	}

	public function isJapanese($str)
	{
		return $this->isHiragana($str);
	}
	public function extractText($regex, $raw)
	{
		$matches = [];
		preg_match($regex, $raw, $matches);
		if (count($matches) > 0) {
			return $matches[0];
		} else {
			return null;
		}
	}
}

?>
