<?php
	$price 		= $_POST['purchasePriceField'];
	$hasAgent	= $_POST['hasAgentField'];
	$location	= $_POST['propertyLocationField'];
	$propType	= $_POST['propertyTypeField'];
	$timeline	= $_POST['timelineField'];
	$refid		= $_POST['refIdField'];
	$fname		= $_POST['fnameField'];
	$lname		= $_POST['lnameField'];
	$email		= $_POST['emailField'];
	$phone		= $_POST['phoneField'];
	$matchtoken = $_POST['matchTokenField'];
	$downpymt	= $_POST['downPaymentField'];
	$credit		= $_POST['creditField'];
	
	$curl = curl_init();

	//TODO: Set production API Key, change "live" param to "true", confirm preapproved = true/false
	$options = array(CURLOPT_URL => "https://api.dwellful.com/smartmatch/v1/acceptMatch",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => "{\"type\":\"buyer\",\"live\":false,\"ref\":\"".$refid."\",\"match_token\":\"".$matchtoken."\",\"data\":{\"with_agent\":\"".$hasAgent."\",\"preapproved\":true,\"location\":\"".$location."\",\"budget\":\"".$price."\",\"property_type\":\"".$propType."\",\"timeline\":\"".$timeline."\",\"firstname\":\"".$fname."\",\"lastname\":\"".$lname."\",\"email\":\"".$email."\",\"phone\":\"".$phone."\",\"custom\":{\"downpayment\":\"".$downpymt."\",\"self_reported_credit\":\"".$credit."\"}}}",
					CURLOPT_HTTPHEADER => array(
						"X-API-KEY: demo",
						"content-type: application/json"
					)
				);

	curl_setopt_array($curl, $options);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		echo "cURL Error #:" . $err;
	} else {
		echo $response;
	}	
?>