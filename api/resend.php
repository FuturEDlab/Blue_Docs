<?php
	/* Pass request to resend OTP email to Neon Auth and return the response. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		session_start();

		if (isset($_SESSION['email'])) {
			$email = htmlspecialchars($_SESSION['email']);

			/* Set Auth URL */
			$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/email-otp/send-verification-otp';

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_URL']
			];

			/* Construct JSON Data */
			$data = [
				'email' => $email,
				'type' => 'email-verification'
			];

			/* Initialize cURL */
			$ch = curl_init();

			/* Set cURL Options */
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			/* Handle cURL Response and End Session */
			$response = curl_exec($ch);
			if ($response === false) {
				die('cURL Error: ' . curl_error($ch));
			} else {
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				http_response_code($http_code);
				echo $response;
			}
		} else {
			/* Respond 401 if an email and password isn't received. */
			http_response_code(401);
			$response = ['message' => 'There was an issue fetching the email.'];
			echo json_encode($response);
		}
		exit;

	/* Return if not a GET or POST Request */
	} else if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>
