<?php
if(  !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}
if(  !class_exists('CoreModel') ) {
	require dirname(__FILE__). '/coreModel.php';
}
if( !class_exists('NormalModel') ) {
	require dirname(dirname(__FILE__)). '/model/normal.php';
}
require dirname(dirname(__FILE__)). '/vendor/autoload.php';

class Influencer extends CoreModel
{
	private $ig_id = null;
	private $fb_id = null;
	private $youtube_id = null;

	public function __construct()
	{
		parent::__construct();
		$this->normalModel  = new NormalModel;
		$socialPlatformList = $this->normalModel->getSocialPlatformList();

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
	public function getById($infId){
		$sql = "SELECT * FROM influencer where (influencer.fb_user_id IS NULL OR influencer.fb_user_id= 0) AND influencer.id = :id";
		$result = $this->db->readQuery($sql, [':id' => $infId]);
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	public function getAllInf(){
		$sql = "SELECT * FROM influencer where (influencer.fb_user_id IS NULL OR influencer.fb_user_id= 0)";
		$result = $this->db->readQuery($sql);
		if($result){
			return $result;
		}else{
			return false;
		}
	}

	public function getInfByIgId($igId){
		$sql = "SELECT influencer.id FROM influencer inner join ig_user on ig_user.id = influencer.ig_user_id where ig_user.igId = :igId";
		$result = $this->db->readQuery($sql, [':igId' => $igId]);
		if($result){
			return $result[0]['id'];
		}else{
			return false;
		}
	}

	public function getAllInfUser($locationId = null, $startNum = null, $limitNum = null) {

		$locationSql = '';
		if(!is_null($locationId)){
			$locationSql = " WHERE influencer.locationId = '".$locationId."' ";
		}
		$limitSql = '';
		if(!is_null($startNum) && !is_null($limitNum)){
			$limitSql = " LIMIT ".$limitNum." OFFSET ".$startNum;
		}

		$sql = "SELECT *, id as infId FROM `influencer` ".$locationSql." ORDER BY id ASC ".$limitSql;
		//echo $sql;
		return $this->db->readQuery($sql);
	}

	public function fetchAllInfUser($locationId = null, callable $callback = null, $startNum = null, $limitNum = null) {

		$locationSql = '';
		if(!is_null($locationId)){
			$locationSql = " WHERE influencer.locationId = '".$locationId."' ";
		}
		$limitSql = '';
		if(!is_null($startNum) && !is_null($limitNum)){
			$limitSql = " LIMIT ".$limitNum." OFFSET ".$startNum;
		}

		$sql = "SELECT * FROM `influencer` ".$locationSql." ORDER BY id ASC ".$limitSql;
		//echo $sql;
		return $this->db->fetchDb($sql,[],$callback);
	}

	public function fetchInfUser2($infId = null, callable $callback = null, $startNum = null, $limitNum = null) {

		$limitSql = '';
		if(!is_null($startNum) && !is_null($limitNum)){
			$limitSql = " LIMIT ".$limitNum." OFFSET ".$startNum;
		}

		$sql = "SELECT * FROM `influencer` WHERE id = ".$infId." ORDER BY id ASC ".$limitSql;
		//echo $sql;
		return $this->db->fetchDb($sql,[],$callback);
	}

	public function getAllInfIP($locationId){
		$sql = "SELECT inf.id AS infId, 
				igu.infPower AS igInfPower, 
				fbu.infPower AS fbInfPower,
				ytu.infPower AS ytInfPower, 
				igu.followerCount AS igFollowerCount, 
				fbu.fanCount AS fbFollowerCount,
				ytu.subscriberCount AS ytFollowerCount
					FROM influencer AS inf
					LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id 
					LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id 
					LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
					WHERE inf.locationId = '".$locationId."'";
		//echo $sql;
		return $this->db->readQuery($sql);
	}
	public function getAllInfIP2($infId){
		$sql = "SELECT inf.id AS infId, 
				igu.infPower AS igInfPower, 
				fbu.infPower AS fbInfPower, 
				ytu.infPower AS ytInfPower,
				igu.followerCount AS igFollowerCount, 
				fbu.fanCount AS fbFollowerCount,
				ytu.subscriberCount AS ytFollowerCount
					FROM influencer AS inf
					LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id 
					LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
					LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id 
					WHERE inf.id = '".$infId."'";
		//echo $sql;
		return $this->db->readQuery($sql);
	}
	public function updateNewIp($infId, $infPower){
			$sql = "UPDATE `influencer` SET
					`infPower` = :infPower
				WHERE `id`= :id";

			$args = [
				":id" 		=> $infId,
				":infPower"	=> $infPower
			];

		$this->db->runQuery($sql, $args);
	}

	public function getInflencerByIgUserId2($ig_user_id)
	{
		$sql = "SELECT * FROM `influencer` WHERE `ig_user_id` = :ig_user_id";
		return $result = $this->db->readQuery($sql, [":ig_user_id"=>$ig_user_id]);
	}

	public function getInflencerByIgUserId($ig_user_id)
	{
		$sql = "SELECT * FROM `influencer` WHERE `ig_user_id` = :ig_user_id";
		$result = $this->db->readQuery($sql, [':ig_user_id'=>$ig_user_id]);
		if($result) {
			return $result[0];
		}
		return ;
	}

	public function getInflencerById($infId)
	{
		$sql = "SELECT * FROM `influencer` WHERE `id` = :id";
		$result = $this->db->readQuery($sql, [':id'=>$infId]);
		if($result) {
			return $result[0];
		}
		return ;
	}

	public function getInflencerById2($infId, $hasIg = null)
	{
		if($hasIg){
			$sql = "SELECT *  FROM `influencer` 
			INNER JOIN `ig_user` ON influencer.ig_user_id=ig_user.id 
			WHERE `influencer`.`id` = :infId";
		}else{
			$sql = "SELECT *  FROM `influencer` 
			WHERE `influencer`.`id` = :infId";
		}
		$result = $this->db->readQuery($sql, [':infId' => $infId]);
		if($result) {
			return $result;
		}
		return ;
	}


	public function getInflencerInterestByIgUserId($influencer_id)
	{
		$sql = "SELECT * FROM `influencer_interest` WHERE `influencerId` = :infId";
		$result = $this->db->readQuery($sql, [':infId'=>$influencer_id]);
		return $result;
	}
	


	public function genIgTopInfluencer($loc_id, $limit, $type = null, $socialType = "IG", $isNewIp = false)
	{

		$socialId = $this->ig_id;
		

		if($socialType == "IG"){
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							igu.name AS igName,
							igu.followerCount,
							inf.profilePic,
							igu.rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId
						ORDER BY igu.rating DESC, igu.followerCount DESC Limit ".$limit;
			if($isNewIp){
				$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							igu.name AS igName,
							igu.followerCount,
							inf.profilePic,
							igu.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5 
						ORDER BY igu.infPower DESC, igu.followerCount DESC Limit ".$limit;
			}
		}else if ($socialType == "FB"){
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS fbName,
							fbu.fanCount AS followerCount,
							inf.profilePic,
							fbu.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5 
						ORDER BY fbu.infPower DESC, fbu.fanCount DESC Limit ".$limit;

			$socialId = $this->fb_id;
		}else if($socialType == "IGFB"){
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							fbu.id AS fbUserId,
							igu.id AS igUserId,
							(IFNULL(fbu.fanCount,0) + IFNULL(igu.followerCount, 0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5 
						ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} else if ($socialType == "YT") {
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							ytu.id AS ytUserId,
							ytu.subscriberCount AS followerCount,
							inf.profilePic,
							ytu.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = infIdentityId
						INNER JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						INNER JOIN loaction AS loc on loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5
						ORDER BY ytu.infPower DESC, ytu.subscriberCount DESC Limit ".$limit;

			$socialId = 3;
		} else if ($socialType == "IGYT") {
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							ytu.id AS ytUserId,
							igu.id AS igUserId,
							(IFNULL(ytu.subscriberCount,0) + IFNULL(igu.followerCount,0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5
						ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
				$socialId = 0;
		} else if ($socialType == "FBYT") {
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							ytu.id AS ytUserId,
							fbu.id AS fbUserId,
							(IFNULL(ytu.subscriberCount,0) + IFNULL(fbu.fanCount,0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5
						ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
				$socialId = 0;
		} else if ($socialType == "IGFBYT") {
			$sql = "SELECT	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							igu.id AS igUserId,
							fbu.id AS fbUserId,
							ytu.id AS ytUserId,
							(IFNULL(ytu.subscriberCount,0) + IFNULL(fbu.fanCount,0) + IFNULL(igu.followerCount,0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE loc.id = :locId AND inf.identityId != 1 AND inf.identityId != 5
						ORDER BY inf.infPowerDESC, follower Count DESC Limit ".$limit;
				$socialId = 0;
		}
		$topInfluencers = $this->db->readQuery($sql, [':locId' => $loc_id]);
		
		$updatedAt = time();
		foreach ($topInfluencers as $key => $influencer) {
			$prevRecord = $this->getPrevTopIgInfluencer($socialId, $type, $influencer, $loc_id, null, null, $isNewIp);
			$topInfluencers[$key]['prevRecord'] = $prevRecord;
		}

		$this->clearTopIgInfluencer($socialId, $type, $loc_id, null, null, $isNewIp);
	
		foreach ($topInfluencers as $key => $influencer) {
			$this->saveTopIgInfluencer($socialId, $type, $influencer, $loc_id, $updatedAt, $influencer['prevRecord'], ($key+1), null,null, $isNewIp);
		}
		return;
	}

	public function genIgTopInfluencerByCategory($loc_id, $cat_id, $limit, $type = null, $socialType = "IG", $isNewIp = false)
	{
		$socialId = $this->ig_id;
		if($socialType == "IG"){
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							igu.name AS igName,
							igu.followerCount,
							inf.profilePic,
							igu.rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY igu.rating DESC, igu.followerCount DESC Limit ".$limit;
			if($isNewIp){
				$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							igu.name AS igName,
							igu.followerCount,
							inf.profilePic,
							igu.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY igu.infPower DESC, igu.followerCount DESC Limit ".$limit;
			}
		}else if($socialType == "FB"){
			$socialId = $this->fb_id;
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							fbu.fanCount AS followerCount,
							inf.profilePic,
							fbu.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY fbu.infPower DESC, fbu.fanCount DESC Limit ".$limit;
		}else if($socialType == "IGFB"){
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							(IFNULL(fbu.fanCount,0) + IFNULL(igu.followerCount, 0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} elseif ($socialType == "YT") {
			$socialId = $this->yt_id;
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							ytu.subscriberCount AS followerCount,
							inf.profilePic,
							ytu.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY ytu.infPower DESC, ytu.subscriberCount DESC Limit ".$limit;
		} elseif ($socialType == "IGYT") {
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							(IFNULL(ytu.subscriberCount,0) + IFNULL(igu.followerCount, 0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} elseif ($socialType == "FBYT") {
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							(IFNULL(fbu.fanCount,0) + IFNULL(ytu.subscriberCount, 0)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} elseif ($socialType == "IGFBYT") {
			$sql = "SELECT  inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							(IFNULL(fbu.fanCount,0) + IFNULL(ytu.subscriberCount, 0) + IFNULL(igu.followerCount)) AS followerCount,
							inf.profilePic,
							inf.infPower AS rating,
							cat.id AS categoryId
						FROM influencer AS inf
						INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
						INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
						INNER JOIN category AS cat ON cat.id = cati.categoryId
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							cat.id = :catId
						GROUP BY inf.id ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		}
		$topInfluencers = $this->db->readQuery($sql, [':locId' => $loc_id, ':catId'=>$cat_id]);
		//echo $sql."\n";
		$updatedAt = time();
		foreach ($topInfluencers as $infKey => $influencer) {
			$prevRecord = $this->getPrevTopIgInfluencer($socialId, $type, $influencer, $loc_id, "CATEGORY", $cat_id, $isNewIp);
			$topInfluencers[$infKey]['prevRecord'] = null;
			if(isset($prevRecord)){
				$topInfluencers[$infKey]['prevRecord'] = $prevRecord;
			}
		}
		$this->clearTopIgInfluencer($socialId, $type, $loc_id, "CATEGORY", $cat_id, $isNewIp);
		foreach ($topInfluencers as $key => $influencer) {
			$this->saveTopIgInfluencer($socialId, $type, $influencer, $loc_id, $updatedAt, $influencer['prevRecord'], ($key+1), "CATEGORY", $cat_id, $isNewIp);
		}

		return;
	}

	public function genIgTopInfluencerByIdentity($loc_id, $identity_id, $limit, $type = null, $socialType = "IG", $isNewIp = false)
	{
		$socialId = $this->ig_id;
		if($socialType == "IG"){
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							igu.name AS igName,
							igu.followerCount,
							inf.profilePic,
							igu.rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							ide.id = :identityId
						ORDER BY igu.rating DESC, igu.followerCount DESC Limit ".$limit;
			if($isNewIp){
				$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							igu.name AS igName,
							igu.followerCount,
							inf.profilePic,
							igu.infPower As rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							ide.id = :identityId
						ORDER BY igu.infPower DESC, igu.followerCount DESC Limit ".$limit;
			}
		}else if($socialType == "FB"){
			$socialId = $this->fb_id;
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							fbu.fanCount AS followerCount,
							inf.profilePic,
							fbu.infPower As rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							ide.id = :identityId
						ORDER BY fbu.infPower DESC, fbu.fanCount DESC Limit ".$limit;
		}else if($socialType == "IGFB"){
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							(IFNULL(fbu.fanCount,0) + IFNULL(igu.followerCount, 0)) AS followerCount,
							inf.profilePic,
							inf.infPower As rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
						LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							ide.id = :identityId
						ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} elseif ($socialType == "YT") {
			$socialId = $this->yt_id;
			$sql = "SELECT 	inf.id AS influencerId,
							ide.id AS identityId,
							inf.name AS name,
							ytu.subscriberCount AS followerCount,
							inf.profilePic,
							ytu.infPower As rating
						FROM influencer AS inf
						INNER JOIN identity AS ide ON ide.id = inf.identityId
						INNER JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
						INNER JOIN location AS loc ON loc.id = inf.locationId
						WHERE
							loc.id = :locId AND
							ide.id = :identityId
						ORDER BY ytu.infPower DESC, ytu.subscriberCount DESC Limit ".$limit;
		} elseif ($socialType == "IGYT") {
			$sql = "SELECT 	inf.id AS influencerId,
						ide.id AS identityId,
						inf.name AS name,
						(IFNULL(ytu.subscriberCount,0) + IFNULL(igu.followerCount, 0)) AS followerCount,
						inf.profilePic,
						inf.infPower As rating
					FROM influencer AS inf
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
					LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE
						loc.id = :locId AND
						ide.id = :identityId
					ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} elseif ($socialType == "FBYT") {
			$sql = "SELECT 	inf.id AS influencerId,
						ide.id AS identityId,
						inf.name AS name,
						(IFNULL(ytu.subscriberCount,0) + IFNULL(fbu.fanCount, 0)) AS followerCount,
						inf.profilePic,
						inf.infPower As rating
					FROM influencer AS inf
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
					LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE
						loc.id = :locId AND
						ide.id = :identityId
					ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		} elseif ($socialType == "IGFBYT") {
			$sql = "SELECT 	inf.id AS influencerId,
						ide.id AS identityId,
						inf.name AS name,
						(IFNULL(ytu.subscriberCount,0) + IFNULL(fbu.fanCount, 0) + IFNULL(igu.followerCount, 0)) AS followerCount,
						inf.profilePic,
						inf.infPower As rating
					FROM influencer AS inf
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					LEFT JOIN ig_user AS igu ON igu.id = inf.ig_user_id
					LEFT JOIN fb_user AS fbu ON fbu.id = inf.fb_user_id
					LEFT JOIN yt_user AS ytu ON ytu.id = inf.yt_user_id
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE
						loc.id = :locId AND
						ide.id = :identityId
					ORDER BY inf.infPower DESC, followerCount DESC Limit ".$limit;
			$socialId = 0;
		}
		$topInfluencers = $this->db->readQuery($sql, [':locId'=>$loc_id, ':identityId'=>$identity_id]);
		//echo $sql."\n";
		//print_r($topInfluencers);
		$updatedAt = time();
		foreach ($topInfluencers as $infKey => $influencer) {
			$prevRecord = $this->getPrevTopIgInfluencer($socialId, $type, $influencer, $loc_id, "IDENTITY", $identity_id, $isNewIp);

			//print_r($topInfluencers[$infKey]);
			$topInfluencers[$infKey]['prevRecord'] = null;
			if(isset($prevRecord)){
				$topInfluencers[$infKey]['prevRecord'] = $prevRecord;
			}
		}
		$this->clearTopIgInfluencer($socialId, $type, $loc_id, "IDENTITY", $identity_id, $isNewIp);
		foreach ($topInfluencers as $key => $influencer) {
			$this->saveTopIgInfluencer($socialId, $type, $influencer, $loc_id, $updatedAt, $influencer['prevRecord'], ($key+1), "IDENTITY", $identity_id, $isNewIp);
		}
		return;
	}

	public function getPrevTopIgInfluencer($socialId, $genType, $influencer, $loc_id,  $type = null, $related_id = null, $isNewIp = null)
	{
		$tableName = 'top_influencer';
		if($genType === 'MONTHLY'){
			$tableName = 'monthly_top_influencer';
		}

		if($isNewIp){
			$tableName = 'top_influencer_new';
			if($genType === 'MONTHLY'){
				$tableName = 'monthly_top_influencer_new';
			}
		}
		$sql = "SELECT rank, updatedAt
					FROM `".$tableName."`
					WHERE
						socialPlatformId = :socialId AND
						locationId = :loc_id AND
						influencerId = :influencerId AND
						type = :type AND
						relatedId = :relatedId
					ORDER BY updatedAt DESC Limit 1";

		$prevInfluencer = $this->db->readQuery($sql, [':socialId'=>$socialId, ':loc_id'=>$loc_id, ':influencerId'=>$influencer['influencerId'], ':type'=>$type, ':relatedId'=>$related_id]);
		if(count($prevInfluencer) != 0){
			return $prevInfluencer[0];
		}
		return null;
	}

	public function clearTopIgInfluencer($socialId, $genType, $locationId, $type = null, $relatedId = null, $isNewIp = null)
	{
		$tableName = 'top_influencer';
		if($genType === 'MONTHLY'){
			$tableName = 'monthly_top_influencer';
		}
		if($isNewIp){
			$tableName = 'top_influencer_new';
			if($genType === 'MONTHLY'){
				$tableName = 'monthly_top_influencer_new';
			}
		}
		$sql = "DELETE FROM ".$tableName." WHERE `socialPlatformId` = :socialId AND `locationId` = :locId AND `type` = '".$type."' AND `relatedId` = '".$relatedId."'";
		$this->db->runQuery($sql, [':socialId'=>$socialId, ':locId'=>$locationId]);

		// $this->db->runQuery("DELETE FROM top_influencer_new
		// 		WHERE `socialPlatformId` = :socialId AND
		// 		`locationId` = :locId AND
		// 		`type` = '' AND
		// 		`relatedId` = ''",[':socialId'=>$socialId, ':locId'=>$locationId]);
	}

	public function saveTopIgInfluencer( $socialId, $genType, $influencer, $loc_id, $updatedAt, $prevRecord, $rank, $type = null, $related_id = null, $isNewIp = null)
	{
		$tableName = 'top_influencer';
		if($genType === 'MONTHLY'){
			$tableName = 'monthly_top_influencer';
		}
		if($isNewIp){
			$tableName = 'top_influencer_new';
			if($genType === 'MONTHLY'){
				$tableName = 'monthly_top_influencer_new';
			}
		}
		$prevRank = null;
		$prevLoggedAt = null;
		if(isset($prevRecord['rank'])){
			$prevRank = $prevRecord['rank'];
		}
		if(isset($prevRecord['updatedAt'])){
			$prevLoggedAt = $prevRecord['updatedAt'];
		}

		$sql = "INSERT INTO `".$tableName."` (
							`socialPlatformId`,
							`locationId`,
							`influencerId`,
							`type`,
							`relatedId`,
							`followerCount`,
							`rating`,
							`rank`,
							`prevRank`,
							`prevLoggedAt`,
							`updatedAt`

						) VALUES('".$socialId."',
							'".$loc_id."',
							'".$influencer['influencerId']."',
							'".$type."',
							'".$related_id."',
							'".$influencer['followerCount']."',
							'".$influencer['rating']."',
							'".$rank."',
							'".$prevRank."',
							'".$prevLoggedAt."',
							'".$updatedAt."'
						)";
						//echo $sql."\n";
		$this->db->runQuery($sql);

		$this->saveTopIgInfluencerLog($socialId, $genType, $influencer, $loc_id, $updatedAt, $rank, $prevRank, $prevLoggedAt, $type, $related_id, $isNewIp);
	}

	public function saveTopIgInfluencerLog($socialId, $genType, $influencer, $loc_id, $updatedAt, $rank, $prevRank, $prevLoggedAt, $type = null, $related_id = null, $isNewIp = null)
	{
		$tableName = 'top_influencer_log';
		if($genType === 'MONTHLY'){
			$tableName = 'monthly_top_influencer_log';
		}
		if($isNewIp){
			$tableName = 'top_influencer_new_log';
			if($genType === 'MONTHLY'){
				$tableName = 'monthly_top_influencer_new_log';
			}
		}
		$sql = "INSERT INTO `".$tableName."` (
							`socialPlatformId`,
							`locationId`,
							`influencerId`,
							`type`,
							`relatedId`,
							`followerCount`,
							`rating`,
							`rank`,
							`prevRank`,
							`prevLoggedAt`,
							`loggedAt`

						) VALUES('".$socialId."',
							'".$loc_id."',
							'".$influencer['influencerId']."',
							'".$type."',
							'".$related_id."',
							'".$influencer['followerCount']."',
							'".$influencer['rating']."',
							'".$rank."',
							'".$prevRank."',
							'".$prevLoggedAt."',
							'".$updatedAt."'
						)";

		$this->db->runQuery($sql);
	}


	public function genTopPost($loc_id, $limit, $type = "IG")
	{
		//echo "load top post \n";
		if($type == "FB") {
			$sql = "SELECT igpop.fb_post_id, igpop.fb_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount, igpop.shareCount, igpop.tagCount,
					igpop.pictureUrl, igpop.link, igpop.postDate
					FROM fb_latest_post AS igpop
					INNER JOIN fb_user AS igu ON igu.id = igpop.fb_user_id
					INNER JOIN influencer AS inf ON inf.fb_user_id = igu.id
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :locId AND
					igpop.postDate >= :postDate 
					GROUP BY igpop.fb_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;

		} elseif ($type == 'YT') {
			$sql = "SELECT igpop.yt_video_id, igpop.yt_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount, igpop.dislikeCount, igpop.pictureUrl, igpop.postDate
					FROM yt_latest_post AS igpop
					INNER JOIN yt_user AS igu ON igu.id = igpop.yt_user_id
					INNER JOIN influencer as inf ON inf.yt_user_id = igu.id
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :locId AND
					igpop.postDate >= :postDate
					GROUP BY igpop.yt_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;
		} else if($type == 'IG') {
			$sql = "SELECT igpop.ig_post_id, igpop.ig_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount,
					igpop.pictureUrl, igpop.videoUrl, igpop.link, igpop.postDate
					FROM ig_latest_post AS igpop
					INNER JOIN ig_user AS igu ON igu.id = igpop.ig_user_id
					INNER JOIN influencer AS inf ON inf.ig_user_id = igu.id
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :locId AND
					igpop.postDate >= :postDate 
					GROUP BY igpop.ig_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;
		}
		//echo $sql."\n";

		$topPosts = $this->db->readQuery($sql, [':locId'=>$loc_id, ':postDate'=>(time()-(60*60*24))]);
		//echo $this->ig_id;
		$socialId = $this->ig_id;
		if($type == "FB"){
			$socialId = $this->fb_id;
		} elseif ($type == "YT") {
			$socialId = $this->yt_id;
		}

		$updatedAt = time();
		//echo "Clear top post\n";
		$this->clearTopPost($socialId, $loc_id);


		//echo "Save top post\n";
		foreach ($topPosts as $key => $topPost) {
			//echo "Save ============\n";
			$this->saveTopPost($type, $topPost, $loc_id, $updatedAt, ($key+1));
		}
	}

	// public function genFbTopPost($loc_id, $limit)
	// {
	// 	$sql = "SELECT igpop.fb_post_id, igpop.fb_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
	// 			igpop.content, igpop.likeCount, igpop.commentCount, igpop.shareCount, igpop.tagCount,
	// 			igpop.pictureUrl, igpop.link, igpop.postDate
	// 			FROM fb_latest_post AS igpop
	// 			INNER JOIN fb_user AS igu ON igu.id = igpop.fb_user_id
	// 			INNER JOIN influencer AS inf ON inf.fb_user_id = igu.id
	// 			INNER JOIN identity AS ide ON ide.id = inf.identityId
	// 			INNER JOIN location AS loc ON loc.id = inf.locationId
	// 			WHERE loc.id = ".$loc_id." AND
	// 			igpop.postDate >= '".(time()-(60*60*24))."'
	// 			GROUP BY igpop.fb_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;

	// 	$topPosts = $this->db->getQuery($sql);
	// 	$updatedAt = time();
	// 	$this->clearFbTopPost('FB', $loc_id);

	// 	foreach ($topPosts as $key => $topPost) {
	// 		$this->saveTopPost($topPost, $loc_id, $updatedAt, ($key+1));
	// 	}
	// }

	public function genTopPostByIdentity($loc_id, $identity_id, $limit, $type = "IG")
	{
		if($type == "FB") {
			$sql = "SELECT igpop.fb_post_id, igpop.fb_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount,
					igpop.pictureUrl, igpop.link, igpop.postDate
					FROM fb_latest_post AS igpop
					INNER JOIN fb_user AS igu ON igu.id = igpop.fb_user_id
					INNER JOIN influencer AS inf ON inf.fb_user_id = igu.id
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :locId AND ide.id = :identityId  AND
					igpop.postDate >= :postDate
					GROUP BY igpop.fb_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;
		} elseif($type == "YT") {
			$sql = "SELECT igpop.yt_post_id, igpop.yt_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount,
					igpop.pictureUrl, igpop.postDate
					FROM yt_latest_post AS igpop
					INNER JOIN yt_user AS igu ON igu.id = igpop.yt_user_id
					INNER JOIN influencer AS inf ON inf.yt_user_id = igu.id
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :locId AND ide.id = :identityId  AND
					igpop.postDate >= :postDate
					GROUP BY igpop.yt_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;
		} else{
			$sql = "SELECT igpop.ig_post_id, igpop.ig_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
				igpop.content, igpop.likeCount, igpop.commentCount,
				igpop.pictureUrl, igpop.videoUrl, igpop.link, igpop.postDate
				FROM ig_latest_post AS igpop
				INNER JOIN ig_user AS igu ON igu.id = igpop.ig_user_id
				INNER JOIN influencer AS inf ON inf.ig_user_id = igu.id
				INNER JOIN identity AS ide ON ide.id = inf.identityId
				INNER JOIN location AS loc ON loc.id = inf.locationId
				WHERE loc.id = :locId AND ide.id = :identityId  AND
				igpop.postDate >=:postDate
				GROUP BY igpop.ig_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC LIMIT ".$limit;
		}
		$topPosts = $this->db->readQuery($sql, [':locId'=>$loc_id, ':identityId'=>$identity_id, ':postDate'=>(time()-(60*60*24))]);

		$updatedAt = time();

		$socialId = $this->ig_id;
		if($type == "FB"){
			$socialId = $this->fb_id;
		} elseif ($type == "YT") {
			$socialId = $this->yt_id;
		}
		$this->clearTopPost($socialId, $loc_id, "IDENTITY", $identity_id);

		foreach ($topPosts as $key => $topPost) {
			//echo "Save ============\n";
			$this->saveTopPost($type, $topPost, $loc_id, $updatedAt, ($key+1), "IDENTITY", $identity_id);
		}
	}

	public function genTopPostByCategory($loc_id, $cat_id, $limit, $type = "IG")
	{
		if($type == "FB") {
			$sql = "SELECT igpop.fb_post_id, igpop.fb_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount,
					igpop.pictureUrl, igpop.link, igpop.postDate
					FROM fb_latest_post AS igpop
					INNER JOIN fb_user AS igu ON igu.id = igpop.fb_user_id
					INNER JOIN influencer AS inf ON inf.fb_user_id = igu.id
					INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
					INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
					INNER JOIN category AS cat ON cat.id = cati.categoryId
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :loc_id AND cat.id = :catId AND
					igpop.postDate >= :postDate 
					GROUP BY igpop.fb_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC  LIMIT ".$limit;
		} elseif ($type == "YT") {
			$sql = "SELECT igpop.yt_post_id, igpop.yt_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
					igpop.content, igpop.likeCount, igpop.commentCount,
					igpop.pictureUrl, igpop.postDate
					FROM yt_latest_post AS igpop
					INNER JOIN yt_user AS igu ON igu.id = igpop.yt_user_id
					INNER JOIN influencer AS inf ON inf.yt_user_id = igu.id
					INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
					INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
					INNER JOIN category AS cat ON cat.id = cati.categoryId
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :loc_id AND cat.id = :catId AND
					igpop.postDate >= :postDate 
					GROUP BY igpop.yt_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC  LIMIT ".$limit;
		} else {
			$sql = "SELECT igpop.ig_post_id, igpop.ig_user_id, igpop.score, loc.id AS locationId, inf.id AS influencerId,
				igpop.content, igpop.likeCount, igpop.commentCount,
				igpop.pictureUrl, igpop.videoUrl, igpop.link, igpop.postDate
				FROM ig_latest_post AS igpop
				INNER JOIN ig_user AS igu ON igu.id = igpop.ig_user_id
				INNER JOIN influencer AS inf ON inf.ig_user_id = igu.id
				INNER JOIN influencer_interest AS infi ON infi.influencerId = inf.id
				INNER JOIN category_interest AS cati ON cati.interestId = infi.interestId
				INNER JOIN category AS cat ON cat.id = cati.categoryId
				INNER JOIN identity AS ide ON ide.id = inf.identityId
				INNER JOIN location AS loc ON loc.id = inf.locationId
				WHERE loc.id = :loc_id AND cat.id = :catId AND
				igpop.postDate >= :postDate 
				GROUP BY igpop.ig_user_id ORDER BY igpop.score DESC, igpop.likeCount DESC  LIMIT ".$limit;
		}
		//echo $sql."\n";
		$topPosts = $this->db->readQuery($sql, [':locId'=>$loc_id, ':catId'=>$cat_id, ':postDate'=>(time()-(60*60*24))]);
		$updatedAt = time();

		$socialId = $this->ig_id;
		if($type == "FB"){
			$socialId = $this->fb_id;
		}
		// foreach ($topPosts as $key => $topPost) {
		// 	$prevRecord = $this->getPrevTopPost($topPost['ig_post_id'], $loc_id, "CATEGORY", $cat_id);
		// 	$topPosts[$key]['prevRecord'] = $prevRecord;
		// }
		$this->clearTopPost($socialId, $loc_id, "CATEGORY", $cat_id);

		foreach ($topPosts as $key => $topPost) {
			$this->saveTopPost($type, $topPost, $loc_id, $updatedAt, ($key+1), "CATEGORY", $cat_id);
		}
	}

	// public function getPrevTopPost( $ig_post_id, $loc_id,  $type = null, $related_id = null)
	// {
	// 	$sql = "SELECT rank, updatedAt
	// 				FROM top_post
	// 				WHERE
	// 					socialPlatformId = ".$this->ig_id." AND
	// 					locationId = ".$loc_id." AND
	// 					ig_post_id = '".$ig_post_id."' AND
	// 					type = '".$type."' AND
	// 					relatedId = '".$related_id."'
	// 				ORDER BY updatedAt DESC Limit 1";
	// 	$prevLog = $this->db->getQuery($sql);
	// 	if(count($prevLog) != 0){
	// 		return $prevLog[0];
	// 	}
	// 	return null;
	// }
	public function clearTopPost($soicalType = "IG", $locationId, $type = null, $relatedId = null)
	{
		if($soicalType == 'FB'){
			$sql = "DELETE FROM top_post
				WHERE `socialPlatformId` = '".$this->fb_id."' AND
				`locationId` = :locationId AND
				`type` = '".$type."' AND
				`relatedId` = '".$relatedId."'";
		} elseif ($soicialType == "YT") {
			$sql = "DELETE FROM top_post
				WHERE `socialPlatformId` = '".$this->yt_id."' AND
				`locationId` = :locationId AND
				`type` = '".$type."' AND
				`relatedId` = '".$relatedId."'";
		} else{
			$sql = "DELETE FROM top_post
				WHERE `socialPlatformId` = '".$this->ig_id."' AND
				`locationId` = :locationId AND
				`type` = '".$type."' AND
				`relatedId` = '".$relatedId."'";
		}
		$this->db->runQuery($sql, [':locationId'=>$locationId]);
	}

	public function saveTopPost($socialType, $post, $loc_id, $updatedAt, $rank, $type = null, $related_id = null)
	{
		$relatedTableId = 'ig_post_id';
		$socialId = $this->ig_id;
		$videoUrl = isset($post['videoUrl'])?$post['videoUrl']:null;

		if($socialType == "FB"){
			$socialId = $this->fb_id;
			$relatedTableId = 'fb_post_id';
			$videoUrl = null;
		}
		if ($socialType == "YT") {

		}
		$sql = "INSERT INTO `top_post` (
							`socialPlatformId`,
							`locationId`,
							`influencerId`,
							`type`,
							`relatedId`,
							`score`,
							`ig_post_id`,
							`content`,
							`likeCount`,
							`commentCount`,
							`pictureUrl`,
							`videoUrl`,
							`link`,
							`postDate`,
							`rank`,
							`updatedAt`

						) VALUES('".$socialId."',
							'".$loc_id."',
							'".$post['influencerId']."',
							'".$type."',
							'".$related_id."',

							'".$post['score']."',
							'".$post[$relatedTableId]."',
							:content,
							'".$post['likeCount']."',
							'".$post['commentCount']."',

							'".$post['pictureUrl']."',
							'".$videoUrl."',
							'".$post['link']."',
							'".$post['postDate']."',
							'".$rank."',
							'".$updatedAt."'
						)";
						//echo $sql."\n\n";
		
		$this->db->runQuery($sql, [':content' => $post['content']]);

		$this->saveTopPostLog($post, $loc_id, $updatedAt, $rank, $type, $related_id, $relatedTableId, $videoUrl);
	}

	public function saveTopPostLog($post, $loc_id, $updatedAt, $rank, $type = null, $related_id = null, $relatedTableId = null, $videoUrl = null)
	{
		$sql = "INSERT INTO `top_post_log` (
							`socialPlatformId`,
							`locationId`,
							`influencerId`,
							`type`,
							`relatedId`,
							`score`,
							`ig_post_id`,
							`content`,
							`likeCount`,
							`commentCount`,
							`pictureUrl`,
							`videoUrl`,
							`link`,
							`postDate`,
							`rank`,
							`loggedAt`

						) VALUES('".$this->ig_id."',
							'".$loc_id."',
							'".$post['influencerId']."',
							'".$type."',
							'".$related_id."',
							'".$post['score']."',
							'".$relatedTableId."',
							:content,
							'".$post['likeCount']."',
							'".$post['commentCount']."',
							'".$post['pictureUrl']."',
							'".$videoUrl."',
							'".$post['link']."',
							'".$post['postDate']."',
							'".$rank."',
							'".$updatedAt."'
						)";
		$this->db->runQuery($sql, [':content' => $post['content']]);
	}

	public function insert( $user, $ig_user_id = null, $fb_user_id = null )
	{
		$snapchat = null;
		$weibo = null;
		$linkedin = null;
		$newInfluencerId = null;
		$verified = null;
		$sourceFrom = "MANUAL";
		$contact_person = null;
		if(isset($user['snapchat'])){
			$snapchat = $user['snapchat'];
		}
		if(isset($user['weibo'])){
			$weibo = $user['weibo'];
		}
		if(isset($user['linkedin'])){
			$linkedin = $user['linkedin'];
		}
		if(isset($user['newInfluencerId'])){
			$newInfluencerId = $user['newInfluencerId'];
		}
		if(isset($user['verified'])){
			$verified = $user['verified'];
		}
		if(isset($user['sourceFrom'])){

			$sourceFrom = $user['sourceFrom'];
		}
		if(isset($user['contact_person'])){
			$contact_person = $user['contact_person'];
		}	
		$fbId = null;
		if(isset($user['fbId'])){
			$fbId = $user['fbId'];
		}
		$facebook = null;
		if(isset($user['facebook'])){
			$facebook = $user['facebook'];
		}

		$twitter = null;
		if(isset($user['twitter'])){
			$twitter = $user['twitter'];
		}
		$isGroup = null;
		if(isset($user['isGroup'])){
			$isGroup = $user['isGroup'];
		}
		$ethnicity = null;
		if(isset($user['ethnicity'])){
			$ethnicity = $user['ethnicity'];
		}
		$language_id = null;
		if(isset($user['language_id'])){
			$language_id = $user['language_id'];
		}
		$sql = "INSERT INTO influencer (`name`,
										`profilePic`,
										`content`,
										`gender`,
										`fbId`,
										`facebook`,
										`instagram`,
										`youtube`,
										`twitter`,
										`website`,
										`snapchat`,
										`weibo`,
										`linkedin`,
										`phone`,
										`email`,
										`contactPerson`,
										`isGroup`,
										`identityId`,
										`locationId`,
										`ig_user_id`,
										`fb_user_id`,
										`yt_user_id`,
										`user_id`,
										`verified`,
										`sourceFrom`,
										`ethnicity`,
										`language_id`,
										`createdAt`,
										`updatedAt`
									) VALUES(
										:name,
										'".$user['profilePic']."',
										:bio,
										'".$user['gender']."',
										'".$fbId."',
										'".$facebook."',
										'".$user['username']."',
										'".$user['youtube']."',
										'".$twitter."',
										'".$user['website']."',
										'".$snapchat."',
										'".$weibo."',
										'".$linkedin."',
										'".$user['phone']."',
										:email,
										:contact_person,
										'".$isGroup."',
										'".$user['identity_id']."',
										'".$user['location_id']."',
										'".$ig_user_id."',
										'".$fb_user_id."',
										'".$yt_user_id."',
										'".$newInfluencerId."',
										'".$verified."',
										'".$sourceFrom."',
										:ethnicity,
										:language_id,
										'".time()."',
										'".time()."'
									)";
		

		return $this->db->runQuery($sql, array(
				':name' => $user['name'],
				':bio' => $user['bio'],
				':email' => $user['email'],
				':ethnicity' => $ethnicity,
				':language_id' => $language_id,
				':contact_person' => $contact_person
			));
	}

	public function insertInfluencerInterest($influencer_id, $interest)
	{
		$sql = "INSERT INTO influencer_interest (`influencerId`,
											`interestId`
											) VALUES('".$influencer_id."', '".$interest."')";
		$this->db->runQuery($sql);
	}
	public function insertInfluencerLanguage($influencer_id, $language)
	{
		$sql = "INSERT INTO influencer_language (`influencerId`,
											`languageId`
											) VALUES('".$influencer_id."', '".$language."')";
		$this->db->runQuery($sql);
	}

	public function getTopIgInfluencer( $loc_id,  $type = null, $related_id = null)
	{
		$sql = "SELECT igu.followerCount, inf.gender, igu.name, igu.postCount, igu.likeCount, ide.name AS identity, igu.profilePic, igu.rating, igu.igId, inf.id AS influencerId
					FROM `top_influencer` AS tinf
					INNER JOIN `influencer` AS inf ON inf.id = tinf.influencerId
					INNER JOIN `identity` AS ide ON ide.id = inf.identityId
					INNER JOIN `ig_user` AS igu ON igu.id = inf.ig_user_id
					WHERE
						tinf.socialPlatformId = ".$this->ig_id." AND
						tinf.locationId = ".$loc_id." AND
						tinf.type = '".$type."' AND
						tinf.relatedId = '".$related_id."'
					ORDER BY tinf.rating DESC";

		return $this->db->readQuery($sql);
	}

	public function getTopInfluencerByLocation( $loc_id=1, $socialPlatformId=1, $isNew = false)
	{
		$tableName = "top_influencer";
		if($isNew){
			$tableName = "top_influencer_new";
		}
		$sql = "SELECT * FROM ".$tableName." AS tinf
					WHERE tinf.locationId = ".$loc_id." AND `socialPlatformId` = ".$socialPlatformId." 
					ORDER BY tinf.type";

		return $this->db->readQuery($sql);
	}
	
	public function getTopIgPost( $loc_id,  $type = null, $related_id = null)
	{
		$sql = "SELECT igp.link, igp.commentCount, igp.likeCount, igp.ig_user_id, igp.videoUrl, igp.pictureUrl,
					igu.igId
					FROM `top_post` AS tpost
					INNER JOIN `influencer` AS inf ON inf.id = tpost.influencerId
					INNER JOIN `identity` AS ide ON ide.id = inf.identityId
					INNER JOIN `ig_post` AS igp ON igp.ig_post_id = tpost.ig_post_id
					INNER JOIN `ig_user` AS igu ON igu.id = igp.ig_user_id
					WHERE
						tpost.socialPlatformId = ".$this->ig_id." AND
						tpost.locationId = ".$loc_id." AND
						tpost.type = '".$type."' AND
						tpost.relatedId = '".$related_id."'
					ORDER BY tpost.score DESC";

		return $this->db->readQuery($sql);
	}

	public function getTopPostByLocation( $loc_id=1, $socialPlatformId=1)
	{
		$sql = "SELECT * FROM `top_post` AS tpost
					WHERE tpost.locationId = :locationId AND `socialPlatformId` = :socialPlatformId 
					ORDER BY tpost.type";

		return $this->db->readQuery($sql, [':locationId' => $loc_id, ':socialPlatformId' => $socialPlatformId]);
	}

	public function genIgWeeklyTopInfluencer($loc_id, $limit)
	{
		$sql = "SELECT 	inf.id AS influencerId,
						ide.id AS identityId,
						igu.name AS igName,
						igu.followerCount,
						igu.followerPercentage,
						igu.interactionPercentage,
						igu.weeklyFollowerCount,
						inf.profilePic,
						igu.weeklyScore,
						igu.rating,
						igu.weeklyLoggedAt,
						igu.updatedAt AS igUserUpdatedAt

					FROM influencer AS inf
					INNER JOIN identity AS ide ON ide.id = inf.identityId
					INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
					INNER JOIN location AS loc ON loc.id = inf.locationId
					WHERE loc.id = :locId AND igu.updatedAt >= ".(time()-(86400*2))." AND inf.identityId != 0 AND inf.identityId != 19 AND inf.identityId != 20
					ORDER BY igu.followerPercentage DESC";
				
		$topInfluencers = $this->db->readQuery($sql, [':locId'=>$loc_id]);
		$updatedAt = time();

		$prevInfRecord = $this->getIgPrevWeeklyTopInfluencer( $loc_id );
		foreach ($topInfluencers as $key => $influencer) {
			echo '.';
			foreach ($prevInfRecord as $pkey => $value) {
				$topInfluencers[$key]['prevRecord'] = null;
				if($value['influencerId'] == $influencer['influencerId']){
					$topInfluencers[$key]['prevRecord'] = $value;
					continue;
				}
			}
			$topInfluencers[$key]['followerPercentageRank'] = ($key+1);
		}

		echo "\n";
		usort($topInfluencers, function($item1, $item2){
		    if ($item1['interactionPercentage'] == $item2['interactionPercentage']) return 0;
		    return ($item1['interactionPercentage'] < $item2['interactionPercentage']) ? 1 : -1;
		});
		foreach ($topInfluencers as $key => $influencer) {
			$topInfluencers[$key]['interactionPercentageRank'] = ($key+1);
		}
		usort($topInfluencers, function($item1, $item2){
		    if ($item1['weeklyFollowerCount'] == $item2['weeklyFollowerCount']) return 0;
		    return ($item1['weeklyFollowerCount'] < $item2['weeklyFollowerCount']) ? 1 : -1;
		});
		foreach ($topInfluencers as $key => $influencer) {
			$topInfluencers[$key]['weeklyFollowerCountRank'] = ($key+1);
			$topInfluencers[$key]['weeklyRank'] = ($topInfluencers[$key]['weeklyFollowerCountRank']+$influencer['followerPercentageRank']+$influencer['interactionPercentageRank']);
			//$topInfluencers[$key]['weeklyRank'] = ($topInfluencers[$key]['weeklyFollowerCountRank']+$influencer['interactionPercentageRank']);
		}
		usort($topInfluencers, function($item1, $item2){
		    if ($item1['weeklyRank'] == $item2['weeklyRank']) return 0;
		    return ($item1['weeklyRank'] > $item2['weeklyRank']) ? 1 : -1;
		});
		$topInfluencersTemp = $topInfluencers;
		$topInfluencers = array();
		foreach ($topInfluencersTemp as $key => $value) {
			array_push($topInfluencers, $value);
			if($key >= $limit-1){
				break;
			}
		}
		//print_r($topInfluencers);
		$this->clearIgWeeklyTopInfluencer($loc_id);
		foreach ($topInfluencers as $key => $influencer) {
		 	$this->saveIgWeeklyTopInfluencer($influencer, $loc_id, $updatedAt, $influencer['prevRecord'], ($key+1));
		}
		return;
	}

	public function getIgPrevWeeklyTopInfluencer(  $loc_id, $influencer = null)
	{
		$sql = "SELECT influencerId, rank, updatedAt
					FROM weekly_top_influencer
					WHERE
						socialPlatformId = ".$this->ig_id." AND
						locationId = ".$loc_id;
		if($influencer){
			$sql.=" AND influencerId = ".$influencer['influencerId'];
		}
		$sql.=" ORDER BY updatedAt DESC";

		$prevInfluencer = $this->db->readQuery($sql);
		if($influencer){
			if(count($prevInfluencer) != 0){
				return $prevInfluencer[0];
			}
		}else{
			return $prevInfluencer;
		}
		return null;
	}

	public function clearIgWeeklyTopInfluencer( $locationId )
	{
		$sql = "DELETE FROM weekly_top_influencer
				WHERE `socialPlatformId` = '".$this->ig_id."' AND
				`locationId` = '".$locationId."'";

		$this->db->runQuery($sql);
	}

	public function saveIgWeeklyTopInfluencer( $influencer, $loc_id, $updatedAt, $prevRecord, $rank )
	{
		$prevRank = null;
		$prevLoggedAt = null;
		if(isset($prevRecord['rank'])){
			$prevRank = $prevRecord['rank'];
		}
		if(isset($prevRecord['updatedAt'])){
			$prevLoggedAt = $prevRecord['updatedAt'];
		}
		$sql = "INSERT INTO `weekly_top_influencer` (
							`socialPlatformId`,
							`locationId`,
							`influencerId`,
							`rating`,
							`followerCount`,
							`followerPercentage`,
							`interactionPercentage`,
							`weeklyFollowerCount`,
							`weeklyRank`,
							`rank`,
							`score`,
							`prevRank`,
							`prevLoggedAt`,
							`updatedAt`,
							`startAt`,
							`endAt`

						) VALUES('".$this->ig_id."',
							'".$loc_id."',
							'".$influencer['influencerId']."',
							'".$influencer['rating']."',
							'".$influencer['followerCount']."',
							'".$influencer['followerPercentage']."',
							'".$influencer['interactionPercentage']."',
							'".$influencer['weeklyFollowerCount']."',
							'".$influencer['weeklyRank']."',
							'".$rank."',
							'".$influencer['weeklyScore']."',
							'".$prevRank."',
							'".$prevLoggedAt."',
							'".$updatedAt."',
							'".$influencer['weeklyLoggedAt']."',
							'".$influencer['igUserUpdatedAt']."'
						)";
		//echo $sql."\n";
		$this->db->runQuery($sql);
		$this->saveIgWeeklyTopInfluencerLog($influencer, $loc_id, $updatedAt, $rank, $prevRank, $prevLoggedAt);
	}

	public function saveIgWeeklyTopInfluencerLog($influencer, $loc_id, $updatedAt, $rank, $prevRank, $prevLoggedAt)
	{
		$sql = "INSERT INTO `weekly_top_influencer_log` (
							`socialPlatformId`,
							`locationId`,
							`influencerId`,
							`rating`,
							`followerCount`,
							`rank`,
							`score`,
							`prevRank`,
							`prevLoggedAt`,
							`loggedAt`,
							`startAt`,
							`endAt`,
							`followerPercentage`,
							`interactionPercentage`,
							`weeklyFollowerCountRank`,
							`weeklyRank`

						) VALUES('".$this->ig_id."',
							'".$loc_id."',
							'".$influencer['influencerId']."',
							'".$influencer['rating']."',
							'".$influencer['followerCount']."',
							'".$rank."',
							'".$influencer['weeklyScore']."',
							'".$prevRank."',
							'".$prevLoggedAt."',
							'".$updatedAt."',
							'".$influencer['weeklyLoggedAt']."',
							'".$influencer['igUserUpdatedAt']."',
							'".$influencer['followerPercentage']."',
							'".$influencer['interactionPercentage']."',
							'".$influencer['weeklyFollowerCountRank']."',
							'".$influencer['weeklyRank']."'

						)";
	//echo $sql."  <<----\n";
		$this->db->runQuery($sql);
	}

	public function getIgWeeklyTopInfluencer($loc_id)
	{
		$sql = "SELECT igu.profilePic AS picture, igu.igId AS id, wti.rank, ide.name AS identity, wti.followerCount AS followers,
				igu.name AS username, wti.prevRank, wti.rating AS adtive , wti.startAt, wti.endAt
				FROM weekly_top_influencer AS wti
				INNER JOIN influencer AS inf ON inf.id = wti.influencerId
				INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
				INNER JOIN identity AS ide ON ide.id = inf.identityId
				WHERE wti.socialPlatformId = ".$this->ig_id." AND
	 					wti.locationId = ".$loc_id."
				ORDER BY wti.rank ASC";
		//echo $sql."\n\n";
		return $this->db->readQuery($sql);
	}

	public function getIgWeeklyTopInfluencerByLocation($loc_id)
	{
		$sql = "SELECT * FROM weekly_top_influencer AS wti
				WHERE wti.socialPlatformId = ".$this->ig_id." AND
	 					wti.locationId = ".$loc_id."
				ORDER BY wti.rank ASC";
		//echo $sql."\n\n";
		return $this->db->readQuery($sql);
	}

	public function getIgMonthlyTopInfluencer($loc_id)
	{
		$sql = "SELECT igu.profilePic AS picture, igu.igId AS id, wti.rank, ide.name AS identity, wti.followerCount AS followers, igu.name AS username, wti.prevRank, wti.rating AS adtive, wti.updatedAt, wti.prevLoggedAt
				FROM monthly_top_influencer AS wti
				INNER JOIN influencer AS inf ON inf.id = wti.influencerId
				INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
				INNER JOIN identity AS ide ON ide.id = inf.identityId
				WHERE wti.socialPlatformId = ".$this->ig_id." AND
	 					wti.locationId = ".$loc_id." AND
	 					wti.relatedId = 0 AND
	 					wti.type = ''
				ORDER BY wti.rank ASC";
			//echo $sql."\n";
		return $this->db->readQuery($sql);
	}

	public function getIgMonthlyTopInfluencerByLocation($loc_id, $isNew = false)
	{
		$tableName = "monthly_top_influencer";
		if($isNew){
			$tableName = "monthly_top_influencer_new";
		}
		$sql = "SELECT * FROM ".$tableName." AS mti
				WHERE mti.locationId = ".$loc_id."
				ORDER BY mti.rank ASC";

		return $this->db->readQuery($sql);
	}

	public function getIgMonthlyTopInfluencerByIdentity($loc_id, $identity_id = null)
	{
		$sql = "SELECT igu.profilePic AS picture, igu.igId AS id, wti.rank, ide.name AS identity, wti.followerCount AS followers, igu.name AS username, wti.prevRank, wti.rating AS adtive, wti.updatedAt, wti.prevLoggedAt
				FROM monthly_top_influencer AS wti
				INNER JOIN influencer AS inf ON inf.id = wti.influencerId
				INNER JOIN ig_user AS igu ON igu.id = inf.ig_user_id
				INNER JOIN identity AS ide ON ide.id = inf.identityId
				WHERE wti.socialPlatformId = ".$this->ig_id." AND
	 					wti.locationId = ".$loc_id." AND
	 					wti.relatedId = ".$identity_id." AND
	 					wti.type = 'IDENTITY'
				ORDER BY wti.rank ASC";

		return $this->db->readQuery($sql);
	}

	public function updateFbId($fbId, $fb_user_id, $infId)
	{
		$sql = "UPDATE `influencer` SET
					`fbId` = :fbId,
					`fb_user_id` = :fb_user_id
				WHERE `id`= :id";

			$args = [
				":id" 			=> $infId,
				":fb_user_id"	=> $fb_user_id,
				":fbId" 		=> $fbId
			];

		$this->db->runQuery($sql, $args);
	}

	public function updateYtId($ytId, $yt_user_id, $infId)
	{
		$sql = "UPDATE `influencer` SET
					`ytId` = :ytId,
					`yt_user_id` = :yt_user_id
				WHERE `id`= :id";

			$args = [
				":id" 			=> $infId,
				":fb_user_id"	=> $yt_user_id,
				":fbId" 		=> $ytId
			];

		$this->db->runQuery($sql, $args);
	}


	public function updateEmailFromAbout($igUserId, $email){
		$sql = "UPDATE `influencer` SET
					`emailFromAbout` = :email
				WHERE `ig_user_id`= :iguId";

			$args = [
				":email"  => $email,
				":iguId"  => $igUserId
			];
		$this->db->runQuery($sql, $args);
	}
	public function getFollowerPerformance($infId){
		$sql = "SELECT * FROM followers_performance 
				WHERE infId = :infId";
				
		return $this->db->readQuery($sql, [':infId'=>$infId]);
	}
	public function getInteractionPerformance($infId){
		$sql = "SELECT * FROM interactions_performance 
				WHERE infId = :infId";
				
		return $this->db->readQuery($sql, [':infId'=>$infId]);
	}

	public function updateInfByBio($user){
		$sql = "UPDATE `influencer` SET 
				`facebook` = :facebook,
				`youtube` = :youtube,
				`email` = :email,
				`locationId` = :locationId,
				`identityId` = :identityId
				WHERE `id` = :id";
		$this->db->runQuery($sql, [
				':id' => $user['inf_id'],
				':facebook' => $user['facebook'],
				':youtube'	=> $user['youtube'],
				':email'	=> $user['email'],
				':identityId' => $user['identityId'],
				':locationId' => $user['locationId']
 			]);
	}
	
	public function updateInfByUsername($infid, $username){
		$sql = "UPDATE `influencer` SET `username` = :username WHERE `id = :id";
		$this->db->runQuery($sql, [
				':id' => $infid,
				':username' => $username
 			]);
	}
	public function updatefix($user){
		// print_r($user['bio']);
		// exit();
		$sql = "UPDATE `influencer` SET 
				`name` = :name,
				`content` = :content,
				`contactPerson` = :contactPerson,
				`email` = :email
				WHERE `id` = :id";
		$this->db->runQuery($sql, [
				':id' => $user['inf_id'],
				':name' => $user['name'],
				':content'	=> $user['bio'],
				':contactPerson' => '',
				':email' => ''
 			]);
	}
	public function updateInfEdited($infId, $user){
		
		$sql = "SELECT * FROM `influencer` WHERE id = :infId";
		$result = $this->wdb->query($sql, [':infId' => $infId]);
		if($result){
			$webInf = $result[0];
			if($webInf['edited']){
				$sql = "UPDATE `influencer` SET 
							`name` = :name,
							`content` = :content,
							`gender` = :gender,
							`edited` = :edited
						WHERE `id = :id";
				$this->db->runQuery($sql, [
						':id' => $infId,
						':name' => $webInf['name'],
						':content' => $webInf['content'],
						':gender' => $webInf['gender'],
						':edited' => $webInf['edited']
		 			]);
			}else{
				$sql = "UPDATE `influencer` SET 
							`name` = :name,
							`content` = :content,
							`username` = :username
						WHERE `id = :id";
				$this->wdb->runQuery($sql, [
						':id' => $infId,
						':name' => $user['name'],
						':content'	=> $user['bio'],
						':username' => $user['username']
		 			]);
			}
		}
	}
}

?>
