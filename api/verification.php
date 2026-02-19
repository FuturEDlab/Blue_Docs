<?php
	/* Pass OTP submission request to Neon Auth and return the response. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		header('Content-Type: application/json');
		if (isset($_POST['email']) && isset($_POST['otp'])) {
			$email = htmlspecialchars($_POST['email']);
			$otp = htmlspecialchars($_POST['otp']);

			/* Set Auth URL */
			$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/email-otp/verify-email';

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_URL']
			];

			/* Construct JSON Data */
			$data = [
				'email' => $email,
				'otp' => $otp
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
				/* Return headers and body from Neon to client. */
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$body = substr($response, $header_size);

				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				http_response_code($http_code);

				$header_lines = explode("\n", trim($header));
				foreach ($header_lines as $line) {
					/* Skip content-lenth header, caused issues with cutting off response body. */
					if (stripos($line, 'content-length:') === 0) {
						continue;
					}
					header($line);
				}
				echo $body;
			}
		} else {
			/* Respond 401 if an email and password isn't received. */
			http_response_code(401);
			$response = ['message' => 'There was an issue fetching the email verification info.'];
			echo json_encode($response);
		}
		exit;

	/* Return if not a redirected GET request or POST Request */
	} else if ($_SERVER['REQUEST_METHOD'] != 'GET' || empty($_SERVER['HTTP_REFERER'])) {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>

<!DOCTYPE HTML>
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs Login</title>

		<link href="/output.css" rel="stylesheet" type="text/css">
		<link href="/loading.gif" rel="preload" as="image">
	</head>

	<body>
		<main class="flex flex-col items-center justify-center min-h-screen">

			<!-- Verification Container -->
			<div class="flex flex-col items-center justify-between w-1/2 lg:w-1/5 gap-7">

				<!-- Logo -->
				<a href="/index" class="w-full"><img src="/templogo.svg" alt="Temporary Blue Docs Logo"></a>

				<!-- Resend OTP Button -->
				<button id="otpResend" onclick="resendOTP()" class="block w-full rounded-sm bg-sky-500 outline-sky-500 outline-2 hover:bg-sky-400 hover:outline-sky-400 focus:bg-sky-500 focus:outline-sky-500">Resend Verification Code →</button>

				<!-- Verification Form -->
				<form class="flex flex-col items-center justify-between w-full gap-4" id="otpForm">
					<input id="email" type="text" name="email" autocomplete="email" placeholder="Email" onclick="resetEmail()" class="block w-full rounded-sm outline-2 invalid:text-red-500 px-1"/>
					<input id="otp" type="number" name="otp" autocomplete="otp" placeholder="Verification Code" class="block w-full rounded-sm outline-2 invalid:text-red-500 px-1"/>
					<button id="otpSubmit" type="submit" class="block w-full rounded-sm bg-sky-500 outline-sky-500 outline-2 hover:bg-sky-400 hover:outline-sky-400 focus:bg-sky-500 focus:outline-sky-500">Submit Verification Code →</button>
					<span id="errorSpan" class="text-red-500 text-center"></span>
				</form>
			</div>
		</main>
	</body>

	<script>
		const errorSpan = document.getElementById('errorSpan');

		document.getElementById('otpForm').addEventListener('submit', (event) => {
			event.preventDefault();

			const formData = new FormData(document.getElementById('otpForm'));
			if (formData.get('email') && formData.get('otp')) {
				if (/.+@(mail.)?gvsu\.edu/.test(formData.get('email'))) {
					errorSpan.innerHTML = '<img src="/loading.gif" alt="Loading GIF" class="size-6">';
					fetch('/verification', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						},
						body: new URLSearchParams(formData).toString()
					})
					.then(response => {
						if (response.status >= 500) {
							errorSpan.textContent = "Server error.";
						} else {
							return response.json();
						}
					})
					.then(data => {
						if (data !== undefined) {
							if ('user' in data) {
								window.location.href='/index';
							} else {
								errorSpan.textContent = data.message;
							}
						}
					})
					.catch(error => {
						errorSpan.textContent = error.message;
					});
				} else {
					document.getElementById('email').setCustomValidity('Not a valid GVSU email.');
					errorSpan.textContent = 'Not a valid GVSU email.';
				}
			} else {
				errorSpan.textContent = 'Both an email and verification code are required.';
			}
		});

		function resendOTP() {
			const formData = new FormData(document.getElementById('otpForm'));
			if (formData.get('email')) {
				if (/.+@(mail.)?gvsu\.edu/.test(formData.get('email'))) {
					errorSpan.innerHTML = '<img src="/loading.gif" alt="Loading GIF" class="size-6">';
					fetch('/resend', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						},
						body: new URLSearchParams(formData).toString()
					})
					.then(response => {
						if (response.status >= 500) {
							errorSpan.textContent = "Server error.";
						} else {
							return response.json();
						}
					})
					.then(data => {
						if (data !== undefined) {
							errorSpan.textContent = data.message;
						}
					})
					.catch(error => {
						errorSpan.textContent = error.message;
					});
				} else {
					document.getElementById('email').setCustomValidity('Not a valid GVSU email.');
					errorSpan.textContent = 'Not a valid GVSU email.';
				}
			} else {
				errorSpan.textContent = 'Email required to resend verification code.';
			}
		}

		function resetEmail() {
			document.getElementById('email').setCustomValidity('');
			errorSpan.textContent = '';
		}
	</script>
</HTML>
