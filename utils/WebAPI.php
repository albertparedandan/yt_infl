<?php 

class WebAPI 
{
	public function __construct($location = null)
    {
    	
    }
    public function sendEmailForInfluencerSuccess ($locationId = 1, $email, $name, $infId, $token = "e56897ad805b9f6cfe964af97d2cee97"){
    	
    	$apiHost = 'https://cloudbreakr.com/auth/signup/sendSuccessInfluencerEmail/'.$locationId.'/'.$email.'/'.$name.'/'.$infId.'/'.$token;

    	return $this->getData($apiHost);
    }

    public function sendEmailForAdmin ($email, $title, $message){
    	
    	$apiHost = 'https://cloudbreakr.com/home/sendSuccessEmail';
  
    	return $this->doHttpPost($apiHost, array('email'=>$email,'title'=>$title,'message'=>$message, 'token'=>'e56897ad805b9f6cfe964af97d2cee97'));
    }


	private function getData($url, $data = [])
	{
		try{
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			if(!empty($data)){
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);


			$jsonData = curl_exec($ch);

			if(curl_errno($ch) ){
				return json_decode(curl_errno($ch));
			}

			//echo 'error code:'. curl_errno($ch) .'</br>';
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			$data = json_decode($jsonData);
			//print_r($data);

			
			if(!isset($data->meta)){
				//print_r($data);
				return $data;
			}
			return json_decode($jsonData);

		} catch (Exception $e) { 
	        // print $e->getMessage(); 
	        // exit(); 
	        print_r($e->getMessage());
	    }
	}

	private function doHttpPost($url, $keyValuePairArr){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keyValuePairArr);
        //TODO the following 2 lines are added to deal with SSL errors. may replace this by more secure solutions.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// execute!
        $response = curl_exec($ch);

        // handle error
        if($response === FALSE){
            var_dump(curl_error($ch));
        }
        // close the connection, release resources used
        curl_close($ch);

				// var_dump($response);
        return $response;
    }
}

?>