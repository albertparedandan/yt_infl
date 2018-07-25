# yt_infl
change client_secrets to client_secret
change default timezone to Asia/Hong-Kong or wherever you are in /vendor/google/auth/src/Cache/Item.php line:130
change:
    FROM: $accessToken = file_get_contents($credentialsPath);
    TO:   $accessToken = json_decode(file_get_contents($credentialsPath), true);  // don't miss the second parameter true here!!!

    FROM: file_put_contents($credentialsPath, $accessToken);
    TO:   file_put_contents($credentialsPath, json_encode($accessToken));

    FROM: file_put_contents($credentialsPath, $client->getAccessToken());
    TO:   file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
