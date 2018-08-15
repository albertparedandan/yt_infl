<?php
if (!class_exists('DB')) {
    require dirname(dirname(__FILE__)) . '/utils/database.php';
}

if (!class_exists('CoreModel')) {
    require dirname(__FILE__) . '/coreModel.php';
}
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

class YtUser extends CoreModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkExistedYtUser($youtubeID)
    {
        $sql = "SELECT ytId FROM yt_user WHERE ytId = :id";
        $result = $this->db->readQuery($sql, array(':id' => $youtubeID));
        if ($result) {
            return $result[0];
        } else {
            return false;
        }
    }

    public function fetchAllYtUser($locationId = null, callable $callback = null, $startNum = null, $limitNum = null)
    {
        $locationSql = '';
        if (!is_null($locationId)) {
            $locationSql = " WHERE influencer.locationId = '" . $locationId . "' ";
        } else {
            $locationSql = " WHERE (influencer.locationId = '' OR location.active = false) ";
        }

        $limitSql = '';
        if (!is_null($limitNum)) {
            $limitSql = " LIMIT " . $limitNum;
        }

        $offsetSql = '';
        if (!is_null($startNum)) {
            $offsetSql = " OFFSET " . $startNum;
        }

        $sql = "SELECT *, influencer.id AS inf_id FROM `influencer`
        INNER JOIN `location` ON influencer.locationId = location.id
        INNER JOIN `yt_user` ON yt_user.id = influencer.yt_user_id
            " . $locationSql . " ORDER BY yt_user.videoCount ASC " . $limitSql . $offsetSql;

        return $this->db->fetchDb($sql, null, $callback);

    }

    public function getAllYtUser($locationId = null, $startNum = null, $limitNum = null)
    {
        $locationSql = '';
        if (!is_null($locationId)) {
            $locationSql = " WHERE influencer.locationId = '" . $locationId . "' ";
        }

        $limitSql = '';
        if (!is_null($startNum) && !is_null($limitNum)) {
            $limitSql = " LIMIT " . $limitNum . " OFFSET " . $startNum;
        }

        $sql = "SELECT *, influencer.id AS inf_id FROM `influencer`
        INNER JOIN `yt_user` AS ytu ON ytu.id = influencer.yt_user_id" . $locationId . " ORDER BY ytu.subscriberCount DESC " . $limitSql;

        return $this->db->readQuery($sql);
    }

    public function fetchYtUserByInfId($id, callable $callback = null)
    {
        $sql = "SELECT *, influencer.id AS inf_id FROM `influencer`
        INNER JOIN `location` ON influencer.locationId FROM `influencer`
        INNER JOIN `yt_user` ON yt_user.id = influencer.yt_user_id
            WHERE influencer.id = :id
        ORDER BY yt_user.subscriberCount DESC";

        return $this->db->fetchDb($sql, array(':id' => $id), $callback);
    }

    public function getYtUserByInfId($infId) 
    {
        $sql = "SELECT *, influencer.id AS inf_id FROM `influencer` left
        JOIN `yt_user` AS ytu ON ytu.id = influencer.yt_user_id
        WHERE influencer.id = " . $infId;

        return $this->db->readQuery($sql);
    }

    public function getYtUserByYtUserId($ytUserId)
    {
        $sql = "SELECT * FROM `yt_user` WHERE id = :ytUserId";
        $result = $this->db->readQuery($sql, array(':ytUserId' => $ytUserId));
        if (count($result)<1) {
            return;
        }
        return $result[0];
    }

    public function getAllInfWithYtId($locationId = null) 
    {
        $locationSql = '';
        if (!is_null($locationId)) {
            $locationSql = " WHERE influencer.locationId = '" .$locationId. "' ";
        }
        $sql = "SELECT * FROM `influencer` " .$locationSql. " AND youtubeId <> ''";
        return $this->db->readQuery($sql);
    }

    public function insert($youtubeId, $name) {
        $sql = "INSERT INTO yt_user 
                (
                    `ytId`,
                    `name`,
                    `updatedAt`,
                    `createdAt`
                )
                VALUES 
                (
                    :ytId,
                    :name,
                    :updatedAt,
                    :createdAt
                )";
        return $this->db->runQuery($sql, array(
            ':ytId' => $youtubeId,
            ':name' => $name,
            ':updatedAt' => time(),
            ':createdAt' => time()
        ));
    }

    public function update($user) 
    {
        $sql = "UPDATE `yt_user`
                    SET `desc`                          = :desc,
                        `profilePic`                    = :profilePic,
                        `email`                         = :email,
                        `subscriberCount`               = :subscriberCount,
                        `videoCount`                    = :videoCount,
                        `totalViewCount`                = :totalViewCount,
                        `country`                       = :country,
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
                        `reachScore`                    = :reachScore,
                        `explosivenessScore`            = :explosivenessScore,
                        `engagementScore`               = :engagementScore,
                        `appealScore`                   = :appealScore,
                        `interactionScore`              = :interactionScore,
                        `videoCount97`                  = :videoCount97,
                        `viewCount97`                   = :viewCount97,
                        `subscriberCount97`             = subscriberCount97,
                        `videoCount90`                  = :videoCount90,
                        `viewCount90`                   = :viewCount,
                        `subscriberCount90`             = :subscriberCount90,
                        `videoCount7`                   = :videoCount7,
                        `viewCount7`                    = :viewCount7,
                        `subscriberCount7`              = :subscriberCount7,
                        `videoCount30`                  = :videoCount30,
                        `viewCount30`                   = :viewCount30,
                        `subscriberCount30`             = :subscriberCount30,
                        `explosivenessVideoId`          = :explosivenessVideoId,
                        `explosivenessPostScore`        = :explosivenessPostScore,
                        `explosivenessPostLike`         = :explosivenessPostLike,
                        `explosivenessPostComment`      = :explosivenessPostComment,
                        `postListUpdatedAt`             = :postListUpdatedAt,
                        `infPower`                      = :infPower,
                        `oldInteraction`                = :oldInteraction,
                        `subscriberPercentage`          = :subscriberPercentage,
                        `interactionPercentage`         = :interactionPercentage,
                        `weeklyScore`                   = :weeklyScore,
                        `weeklyLoggedAt`                = :weeklyLoggedAt,
                        `weeklySubscriberCount`         = :weeklySubscriberCount,
                        `weeklyInteraction`             = :weeklyInteraction,
                        `engagementRate`                = :engagementRate,
                        `updatedAt`                     = :updatedAt
                    WHERE `id` = :id";
        $this->db->runQuery($sql, array
        (
            ':id'                           => $user['id'],
            ':desc'                         => $user['desc'],
            ':profilePic'                   => $user['profilePic'],
            ':email'                        => $user['email'],
            ':subscriberCount'              => $user['subscriberCount'],
            ':videoCount'                   => $user['videoCount'],
            ':totalViewCount'               => $user['totalViewCount'],
            ':country'                      => $user['country'],
            ':uploadPlaylistId'             => $user['uploadPlaylistId'],
            ':topicIds'                     => $user['topicIds'],
            ':topicCategories'              => $user['topicCategories'],
            ':activeness'                   => $user['activeness'],
            ':reach'                        => $user['reach'],
            ':explosiveness'                => $user['explosiveness'],
            ':engagement'                   => $user['engagement'],
            ':appeal'                       => $user['appeal'],
            ':interaction'                  => $user['interaction'],
            ':activenessScore'              => $user['activenessScore'],
            ':reachScore'                   => $user['reachScore'],
            ':explosivenessScore'           => $user['explosivenessScore'],
            ':engagementScore'              => $user['engagementScore'],
            ':appealScore'                  => $user['appealScore'],
            ':interactionScore'             => $user['interactionScore'],
            ':videoCount97'                 => $user['videoCount97'],
            ':viewCount97'                  => $user['viewCount97'],
            ':subscriberCount97'            => $user['ubscriberCount97'],
            ':videoCount90'                 => $user['videoCount90'],
            ':viewCount90'                  => $user['viewCount'],
            ':subscriberCount90'            => $user['subscriberCount90'],
            ':videoCount7'                  => $user['videoCount7'],
            ':viewCount7'                   => $user['viewCount7'],
            ':subscriberCount7'             => $user['subscriberCount7'],
            ':videoCount30'                 => $user['videoCount30'],
            ':viewCount30'                  => $user['viewCount30'],
            ':subscriberCount30'            => $user['subscriberCount30'],
            ':explosivenessVideoId'         => $user['explosivenessVideoId'],
            ':explosivenessPostScore'       => $user['explosivenessPostScore'],
            ':explosivenessPostLike'        => $user['explosivenessPostLike'],
            ':explosivenessPostComment'     => $user['explosivenessPostComment'],
            ':postListUpdatedAt'            => $user['postListUpdatedAt'],
            ':infPower'                     => $user['infPower'],
            ':oldInteraction'               => $user['oldInteraction'],
            ':subscriberPercentage'         => $user['subscriberPercentage'],
            ':interactionPercentage'        => $user['interactionPercentage'],
            ':weeklyScore'                  => $user['weeklyScore'],
            ':weeklyLoggedAt'               => $user['weeklyLoggedAt'],
            ':weeklySubscriberCount'        => $user['weeklySubscriberCount'],
            ':weeklyInteraction'            => $user['weeklyInteraction'],
            ':engagementRate'               => $user['engagementRate'],
            ':updatedAt'                    => time()
        ));
    }

    public function updateYtUserScore($user)
    {
        $sql = "UPDATE `yt_user`
                        SET
                        `videoCount97`                  = :videoCount97,
                        `viewCount97`                   = :viewCount97,
                        `subscriberCount97`             = subscriberCount97,
                        `videoCount90`                  = :videoCount90,
                        `viewCount90`                   = :viewCount,
                        `subscriberCount90`             = :subscriberCount90,
                        `videoCount7`                   = :videoCount7,
                        `viewCount7`                    = :viewCount7,
                        `subscriberCount7`              = :subscriberCount7,
                        `videoCount30`                  = :videoCount30,
                        `viewCount30`                   = :viewCount30,
                        `subscriberCount30`             = :subscriberCount30,
                        `explosivenessPostScore`        = :explosivenessPostScore,
                        `postListUpdatedAt`             = :postListUpdatedAt,
                        `updatedAt`                     = :updatedAt
                        WHERE `id` = :id";
        $this->db->runQuery($sql, array(
            ':id'                           => $user['id'],
            ':videoCount97'                 => $user['videoCount97'],
            ':viewCount97'                  => $user['viewCount97'],
            ':subscriberCount97'            => $user['ubscriberCount97'],
            ':videoCount90'                 => $user['videoCount90'],
            ':viewCount90'                  => $user['viewCount'],
            ':subscriberCount90'            => $user['subscriberCount90'],
            ':videoCount7'                  => $user['videoCount7'],
            ':viewCount7'                   => $user['viewCount7'],
            ':subscriberCount7'             => $user['subscriberCount7'],
            ':videoCount30'                 => $user['videoCount30'],
            ':viewCount30'                  => $user['viewCount30'],
            ':subscriberCount30'            => $user['subscriberCount30'],
            ':explosivenessPostScore'       => $user['explosivenessPostScore'],
            ':postListUpdatedAt'            => $user['postListUpdatedAt'],
            ':updatedAt'                    => time()
        ));
    }

    public function updateUserLog($user) 
    {
        $sql = "INSERT INTO yt_update_user_log (
            `yt_user_id`, 
            `oldSubscriberCount`, 
            `subscriberCount`, 
            `activeness`, 
            `reach`, 
            `explosiveness`, 
            `appeal`, 
            `interaction`,
            `videoCount97`, 
            `viewCount97`,
            `subscriberCount97`,
            `videoCount90`, 
            `viewCount90`,
            `subscriberCount90`,
            `videoCount7`, 
            `viewCount7`,
            `subscriberCount7`,
            `videoCount30`, 
            `viewCount30`,
            `subscriberCount30`,
            `subscriberStartedAt`,
            `postListUpdatedAt`,
            `explosivenessPostScore`,
            `loggedAt`
        ) VALUES(
            :yt_user_id,
            :oldSubscriberCount,
            :subscriberCount,
            :activeness,
            :reach,
            :explosiveness,
            :appeal,
            :interaction,
            :videoCount97,
            :viewCount97,
            :subscriberCount97,
            :videoCount90,
            :viewCount90,
            :subscriberCount90,
            :videoCount7,
            :viewCount7,
            :subscriberCount7,
            :videoCount30,
            :viewCount30,
            :subscriberCount30,
            :subscriberStartedAt,
            :postListUpdatedAt,
            :explosivenessPostScore,
            :loggedAt
        )";
        $this->db->runQuery($sql, array(
            ':yt_user_id'                      => $user['id'],
            ':oldSubscriberCount'              => $user['old']['subscriberCount'],
            ':subscriberCount'                 => $user['subscriberCount'],
            ':activeness'                      => $user['activeness'],
            ':reach'                           => $user['reach'],
            ':explosiveness'                   => $user['explosiveness'],
            ':appeal'                          => $user['appeal'],
            ':interaction'                     => $user['interaction'],
            ':videoCount97'                    => $user['videoCount97'],
            ':viewCount97'                     => $user['viewCount97'],
            ':subscriberCount97'               => $user['subscriberCount97'],
            ':videoCount90'                    => $user['videoCount90'],
            ':viewCount90'                     => $user['viewCount90'],
            ':subscriberCount90'               => $user['subscriberCount90'],
            ':videoCount7'                     => $user['videoCount7'],
            ':viewCount7'                      => $user['viewCount7'],
            ':subscriberCount7'                => $user['subscriberCount7'],
            ':videoCount30'                    => $user['videoCount30'],
            ':viewCount30'                     => $user['viewCount30'],
            ':subscriberCount30'               => $user['subscriberCount30'],
            ':subscriberStartedAt'             => $user['subscriberStartedAt'],
            ':postListUpdatedAt'               => $user['postListUpdatedAt'],
            ':explosivenessPostScore'          => $user['explosivenessPostScore'],
            ':loggedAt'                         => time()
        ));
    }

    public function insertUpdatePostLog($user) 
    {
        $sql = "INSERT INTO yt_update_post_log 
                        (
                            `yt_user_id`,
                            `videoCount7`,
                            `viewCount7`,
                            `subscriberCount7`,
                            `videoCount30`,
                            `viewCount30`,
                            `subscriberCount30`,
                            `loggedAt`
                        )
                        VALUES 
                        (
                            :yt_user_id,
                            :videoCount7,
                            :viewCount7,
                            :subscriberCount7,
                            :videoCount30,
                            :viewCount30,
                            :subscriberCount30,
                            :loggedAt
                        )";
        
        $this->db->runQuery($sql, array(
            `:yt_user_id`               => $user['yt_user_id'],
            `:videoCount7`              => $user['videoCount7'],
            `:viewCount7`               => $user['viewCount7'],
            `:subscriberCount7`         => $user['subscriberCount7'],
            `:videoCount30`             => $user['videoCount30'],
            `:viewCount30`              => $user['viewCount30'],
            `:subscriberCount30`        => $user['subscriberCount30'],
            `:loggedAt`                 => time()
        ));
    }
}
?>