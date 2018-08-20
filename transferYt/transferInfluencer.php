<?php
if (!class_exists('PrintLog')) {
    require dirname(dirname(__FILE__)) . '/utils/printLog.php';
}
if (!class_exists('YtUser')) {
    require dirname(dirname(__FILE__)) . '/model/ytUser.php';
}
if (!class_exists('YtPost')) {
    require dirname(dirname(__FILE__)) . '/model/ytVideo.php';
}
if (!class_exists('Influencer')) {
    require dirname(dirname(__FILE__)) . '/model/influencer.php';
}
if (!class_exists('DB')) {
    require dirname(dirname(__FILE__)) . '/utils/database.php';
}
if (!class_exists('Core')) {
    require dirname(dirname(__FILE__)) . '/grab/core.php';
}
if (!class_exists('Emoji')) {
    require dirname(dirname(__FILE__)) . '/library/emoji.php';
}

class TransferInfluencer extends Core
{
    private $userModel = null;
    private $influencerModel = null;
    private $wdb = null;
    private $printLog = null;

    public function __construct($location = null, $type = null)
    {
        parent::__construct($location);
        $this->userModel = new YtUser;
        $this->postModel = new YtVideo;
        $this->influencerModel = new Influencer;
        $this->db = new DB();
        $this->wdb = new DB("WEB");
        $this->type = $type;

        if (!$type) {
            $this->printLog = new PrintLog("transferYtInfluencer", $location);
        } elseif ($type == "new") {
            $this->printLog = new PrintLog("transferYtNewInfluencer", $location);
        }
    }

    public function run($infId = null)
    {
        $transferStartTime = time();
        $this->printLog->log("INFO", "Start transfer Influencer");

        if (!$infId) {
            $allUsers = $this->userModel->getAllYtUser($this->locationId);
        } else {
            $allUsers = $this->userModel->getYtUserByInfId($inf_id);
        }

        foreach ($allUsers as $key => $user) {
            $this->updateUser($user);
            $this->updateUser($user);
            if ($this->type = "new") {
                $this->updateUserPost($user);
            }
        }
    }

    public function transferTopPost($inf_id = null)
    {
        $transferStartTime = time();
        if (!$inf_id) {
            $allUsers = $this->userModel->getAllYtUser($this->locationId);
        } else {
            $allUsers = $this->userModel->getYtUserByInfId($inf_id);
        }

        foreach ($allUsers as $key => $user) {
            $this->updateUserPost($user);
        }

        if (!$inf_id) {
            $this->updateTopPost($this->locationId);
        }
    }

    public function transferTopInfluencer()
    {
        $transferStartTime = time();
        $this->updateTopInfluencer($this->locationId, 2);
        $this->updateTopInfluencer($this->locationId, 2, true);
    }

