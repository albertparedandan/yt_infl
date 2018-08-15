<?php
session_start();
// Call set_include_path() as needed to point to your client library.
if (!file_exists($file = __DIR__ . '/../vendor/autoload.php')) {
    throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ . '"');
}
require_once __DIR__ . '/../vendor/autoload.php';

/*
 * This variable specifies the location of a file where the access and
 * refresh tokens will be written after successful authorization.
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
define('CREDENTIALS_PATH', '~/php-yt-oauth2.json');

class YoutubeAPI
{
    protected $client;
    protected $service;
    protected $access_token;
    protected $refresh_token;

    public function __construct()
    {
        $client = new Google_Client();
        // Set to name/location of your client_secret.json file.
        $client->setAuthConfigFile('client_secret.json');
        // Set to valid redirect URI for your project.
        $client->setRedirectUri('http://localhost');

        $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        }
        $credentialsPath = str_replace('~', realpath($homeDirectory), CREDENTIALS_PATH);

        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);  // don't miss the second parameter true here!!!
        } else {
        // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
            $accessToken = $client->authenticate($authCode);

        // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }

        $this->service = new Google_Service_YouTube($client);
        if (isset($_GET['code'])) {
            if (strval($_SESSION['state']) !== strval($_GET['state'])) {
                die('The session state did not match.');
            }

            $client->authenticate($_GET['code']);
            $_SESSION['token'] = $client->getAccessToken();
            header('Location: ' . $redirect);
        }

        if (isset($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
        }

        if (!$client->getAccessToken()) {
            print("no access token, whaawhaaa");
            exit;
        }
        $this->ytClient = $client;

    }

    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    public function expandHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

    protected function conv($array)
    {
        $string = "";
        for ($i = 0; $i < count($array); ++$i) {
            $string .= $array[$i] . ", ";
        }
        return $string;
    }

    protected function yt_user($service, $part, $params)
    {
        $params = array_filter($params);
        $response = $service->channels->listChannels(
            $part,
            $params
        );
        $data = array(
            'ytId' => $response['items'][0]['id'],
            'name' => $response['items'][0]['snippet']['title'],
            'bio' => str_replace(array("\n", "\r"), '', $response['items'][0]['snippet']['description']),
            'profilePic' => $response['items'][0]['snippet']['thumbnails']['default']['url'],
            'videoCount' => $response['items'][0]['statistics']['videoCount'],
            'subscriberCount' => $response['items'][0]['statistics']['subscriberCount'],
            'viewCount' => $response['items'][0]['statistics']['viewCount'],
            'publishedAt' => $response['items'][0]['snippet']['publishedAt'],
            'country' => $response['items'][0]['snippet']['country'],
            'uploadId' => $response['items'][0]['contentDetails']['relatedPlaylists']['uploads']
        );
        $topicId = "";
        for ($i = 0; $i < count($response['items'][0]['topicDetails']['topicIds']); ++$i) {
            $topicId .= $response['items'][0]['topicDetails']['topicIds'][$i] . ", ";
        }

        $topicCateg = "";
        for ($i = 0; $i < count($response['items'][0]['topicDetails']['topicCategories']); ++$i) {
            $topicCateg .= $response['items'][0]['topicDetails']['topicCategories'][$i] . ", ";
        }
        $data['topicId'] = $topicId;
        $data['topicCateg'] = $topicCateg;
        return $data;
    }

    protected function yt_playlist($service, $part, $params, &$playlist)
    {
        $params = array_filter($params);
        $response = $service->playlistItems->listPlaylistItems(
            $part,
            $params
        );

        if ($response['prevPageToken'] == "") {
            $playlist[0] = ($response['pageInfo']['totalResults'] / $response['pageInfo']['resultsPerPage']);
        }

        $playlist[1] = $response['nextPageToken'];
        $string = "";
        for ($i = 0; @$response['items'][$i]['contentDetails']['videoId'] != null; ++$i) {
            $string .= $response['items'][$i]['contentDetails']['videoId'] . ",";
        }
        $string = substr($string, 0, -1);
        return $string;
    }

    // calls youtube API and gets data for multiple(50) videos 
    protected function videosListMultipleIds($service, $part, $params, &$return)
    {
        $params = array_filter($params);
        $response = $service->videos->listVideos(
            $part,
            $params
        );

        for ($i = 0; @$response['items'][$i]['statistics']['viewCount'] != null; ++$i) {
            array_push(
                $return,
                array(
                    'videoId' => $response['items'][$i]['id'],
                    'videoTitle' => $response['items'][$i]['snippet']['title'],
                    'description' => str_replace(array("\n", "\r"), '', $response['items'][$i]['snippet']['description']),
                    'pictureUrl' => $response['items'][$i]['snippet']['thumbnails']['default']['url'],
                    'postDate' => $response['items'][$i]['snippet']['publishedAt'],
                    'viewCount' => $response['items'][$i]['statistics']['viewCount'],
                    'likeCount' => $response['items'][$i]['statistics']['likeCount'],
                    'dislikeCount' => $response['items'][$i]['statistics']['dislikeCount'],
                    'commentCount' => $response['items'][$i]['statistics']['commentCount'],
                    'videoDuration' => $response['items'][$i]['contentDetails']['duration'],
                    'tags' => $this->conv($response['items'][$i]['snippet']['tags']),
                    'categId' => $response['items'][$i]['snippet']['categoryId']
                )
            );
        }
    }

    // call youtube API and gets data for a single video
    protected function videoList($service, $part, $params)
    {
        $params = array_filter($params);
        $response = $service->videos->listVideos(
            $part,
            $params
        );
        $result = array(
            'videoId' => $response['items'][$i]['id'],
            'videoTitle' => $response['items'][$i]['snippet']['title'],
            'description' => str_replace(array("\n", "\r"), '', $response['items'][$i]['snippet']['description']),
            'pictureUrl' => $response['items'][$i]['snippet']['thumbnails']['default']['url'],
            'postDate' => $response['items'][$i]['snippet']['publishedAt'],
            'viewCount' => $response['items'][$i]['statistics']['viewCount'],
            'likeCount' => $response['items'][$i]['statistics']['likeCount'],
            'dislikeCount' => $response['items'][$i]['statistics']['dislikeCount'],
            'commentCount' => $response['items'][$i]['statistics']['commentCount'],
            'videoDuration' => $response['items'][$i]['contentDetails']['duration'],
            'tags' => $this->conv($response['items'][$i]['snippet']['tags']),
            'categId' => $response['items'][$i]['snippet']['categoryId']
        );

        return $result;
    }

    public function getUserDatabyId($id)
    {
        $data = $this->yt_user($this->service, 'snippet,contentDetails,statistics,topicDetails', array('id' => $id));
        return $data;
    }

    public function getUserDatabyUsername($name)
    {
        $data = $this->yt_user($this->service, 'snippet,contentDetails,statistics,topicDetails', array('forUsername' => $name));
        return $data;
    }

    public function getAllVideoId($playlistId)
    {
        $track = [];
        $result = [];
        // track[0] is totalVideo/videoPerCall to keep track of how many times to call yt_playlist function
        // track[1] is nextpagetoken
        $result[0] = $this->yt_playlist($this->service, 'snippet,contentDetails', array('maxResults' => 50, 'playlistId' => $playlistId), $track);
        while ($track[0] > 1) {
            array_push($result, $this->yt_playlist($this->service, 'snippet,contentDetails', array('maxResults' => 50, 'pageToken' => $track[1], 'playlistId' => $playlistId), $track));
            --$track[0];
        }
        // result is an array of videoIds with 50ids per element
        return $result;
    }

    public function getAllVideoData($array)
    {
        // receives an array of strings of video ids separated by commas
        // each element has 50 videoIDs
        if (empty($array[0])) {
            return;
        }
        $videoList = [];
        for ($i = 0; $i < count($array); ++$i) {
            $this->videosListMultipleIds($this->service, 'snippet, contentDetails, statistics', array('maxResults' => 50, 'id' => $array[$i]), $videoList);
        }
        foreach ($videoList as $user) {
            print $user['videoId'] . "\n";
        }
        return $videoList;
    }

    public function getVideoData($videoId)
    {
        if ($videoId == null) {
            return;
        }
        $data = [];
        $data = $this->videoList($this->service, 'snippet, contentDetails, statistics', array('id' => $videoId));
        return $data;
    }

    public function getYtUserMediaData($youtubeID) 
    {
        $data = getUserDatabyId($youtubeID);
        $data = getAllVideoId($data['uploadId']);
        $data = getAllVideoData($data);
        return $data;
    }
}

?>