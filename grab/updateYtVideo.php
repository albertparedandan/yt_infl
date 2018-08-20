<?php
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
if (!class_exists('Core')) {
    require dirname(__FILE__) . '/core.php';
}

if (!class_exists('PrintLog')) {
    require dirname(dirname(__FILE__)) . '/utils/printLog.php';
}

if (!class_exists('YtUser')) {
    require dirname(dirname(__FILE__)). '/model/ytUser.php';
}

if (!class_exists('YtVideo')) {
    require dirname(dirname(__FILE__)). '/model/ytVideo.php';
}

if( !class_exists('Influencer') ) {
	require dirname(dirname(__FILE__)). '/model/influencer.php';
}

if( !class_exists('NormalModel') ) {
	require dirname(dirname(__FILE__)). '/model/normal.php';
}

if (!class_exists('YoutubeApi')) {
    require dirname(dirname(__FILE__)). '/utils/youtubeAPI.php';
}

/* if (!class_exists('GenTopPost')) {
    require dirname(dirname(__FILE__)). '/getTopPost.php';
} */

class UpdateYtPost extends Core
{
    const RECENT_DAY = 97;
    private $postModel = null;
    private $userModel = null;
    private $influencerModel = null;
    private $normalModel = null;

    private $print = null;
    private $youtubeApi = null;

    const INSERT_TYPE = "NEW_YT_POST";

    public function __construct($location = null)
    {
        parent::__construct($location);

        $this->normalModel = new NormalModel;
        $this->userModel = new YtUser;
        $this->postModel = new YtVideo;
        $this->influencerModel = new Influencer;
        
        $this->location = $location;
        $this->printLog = new PrintLog("updateYtPost", $location);
        $this->youtubeApi = new YoutubeApi();
    }

    public function run()
    {
        $this->normalModel->clearYtInsertErrorLog();

        $this->failCount = 0;
        $this->successCount = 0;
        $this->updateUserStartTime = time();

        //$infId = 13542;
        if (!$infId) {
            $this->userModel->fetchAllYtUser($this->locationId, array($this, 'updatePost'));
        }
        else {
            $this->userModel->fetchYtUserByInfId($infId, array($this, 'updatePost'));
        }

        //gen top post
        if($this->location) {
            $genTopPost = new GenTopPost($this->location, "YT");
            $genTopPost->run($infId);
        }

        return;
    }

    public function updatePost($key = 0, $user = null)
    {
        $updateStartTime = time();
        $userDetail = parent::getYtUserMediaData($user, $this->updateUserStartTime);

        if (!$userDetail || isset($userDetail->error)) {
            $this->normalModel->updateYtErrorLog($user['ytId'], 400, 'GET_API_FAILURE', 'Get api failure');
            $this->failCount++;
            return;
        }
        $this->successCount++;
        $this->updateYtUser($userDetail, $this->updateUserStartTime, $updateStartTime);
    }

    public function updateYtUser($user, $startTime, $updateStartTime) {
        if (!isset($user['media'])) {
            $this->printLog->log("INFO", "No recent post '".$user['ytId']."'\n===============\n");
            return;
        }
        $userOldPost = $this->postModel->getPostByYtId($user['id']);
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
        $this->userModel->insertUpdatePostLog($user);
        $this->updatePostList($user);
    }

    public function updatePostList($user)
    {
        $this->postModel->clearUserPostList($user['id']);
        if (isset($user['popularPosts'])) {
            foreach ($user['popularPosts'] as $key => $post) {
                $this->postModel->updatePopPostList($user['id'], $user['locationId'], $post, $user['popListUpdatedAt']);
            }
        }
        if (isset($user['latestPost'])) {
            foreach ($user['latestPosts'] as $post) {
                $this->postModel->updateLatestPostList($user['id'], $user['locationId'], $post, $user['postListUpdatedAt']);
            }
        }
    }
}

$location = null;
if (isset($argv[1])) {
    $location = $argv[1];
}
?>