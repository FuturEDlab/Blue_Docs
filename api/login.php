<?php
	/* Initialize cURL */
	$ch = curl_init();

	/* Construct URL Parameters */
	$params = [
		'client_id' => $_ENV['CLIENT_ID'],
		'redirect_uri' => $_ENV['REDIRECT_URI'],
		'response_type' => 'token',
		'scope' => 'https://www.googleapis.com/auth/userinfo.profile.readonly https://www.googleapis.com/auth/userinfo.email.readonly',
		'hd' => 'gvsu.edu'
	];

	/* Set Google OAuth URL */
	$url = 'https://accounts.google.com/o/oauth2/v2/auth' . '?' . http_build_query($params);

	/* Set cURL Options */
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	/* Echo cURL Response */
    $response = curl_exec($ch);
    echo $response;
?>
