<?php
include __DIR__ . '/API/yt_clientAPI.php';
$servername = "192.168.64.2";
$username = "cloudbreakr";
$password = "cloudbreakr";
$myDB = "youtubeDB";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$myDB", $username, $password);
        // set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get channelID
    $youtubeID = readline("Enter YouTube Channel ID: ");
    if (!$youtubeID) {
        die ("No youtubeID inserted\n");
    }
    $sql = $conn->prepare("
    SELECT ytId
    FROM yt_user
    WHERE ytId = :id
    ");
    $sql->execute(array(':id' => $youtubeID));
    $check = $sql->fetchAll(PDO::FETCH_ASSOC);
    if (!$check) {

        $data = getUser($youtubeID);
    // $videos = getVideos ($youtubeID);
    // insert data to sql table
        $sql = $conn->prepare("INSERT INTO yt_user 
            (
                `ytId`,
                `channelName`,
                `yt_desc`,
                `profilePic`,
                `videoCount`,
                `subscriberCount`,
                `viewCount`,
                `createdAt`,
                `country`,
                `uploadPlaylistId`,
                `topicIds`,
                `topicCategories`
            )
            VALUES
            (
                '" . $data[0] . "',
                '" . $data[1] . "',
                :bio,
                '" . $data[3] . "',
                '" . $data[4] . "',
                '" . $data[5] . "',
                '" . $data[6] . "',
                '" . $data[7] . "',
                '" . $data[8] . "',
                '" . $data[9] . "',
                '" . $data[10] . "',
                '" . $data[11] . "'
            )
        ");
        $sql->execute(array(
            ':bio' => $data[2]
        ));
    //$video_list is an array with key 'id' and value = all the uploaded videoId of $youtubeId seperated by ','
        $video_list = getVideoId($data[9]);

    // $result is a 2D array, it contains x amount of videos and each $result[x] has data of the video
        $result = getVideo($video_list);
        if ($result) {
    //save last insert Id
            $db_ytId = $conn->lastInsertId();

            for ($i = 0; $i < count($result); ++$i) {
                $vid = $conn->prepare("INSERT INTO yt_video 
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
                `categId`
            )
            VALUES
            (
                '" . $db_ytId . "',
                '" . $result[$i]['videoId'] . "',
                :title,
                :descr,
                '" . $result[$i]['pictureUrl'] . "',
                '" . $result[$i]['postDate'] . "',
                '" . $result[$i]['viewCount'] . "',
                '" . $result[$i]['likeCount'] . "',
                '" . $result[$i]['dislikeCount'] . "',
                '" . $result[$i]['commentCount'] . "',
                '" . $result[$i]['videoDuration'] . "',
                :tag,
                '" . $result[$i]['categId'] . "'
            )
        ");
                $vid->execute(array(
                    ':title' => $result[$i]['videoTitle'],
                    ':descr' => $result[$i]['description'],
                    ':tag' => $result[$i]['tags']
                ));
            }
        }

        echo "Success\n";
    } else {
        die("Error! Channel Exists\n");
    }

} catch (PDOException $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}



$conn = null;
?>