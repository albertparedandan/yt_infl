<?php

if(  !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}
if(  !class_exists('CoreModel') ) {
	require dirname(__FILE__). '/coreModel.php';
}
require dirname(dirname(__FILE__)). '/vendor/autoload.php';

class NormalModel extends CoreModel
{
	public function __construct()
	{
		parent::__construct();

	}

	public function getInterestList() {

		$sql = "SELECT * FROM `interest`";

		return $this->db->readQuery($sql);
	}

	public function getCategoryList() {

		$sql = "SELECT * FROM `category`";

		return $this->db->readQuery($sql);
	}

	public function getYtCategoryList() {
		$sql = "SELECT * FROM `yt_categ`";

		return $this->db->readQuery($sql);
	}

	public function getIdentityList() {

		$sql = "SELECT * FROM `identity`";
		return $this->db->readQuery($sql);
	}

	public function getLocationIdByKey($key) {
		
		$sql = "SELECT * FROM `location` WHERE `key` = '".$key."' Limit 1";
		
		$result = $this->db->readQuery($sql);
		if(count($result)>0){
			return $result[0]['id'];
		}
		return null;
	}

	public function getLocationList() {

		$sql = "SELECT * FROM `location` WHERE active =true";

		return $this->db->readQuery($sql);
	}

	public function getAllUser($type = false) {
		if($type){
			$sql = "SELECT influencer.id, influencer.ig_user_id, ig_user.igId, influencer.fb_user_id, fb_user.fbId, influencer.youtube, influencer.yt_user_id FROM influencer 
					left join ig_user on ig_user.id = influencer.ig_user_id 
					left join fb_user on fb_user.id = influencer.fb_user_id
					left join yt_user on yt_user.id = influencer.yt_user_id  
					order by influencer.id ";
		}else{
			$sql = "SELECT * FROM `ig_user`";
		}
		return $this->db->readQuery($sql);
	}

	public function getWebUser() {
		
			$sql = "SELECT * FROM `ig_user`";
		
		return $this->wdb->query($sql);
	}
	public function getTempUser($locationId = null, $startNum = null, $limitNum = null) {

		//$sql = "SELECT * FROM `ig_user_temp` ORDER BY `followers` DESC";

		$locationSql = '';
		if(!is_null($locationId)){
			$locationSql = " WHERE `locationId` = '".$locationId."' ";
		}

		$limitSql = '';
		if(!is_null($startNum) && !is_null($limitNum)){
		
			$limitSql = " LIMIT ".$limitNum." OFFSET ".$startNum;
		}

		$sql = "SELECT * FROM `ig_user_temp` ".$locationSql." ORDER BY `followers` DESC ".$limitSql;
		return $this->db->readQuery($sql);
	}