    public function updateUser($user)
    {
        $sql = "SELECT * FROM yt_user
            INNER JOIN influencer AS inf on inf.yt_user_id = yt_user.id
            WHERE yt_user.id = " . $user['yt_user_id'];
        $result = $this->wdb->query($sql);

        if (empty($result)) {
            $sql = "INSERT INTO yt_user
                            (
                                `id`,
                                `ytId`,
                                `name`,
                                `desc`,
                                `profilePic`,
                                `email`,
                                `subscriberCount`,
                                `videoCount`,
                                `totalViewCount`,
                                `country`,
                                `uploadPlaylistId`,
                                `topicIds`,
                                `topicCategories`,
                                `activeness`,
                                `reach`,
                                `explosiveness`,
                                `engagement`,
                                `appeal`,
                                `interaction`,
                                `activenessScore`,
                                `engagementScore`,
                                `appealScore`,
                                `interactionScore`,
                                `infPower`,
                                `postListUpdatedAt`,
                                `oldInteraction`,
                                `subscriberPercentage`,
                                `interactionPercentage`,
                                `explosivenessPostScore`,
                                `explosivenessVideoId`,
                                `explosivenessPostLike`,
                                `explosivenessPostComment`,
                                `weeklyScore`,
                                `weekyLoggedAt`,
                                `weeklySubscriberCount`,
                                `weeklyInteraction`,
                                `engagementRate`,
                            ) VALUES
                            (
                                :id,
                                :ytId,
                                :name,
                                :desc,
                                :profilePic,
                                :email,
                                :subscriberCount,
                                :videoCount,
                                :totalViewCount,
                                :country,
                                :uploadPlaylistId,
                                :topicIds,
                                :topicCategories,
                                :activeness,
                                :reach,
                                :explosiveness,
                                :engagement,
                                :appeal,
                                :interaction,
                                :activenessScore,
                                :engagementScore,
                                :appealScore,
                                :interactionScore,
                                :infPower,
                                :postListUpdatedAt,
                                :oldInteraction,
                                :subscriberPercentage,
                                :interactionPercentage,
                                :explosivenessPostScore,
                                :explosivenessVideoId,
                                :explosivenessPostLike,
                                :explosivenessPostComment,
                                :weeklyScore,
                                :weekyLoggedAt,
                                :weeklySubscriberCount,
                                :weeklyInteraction,
                                :engagementRate
                            )";
            $args = [
                ':id' => $user['yt_user_id'],
                ':ytId' => $user['ytId'],
                ':name' => $user['name'],
                ':desc' => $user['desc'],
                ':profilePic' => $user['profilePic'],
                ':email' => $user['email'],
                ':subscriberCount' => $user['subscriberCount'],
                ':videoCount' => $user['videoCount'],
                ':totalViewCount' => $user['totalViewCount'],
                ':country' => $user['country'],
                ':uploadPlaylistId' => $user['uploadPlaylistId'],
                ':topicIds' => $user['topicIds'],
                ':topicCategories' => $user['topicCategories'],
                ':activeness' => $user['activeness'],
                ':reach' => $user['reach'],
                ':explosiveness' => $user['explosiveness'],
                ':engagement' => $user['engagement'],
                ':appeal' => $user['appeal'],
                ':interaction' => $user['interaction'],
                ':activenessScore' => $user['activenessScore'],
                ':engagementScore' => $user['engagementScore'],
                ':appealScore' => $user['appealScore'],
                ':interactionScore' => $user['interactionScore'],
                ':infPower' => $user['infPower'],
                ':postListUpdatedAt' => $user['postListUpdatedAt'],
                ':oldInteraction' => $user['oldInteraction'],
                ':subscriberPercentage' => $user['subscriberPercentage'],
                ':interactionPercentage' => $user['interactionPercentage'],
                ':explosivenessPostScore' => $user['explosivenessPostScore'],
                ':explosivenessVideoId' => $user['explosivenessVideoId'],
                ':explosivenessPostLike' => $user['explosivenessPostLike'],
                ':explosivenessPostComment' => $user['explosivenessPostComment'],
                ':weeklyScore' => $user['weeklyScore'],
                ':weekyLoggedAt' => $user['weekyLoggedAt'],
                ':weeklySubscriberCount' => $user['weeklySubscriberCount'],
                ':weeklyInteraction' => $user['weeklyInteraction'],
                ':engagementRate' => $user['engagementRate']
            ];

            $this->wdb->query($sql, $args);

            $sql = "SELECT * FROM influencer WHERE `id` = " . $user['inf_id'];
            $influencer = $this->wdb->query($sql);
            if (count($influencer) > 0) {
                $influencer = $influencer[0];
            }
            if (empty($influencer)) {
                $influencer = $this->influencerModel->getInfluencerById($user['inf_id']);
                $influencer_interests = $this->influencerModel->getInflencerInterestByIgUserId($influencer['id']);

                $sql = "INSERT INTO influencer (
                                        `id`,
                                        `profilePic`,
                                        `name`,
                                        `content`,
                                        `gender`,
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
                                        `createdAt`,
                                        `updatedAt`
                                    ) VALUES(
                                        :id,
                                        '" . $influencer['profilePic'] . "',
                                        :name,
                                        :content,
                                        '" . $influencer['gender'] . "',
                                        '" . $influencer['facebook'] . "',
                                        '" . $influencer['instagram'] . "',
                                        '" . $influencer['youtube'] . "',
                                        '" . $influencer['twitter'] . "',
                                        '" . $influencer['website'] . "',
                                        '" . $influencer['snapchat'] . "',
                                        '" . $influencer['weibo'] . "',
                                        '" . $influencer['linkedin'] . "',
                                        '" . $influencer['phone'] . "',
                                        '" . $influencer['email'] . "',
                                        :contact_person,
                                        '" . $influencer['isGroup'] . "',
                                        '" . $influencer['identityId'] . "',
                                        '" . $influencer['locationId'] . "',
                                        '" . $influencer['ig_user_id'] . "',
                                        '" . $influencer['fb_user_id'] . "',
                                        '" . $influencer['yt_user_id'] . "',
                                        '" . $newInfluencerId . "',
                                        '" . $verified . "',
                                        :createdAt,
                                        :updatedAt
                                    )";
                $influencer_id = $this->wdb->runQuery($sql, [
                    ':id' => $influencer['id'],
                    ':name' => Emoji::Encode($influencer['name']),
                    ':content' => Emoji::Encode($influencer['content']),
                    ':contact_person' => Emoji::Encode($influencer['contactPerson']),
                    ':createdAt' => time(),
                    ':updatedAt' => time()
                ]);

                $sql = "SELECT * FROM influencer_interest WHERE `influencerId` = " . $influencer['id'];
                $result = $this->wdb->getQuery($sql);
                if (empty($result)) {
                    foreach ($influencer_interests as $key => $influencer_interest) {
                        $sql = "INSERT INTO influencer_interest (`influencerId`,
											`interestId`
										) VALUES('" . $influencer['id'] . "',
											'" . $influencer_interest['interestId'] . "'
										)";
                        $this->wdb->insertQuery($sql);
                    }
                }
            } else {
                $sql = "UPDATE `influencer` 
                                SET 
                                `fb_user_id` = :fb_user_id,
                                `updatedAt` = :updatedAt
                                WHERE `id` = :id";

                $influencer_id = $this->wdb->updateQuery($sql, [
                    ':id' => $user['inf_id'],
                    ':fb_user_id' => $user['fb_user_id'],
                    ':updatedAt' => $user['updatedAt']
                ]);
            }
        } else {
            $sql = "UPDATE `yt_user` SET
                        `ytId`                          = :ytId,
                        `name`                          = :name,
                        `desc`                          = :desc,
                        `profilePic`                    = :profilePic,
                        `email`                         = :email,
                        `subscriberCount`               = :subscriberCount,
                        `videoCount`                    = :videoCount,
                        `totalViewCount`                = :totalViewCount,
                        `country                        = :country,
                        `uploadPlaylistId`              = :uploadPlaylistId,
                        `topicIds`                      = :topicIds,
                        `topicCategories`               = :topicCategories,
                        `activeness`                    = :activeness,
                        `reach`                         = :reach,
                        `explosiveness`                 = :explosiveness,
                        `engagement`                    = :engagement,
                        `appeal`                        = :appeal,
                        `interaction`                   = :interaction,
                        `activenessScore`               = :activenessScore,
                        `engagementScore`               = :engagementScore,
                        `appealScore`                   = :appealScore,
                        `interactionScore`              = :interactionScore,
                        `infPower`                      = :infPower,
                        `postListUpdatedAt`             = :postListUpdatedAt,
                        `oldInteraction`                = :oldInteraction,
                        `subscriberPercentage`          = :subscriberPercentage,
                        `interactionPercentage`         = :interactionPercentage,
                        `explosivenessPostScore`        = :explosivenessPostScore,
                        `explosivenessVideoId`          = :explosivenessVideoId,
                        `explosivenessPostLike`         = :explosivenessPostLike,
                        `explosivenessPostComment`      = :explosivenessPostComment,
                        `weeklyScore`                   = :weeklyScore,
                        `weekyLoggedAt`                 = :weeklyLoggedAt,
                        `weeklySubscriberCount`         = :weeklySubscriberCount,
                        `weeklyInteraction`             = :weeklyInteraction,
                        `engagementRate'                = :engagementRate
                        
                    WHERE `id` = :id";
                    $args = [
                        ':id'                       => $user['yt_user_id'],
                        ':ytId'                     => $user['ytId'],
                        ':name'                     => $user['name'],
                        ':desc'                     => $user['desc'],
                        ':profilePic'               => $user['profilePic'],
                        ':email'                    => $user['email'],
                        ':subscriberCount'          => $user['subscriberCount'],
                        ':videoCount'               => $user['videoCount'],
                        ':totalViewCount'           => $user['totalViewCount'],
                        ':country'                  => $user['country'],
                        ':uploadPlaylistId'         => $user['uploadPlaylistId'],
                        ':topicIds'                 => $user['topicIds'],
                        ':topicCategories'          => $user['topicCategories'],
                        ':activeness'               => $user['activeness'],
                        ':reach'                    => $user['reach'],
                        ':explosiveness'            => $user['explosiveness'],
                        ':engagement'               => $user['engagement'],
                        ':appeal'                   => $user['appeal'],
                        ':interaction'              => $user['interaction'],
                        ':activenessScore'          => $user['activenessScore'],
                        ':engagementScore'          => $user['engagementScore'],
                        ':appealScore'              => $user['appealScore'],
                        ':interactionScore'         => $user['interactionScore'],
                        ':infPower'                 => $user['infPower'],
                        ':postListUpdatedAt'        => $user['postListUpdatedAt'],
                        ':oldInteraction'           => $user['oldInteraction'],
                        ':subscriberPercentage'     => $user['subscriberPercentage'],
                        ':interactionPercentage'    => $user['interactionPercentage'],
                        ':explosivenessPostScore'   => $user['explosivenessPostScore'],
                        ':explosivenessVideoId'     => $user['explosivenessVideoId'],
                        ':explosivenessPostLike'    => $user['explosivenessPostLike'],
                        ':explosivenessPostComment' => $user['explosivenessPostComment'],
                        ':weeklyScore'              => $user['weeklyScore'],
                        ':weeklyLoggedAt'           => $user['weeklyLoggedAt'],
                        ':weeklySubscriberCount'    => $user['weeklySubscriberCount'],
                        ':weeklyInteraction'        => $user['weeklyInteraction'],
                        ':engagementRate'           => $user['engagementRate']
                    ];
                $this->wdb->query($sql, $args);

                $sql = "UPDATE `influencer`
                            SET 
                                `yt_use r_id` = :yt_user_id,
                                `updatedAt` = :updatedAt
                            WHERE `id` = :id";
                $influencer_id = $this->wdb->updateQuery($sql, [    ':id'            => $user['inf_id'],
                                                                    ':yt_user_id'    => $user['yt_user_id'],
                                                                    ':updatedAt'     => $user['updatedAt']]);
        }
    }

    public function updateUserPost($user)
    {
        $sql = "DELETE FROM `yt_pop_post` WHERE `yt_user_id` = :yt_user_id";

        $this->wdb->query($sql, [
            ":yt_user_id" => $user['id']
        ]);

        $popularPosts = $this->postModel->getUserPopularPost($user['id']);

        foreach ($popularPosts as $key => $post) {
            $sql = "INSERT INTO `yt_pop_post` 
                                (
                                    `yt_user_id`,
                                    `yt_post_id`,
                                    `locationId`,
                                    `score`,
                                    `content`,
                                    `pictureUrl`,
                                    `likeCount`,
                                    `dislikeCount`,
                                    `commentCount`,
                                    `postDate`,
                                    `updatedAt`
                                ) VALUES 
                                (
                                    :yt_user_id,
                                    :yt_post_id,
                                    :locationId,
                                    :score,
                                    :content,
                                    :pictureUrl,
                                    :likeCount,
                                    :dislikeCount,
                                    :commentCount,
                                    :postDate,
                                    :updatedAt
                                )";
            $this->wdb->Query($sql, [
            ':yt_user_id'       => $$post['yt_user_id'],            
            ':yt_post_id'       => $post['yt_post_id'],
            ':locationId'       => $post['locationId'],
            ':score'            => $post['score'],
            ':content'          => $post['content'],
            ':pictureUrl'       => $post['pictureUrl'],
            ':likeCount'        => $post['likeCount'],
            ':dislikeCount'     => $post['dislikeCount'],
            ':commentCount'     => $post['commentCount'],
            ':postDate'         => $post['postDate'],
            ':updatedAt'        => $post['updatedAt']    
            ]);
        }
    }

	public function updateTopPost($locationId)
	{
		echo "updateTopPost\n";
		$topPost = $this->influencerModel->getTopPostByLocation($locationId, 3);
		echo "a\n";
		$sql = "DELETE FROM `top_post` WHERE `locationId` = :locationId AND `socialPlatformId` = 3";
		echo $sql."\n";
		$this->wdb->query($sql, [
			":locationId" => $this->locationId
		]);
		echo "c\n";

		foreach ($topPost as $key => $post) {
			$sql = "INSERT INTO top_post (
								`socialPlatformId`,
								`locationId`,
								`influencerId`,
								`relatedId`,
								`type`,
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
								`prevRank`,
								`prevLoggedAt`,
								`updatedAt`
							) VALUES(
								:socialPlatformId,
								:locationId,
								:influencerId,
								:relatedId,
								:type,
								:score,
								:ig_post_id,
								:content,
								:likeCount,
								:commentCount,
								:pictureUrl,
								:videoUrl,
								:link,
								:postDate,
								:rank,
								:prevRank,
								:prevLoggedAt,
								:updatedAt
							)";
			$args = [
				":socialPlatformId" => $post['socialPlatformId'],
				":locationId" => $post['locationId'],
				":influencerId" => $post['influencerId'],
				":relatedId" => $post['relatedId'],
				":type" => $post['type'],
				":score" => $post['score'],
				":ig_post_id" => $post['ig_post_id'],
				":content" => Emoji::Encode($post['content']),
				":likeCount" => $post['likeCount'],
				":commentCount" => $post['commentCount'],
				":pictureUrl" => $post['pictureUrl'],
				":videoUrl" => $post['videoUrl'],
				":link" => $post['link'],
				":postDate" => $post['postDate'],
				":rank" => $post['rank'],
				":prevRank" 	=> $post['prevRank'],
				":prevLoggedAt" => $post['prevLoggedAt'],
				":updatedAt" => $post['updatedAt']
			];
			$this->wdb->query($sql, $args);
		}
	}

	public function updateTopInfluencer($locationId, $socialPlatformId = 2, $isNew = false)
	{
		$tableName = 'top_influencer';
		if($isNew){
			$tableName = 'top_influencer_new';
		}
		$topInfluencer = $this->influencerModel->getTopInfluencerByLocation($locationId, $socialPlatformId, $isNew);
		$sql = "DELETE FROM `".$tableName."` WHERE `locationId` = :locationId  AND `socialPlatformId` = ".$socialPlatformId;

		$this->wdb->query($sql, [
			":locationId" => $this->locationId
		]);

		foreach ($topInfluencer as $key => $influencer) {
			$sql = "INSERT INTO `".$tableName."` (
								`socialPlatformId`,
								`locationId`,
								`influencerId`,
								`relatedId`,
								`type`,
								`rating`,
								`followerCount`,
								`rank`,
								`prevRank`,
								`prevLoggedAt`,
								`updatedAt`
							) VALUES(
								:socialPlatformId,
								:locationId,
								:influencerId,
								:relatedId,
								:type,
								:rating,
								:followerCount,
								:rank,
								:prevRank,
								:prevLoggedAt,
								:updatedAt
							)";
			$args = [
				":socialPlatformId" => $influencer['socialPlatformId'],
				":locationId"	 	=> $influencer['locationId'],
				":influencerId" 	=> $influencer['influencerId'],
				":relatedId" 		=> $influencer['relatedId'],
				":type" 			=> $influencer['type'],
				":rating" 			=> $influencer['rating'],
				":followerCount" 	=> $influencer['followerCount'],
				":rank" 			=> $influencer['rank'],
				":prevRank" 		=> $influencer['prevRank'],
				":prevLoggedAt" 	=> $influencer['prevLoggedAt'],
				":updatedAt" 		=> $influencer['updatedAt']
			];
			$this->wdb->query($sql, $args);
		}
	}

	public function updateYtFollowerChart($user)
	{
		$result = $this->influencerModel->getFollowerPerformance($user['inf_id'], 3);
		//print_r($result);
		$sql = "DELETE FROM followers_performance
				WHERE `platform` = '3' AND
				`infId` = '".$user['inf_id']."'";

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

			$this->wdb->query($sql, [
					':infId' 			=> $user['inf_id'],
					':platform' 		=> 3,
					':performanceTime' 	=> $value['performanceTime'],
					':value' 			=> $value['value']
				]);
		}
		
	}

	public function updateYtInteractionChart($user)
	{
		$result = $this->influencerModel->getInteractionPerformance($user['inf_id'], 3);
		//print_r($result);
		$sql = "DELETE FROM interactions_performance
				WHERE `platform` = '3' AND
				`infId` = '".$user['inf_id']."'";

		$this->wdb->runQuery($sql);
		foreach ($result as $key => $value) {
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

			$this->wdb->query($sql, [
					':infId' 			=> $user['inf_id'],
					':platform' 		=> 3,
					':performanceTime' 	=> $value['performanceTime'],
					':value' 			=> $value['value']
				]);
		}
		
	}
}
?>