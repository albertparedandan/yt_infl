<?php
function conv($array) {
    $string = "";
    for ($i = 0; $i<count($array); ++$i) {
        $string .= $array[$i] . ", ";
    }
    return $string;
}

function videosListMultipleIds($service, $part, $params, &$return)
{
    $params = array_filter($params);
    $response = $service->videos->listVideos(
        $part,
        $params
    );

    for ($i = 0; @$response['items'][$i]['statistics']['viewCount'] != null; ++$i) {
        print_r($response['items'][$i]['snippet']['categoryId']);
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
                'tags' => conv($response['items'][$i]['snippet']['tags']),
                'categId' => $response['items'][$i]['snippet']['categoryId']
            )
        );
    }
}
function getVideo($array)
{
    if (empty($array[0])) {
        return;
    }
    global $service;
    $videoList = [];
    for ($i = 0; $i < count($array); ++$i) {
        videosListMultipleIds($service, 'snippet, contentDetails, statistics', array('maxResults' => 50, 'id' => $array[$i]), $videoList);
    }
    return $videoList;
}
?>