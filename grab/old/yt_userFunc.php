<?php
function yt_user($service, $part, $params)
{
  $params = array_filter($params);
  $response = $service->channels->listChannels(
    $part,
    $params
  );
  $data = array(
    $response['items'][0]['id'],
    $response['items'][0]['snippet']['title'],
    str_replace(array("\n", "\r"), '', $response['items'][0]['snippet']['description']),
    $response['items'][0]['snippet']['thumbnails']['default']['url'],
    $response['items'][0]['statistics']['videoCount'],
    $response['items'][0]['statistics']['subscriberCount'],
    $response['items'][0]['statistics']['viewCount'],
    $response['items'][0]['snippet']['publishedAt'],
    $response['items'][0]['snippet']['country'],
    $response['items'][0]['contentDetails']['relatedPlaylists']['uploads']
  );
  $topicId = "";
  for ($i = 0; $i < count($response['items'][0]['topicDetails']['topicIds']); ++$i) {
    $topicId .= $response['items'][0]['topicDetails']['topicIds'][$i] . ", ";
  }
  
  $topicCateg = "";
  for ($i = 0; $i < count($response['items'][0]['topicDetails']['topicCategories']); ++$i) {
    $topicCateg .= $response['items'][0]['topicDetails']['topicCategories'][$i] . ", ";
  }
  array_push($data, $topicId);
  array_push($data, $topicCateg);

  return $data;
}

function getUser($youtubeID)
{
  global $service;
  $data = yt_user($service, 'snippet,contentDetails,statistics,topicDetails', array('id' => $youtubeID));
  return $data;
}
?>