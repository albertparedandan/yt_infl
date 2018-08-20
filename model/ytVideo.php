<?php
if(  !class_exists('DB') ) {
	require dirname(dirname(__FILE__)). '/utils/database.php';
}

if(  !class_exists('CoreModel') ) {
	require dirname(__FILE__). '/coreModel.php';
}

require dirname(dirname(__FILE__)). '/vendor/autoload.php';

class YtVideo extends CoreModel
{
    const GEN_POST_LIST = "LATEST_POPULAR";

    public function __construct() {
        parent::__construct(self::YT_POST_TABLE);
    }

    public function getPostByYtId($ytUserId, $date = null) {
        $sql = "SELECT * FROM yt_ video WHERE `yt_user_id` = '".$ytUserId."' ";
        /* if ($date) {
            $sql = $sql . " AND postDate >= '".$date."'";
        }
        $sql = $sql." ORDER BY postDate DESC"; */
        echo $sql."\n";
        $result = $this->db->readQuery($sql);

        return $result;
    }

    public function insert($yt_user_id, $media)
    {
        $sql = "INSERT INTO yt_video 
                            (
                                `ytId`,
                                `yt_videoId`,
                                `videoTitle`,
                                `content`,
                                `pictureUrl`,
                                `postDate`,
                                `viewCount`,
                                `likeCount`,
                                `dislikeCount`,
                                `commentCount`,
                                `videoDuration`,
                                `tags`,
                                `categId`,
                                `createdAt`,
                                `updatedAt`
                            ) VALUES 
                            (
                                :ytId,
                                :yt_videoId,
                                :videoTitle
                                :content,
                                :pictureUrl,
                                :postDate,
                                :viewCount,
                                :likeCount,
                                :dislikeCount,
                                :commentCount,
                                :videoDuration,
                                :tags,
                                :categId
                                :createdAt,
                                :updatedAt
                            )";
        
        $this->db->runQuery($sql, [
            ':ytId'                     => $yt_user_id,
            ':yt_videoId'               => $media['yt_videoId'],
            ':videoTitle'               => $media['videoTitle'],
            ':content'                  => $media['content'],
            ':pictureUrl'               => $media['pictureUrl'],
            ':postDate'                 => $media['postDate'],
            ':viewCount'                => $media['viewCount'],
            ':likeCount'                => $media['likeCount'],
            ':dislikeCount'             => $media['dislikeCount'],
            ':commentCount'             => $media['commentCount'],
            ':videoDuration'            => $media['videoDuration'],
            ':tags'                     => $media['tags'],
            ':categId'                  => $media['categId'],
            ':createdAt'                => time(),
            ':updatedAt'                => time()
        ]);
    }

    public function update($id, $media)
    {
        $sql = "UPDATE yt_video
                    SET `videoTitle`    = :videoTitle,
                        `content`       = :content,
                        `pictureUrl`    = :pictureUrl,
                        `postDate`      = :postDate,
                        `viewCount`     = :viewCount,
                        `likeCount`     = :likeCount,
                        `dislikeCount`  = :dislikeCount,
                        `commentCount`  = :commentCount,
                        `videoDuration` = :videoDuration,
                        `tags`          = :tags,
                        `categId`       = :categId,
                        `updatedAt`     = :updatedAt
                    WHERE `id` = :id";

        $this->db->runQuery($sql, [
            `:id`               => $id,
            `:videoTitle`       => $media['videoTitle'],
            `:content`          => $media['content'],
            `:pictureUrl`       => $media['pictureUrl'],
            `:postDate`         => $media['postDate'],
            `:viewCount`        => $media['viewCount'],
            `:likeCount`        => $media['likeCount'],
            `:dislikeCount`     => $media['dislikeCount'],
            `:commentCount`     => $media['commentCount'],
            `:videoDuration`    => $media['videoDuration'],
            `:tags`             => $media['tags'],
            `:categId`          => $media['categId'],
            `:updatedAt`        => time()
        ]);
    }

    public function clearUserPostList($yt_user_id)
    {
        $sql = "DELETE FROM `yt_pop_post` WHERE `yt_user_id` = '".$yt_user_id."'";
        $this->db->runQuery($sql);

        $sql = "DELETE FROM `yt_latest_post` WHERE `yt_user_id` = '".$yt_user_id."'";
        $this->db->runQuery($sql);
    }

    public function updatePopPostList($yt_user_id, $locationId, $post, $updatedAt)
    {
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
        $this->db->runQuery($sql, [
            ':yt_user_id'       => $yt_user_id,            
            ':yt_post_id'       => $post['yt_post_id'],
            ':locationId'       => $locationId,
            ':score'            => $post['score'],
            ':content'          => $post['content'],
            ':pictureUrl'       => $post['pictureUrl'],
            ':likeCount'        => $post['likeCount'],
            ':dislikeCount'     => $post['dislikeCount'],
            ':commentCount'     => $post['commentCount'],
            ':postDate'         => $post['postDate'],
            ':updatedAt'        => $updatedAt    
        ]);
    }

    public function updateLatestPostList($yt_user_id, $locationId, $post, $updatedAt) 
    {
        $sql = "INSERT INTO `yt_latest_post`
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
                                :likeCount
                                :dislikeCount,
                                :commentCount,
                                :postDate,
                                :updatedAt
                            )";
        $this->db->runQuery($sql, [
            ':yt_user_id'           => $yt_user_id,
            ':yt_post_id'           => $post['yt_post_id'],
            ':locationId'           => $locationId,
            ':score'                => $post['score'],
            ':content'              => $post['content'],
            ':pictureUrl'           => $post['pictureUrl'],
            ':likeCount'            => $post['likeCount'],
            ':dislikeCount'         => $post['dislikeCount'],
            ':commentCount'         => $post['commentCount'],
            ':postDate'             => $post['postDate'],
            ':updatedAt'            => $updatedAt
        ]);
    }

    public function getUserPopularPost($yt_user_id)
    {
        $sql = "SELECT * FROM `yt_pop_post` WHERE `yt_user_id` = :yt_user_id";
        return $this->db->readQuery($sql, [':yt_user_id' => $yt_user_id]);
    }

    public function getDuplicatedPosts() 
    {
        return $this->query('SELECT count(*) as count, yt_post_id FROM '.$this->table.' group by yt_post_id having count > 1');
    }    

    
}
?>