<?php
	/* Pass request to log out user session. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_COOKIE['__Secure-neon-auth_session_token'])) {
		$postData = json_decode(file_get_contents('php://input'), true);

		/* Log out user via Neon data API. */
		
		/* Set Auth URL */
		$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/sign-out';

		/* Construct Headers */
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'Origin: ' . $_ENV['VERCEL_ORIGIN']
		];

		/* Empty JSON Data (couldn't get this to work otherwise) */
		$data = [];

		/* Initialize cURL */
		$ch = curl_init();

		/* Set cURL Options */
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_COOKIE, '__Secure-neon-auth.session_token=' . $_COOKIE['__Secure-neon-auth_session_token']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		/* Handle cURL Response and End Session */
		$response = curl_exec($ch);
		if ($response === false) {
			die('cURL Error: ' . curl_error($ch));
		} else {
			echo $response;
		}

		exit();

	/* Return if not a POST Request */
	} else {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>