	public function insertErrorLog($igId, $code, $type, $message) 
	{
		$sql = "INSERT INTO `insert_error_log` (
							`igId`,
							`code`,
							`errorType`,
							`errorMessage`,
							`loggedAt`
						) VALUES(
							'".$igId."', 
							'".$code."', 
							'".$type."', 
							'".$message."', 
							'".time()."'
						)";
		$this->db->runQuery($sql);
	}

	public function updateErrorLog($igId, $code, $type, $message = null) 
	{
		$sql = "INSERT INTO `update_error_log` (
							`igId`,
							`code`,
							`errorType`,
							`errorMessage`,
							`loggedAt`
						) VALUES(
							'".$igId."', 
							'".$code."', 
							'".$type."', 
							'".$message."', 
							'".time()."'
						)";
		$this->db->runQuery($sql);
	}
	public function updateFbErrorLog($fbId, $code, $type, $message = null) 
	{
		$sql = "INSERT INTO `update_fb_error_log` (
							`fbId`,
							`code`,
							`errorType`,
							`errorMessage`,
							`loggedAt`
						) VALUES(
							'".$fbId."', 
							'".$code."', 
							'".$type."', 
							'".$message."', 
							'".time()."'
						)";
		$this->db->runQuery($sql);
	}

	public function updateYtErrorLog($ytId, $code, $type, $message = null) 
	{
		$sql = "INSERT INTO `update_yt_error_log` 
				(
					`ytId`,
					`code`,
					`errorType`,
					`errorMessage`,
					`loggedAt`
				) VALUES
				(
					'".$ytId."',
					'".$code."',
					'".$type."',
					'".$message."',
					'".time()."'
				)";
		$this->db->runQuery($sql);
	}
	
	public function clearInsertErrorLog()
	{
		$sql = "truncate `insert_error_log`";
		$this->db->runQuery($sql);
	}
	
	public function clearFbInsertErrorLog()
	{
		$sql = "truncate `insert_fb_error_log`";
		$this->db->runQuery($sql);
	}

	public function clearYtInsertErrorLog()
	{
		$sql = "truncate `insert_yt_error_log`";
		$this->db->runQuery($sql);
	}

	public function getLastUserLog($platform_user_id = null, $type = null)
	{
		if($type == "FB"){
			$sql = "SELECT * FROM `fb_update_user_log` WHERE fb_user_id = '".$platform_user_id."' ORDER BY loggedAt ASC Limit 1";
		} else if ($type == "YT") {
			$sql = "SELECT * FROM `yt_update_user_log` WHERE yt_user_id = '".$platform_user_id."' ORDER BY loggedAt ASC Limit 1";
		} else {
			$sql = "SELECT * FROM `ig_update_user_log` WHERE ig_user_id = '".$platform_user_id."' ORDER BY loggedAt ASC Limit 1";
		}

		$result = $this->db->readQuery($sql);
		if(count($result)>0){
			return $result[0]['loggedAt'];
		}
		return null;
	}

 	public function getDayBeforeUserLog($platform_user_id = null, $day, $type = null)
	{
		$halfDay = 12*60*60; //43200
		if($type == "FB") {
			$sql = "SELECT *, fanCount AS updatedFollowerCount FROM `fb_update_user_log` WHERE fb_user_id = ".$platform_user_id." AND loggedAt BETWEEN '".($day-$halfDay)."' AND '".($day+$halfDay)."' ORDER BY loggedAt DESC Limit 1";
		} else if ($type == "YT") {
			$sql = "SELECT * FROM `yt_update_user_log` WHERE yt_user_id = ".$platform_user_id." AND loggedAt BETWEEN '".($day-$halfDay)."' AND '".($day+$halfDay)."' ORDER BY loggedAt DESC Limit 1";
		} else {
			$sql = "SELECT * FROM `ig_update_user_log` WHERE ig_user_id = ".$platform_user_id." AND loggedAt BETWEEN '".($day-$halfDay)."' AND '".($day+$halfDay)."' ORDER BY loggedAt DESC Limit 1";
			echo $sql."\n";
		}
		$result = $this->db->readQuery($sql);
		//echo "\n".$sql."\n";
		if(count($result)>0){
			return $result[0];
		}
		return null;
	}

	public function insertApiLog($igId, $text)
	{
		$sql = "SELECT * FROM `insert_user_log` WHERE igId = '".$igId."' Limit 1";
		
		$result = $this->db->readQuery($sql);

		if(count($result)>0){
			$sql = "UPDATE insert_user_log
						SET `text` = '".addslashes($text)."',
						`loggedAt` = '".time()."'
						WHERE `igId`='".$igId."'";
			$this->db->runQuery($sql);
		}else{
			$sql = "INSERT INTO `insert_user_log` (
								`igId`,
								`text`,
								`loggedAt`

							) VALUES(
								'".$igId."', 
								'".addslashes($text)."', 
								'".time()."'
							)";
			$this->db->runQuery($sql);
		}
	}

	public function insertApiPostLog($igId, $text)
	{
		$json = json_decode($text, true);
		if($json['meta']['code'] != 200){
			
			$sql = "SELECT * FROM `insert_post_log` WHERE igId = '".$igId."' Limit 1";
			
			$result = $this->db->readQuery($sql);
		
			if(count($result)>0){
				$sql = "UPDATE insert_post_log
							SET `text` = '".addslashes($text)."',
							`loggedAt` = '".time()."'
							WHERE `igId`='".$igId."'";
				$this->db->runQuery($sql);
			}else{
				$sql = "INSERT INTO `insert_post_log` (
									`igId`,
									`text`,
									`loggedAt`

								) VALUES(
									'".$igId."', 
									'".addslashes($text)."', 
									'".time()."'
								)";
				$this->db->runQuery($sql);
			}
		}
	}
	public function getSocialPlatformList() {

		$sql = "SELECT * FROM `social_platform` WHERE `active` = true";

		return $this->db->readQuery($sql);
	}

	public function getAutomationKeyword($type){
		$sql = "SELECT * FROM keyword WHERE type = '".$type."'";
		return $this->dba->query($sql);
	}

}

?>