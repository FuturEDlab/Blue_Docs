<?php
	/* Pass request to delete user. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$postData = json_decode(file_get_contents('php://input'), true);

		if(isset($postData['id'])) {
			/* Delete user via Neon data API. */
		
			/* Set Auth URL */
			$url = 'https://console.neon.tech/api/v2/projects/' . $_ENV['BLUE_DOCS_NEON_PROJECT_ID'] . '/branches/' . $_ENV['BLUE_DOCS_NEON_MAIN_BRANCH_ID'] . '/auth/users/' . $postData['id'];

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_ORIGIN'],
				'Authorization: Bearer ' . $_ENV['BLUE_DOCS_NEON_API_TOKEN']
			];

			/* Initialize cURL */
			$ch = curl_init();

			/* Set cURL Options */
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			/* Handle cURL Response and End Session */
			$response = curl_exec($ch);
			if ($response === false) {
				die('cURL Error: ' . curl_error($ch));
			} else {
				echo $response;
			}

			exit();

		/* Return if User ID not Set */
		} else {
			http_response_code(405);
			exit('Method not allowed.');
		}
	/* Return if not a POST Request */
	} else {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>
