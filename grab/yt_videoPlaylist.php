<?php 
function yt_playlist($service, $part, $params, &$playlist)
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
    for ($i = 0; @$response['items'][$i]['contentDetails']['videoId'] != NULL; ++$i) {
        $string .= $response['items'][$i]['contentDetails']['videoId'] . ",";
    }
    $string = substr($string, 0, -1);
    return $string;
}

function getVideoId($playlistId)
{
    global $service;
    $track = array("");
    $result = array("");
    // track[0] = totalVideo/videoPerCall
    // track[1] = nextPageToken
    $result[0] = yt_playlist($service, 'snippet, contentDetails', array('maxResults' => 50, 'playlistId' => $playlistId), $track);
    
    while ($track[0] > 1) {
        array_push($result, yt_playlist($service, 'snippet, contentDetails', array('maxResults' => 50, 'pageToken' => $track[1], 'playlistId' => $playlistId), $track));
        --$track[0];
    }
    // result is an array of videoIds with 50 ids per element
    return $result;
}
?>