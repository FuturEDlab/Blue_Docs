<?php
	/* Pass sign-in request to Neon Auth and return the response. */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		header('Content-Type: application/json');
		if (isset($_POST['email']) && isset($_POST['password'])) {
			$email = htmlspecialchars($_POST['email']);
			$password = htmlspecialchars($_POST['password']);

			/* Set Auth URL */
			$url = $_ENV['BLUE_DOCS_NEON_AUTH_BASE_URL'] . '/sign-in/email';

			/* Construct Headers */
			$headers = [
				'Content-Type: application/json',
				'Accept: application/json',
				'Origin: ' . $_ENV['VERCEL_URL']
			];

			/* Construct JSON Data */
			$data = [
				'email' => $email,
				'password' => $password
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
			$response = ['message' => 'There was an issue fetching the email and password.'];
			echo json_encode($response);
		}
		exit;

	/* Return if not a GET or POST Request */
	} else if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		http_response_code(405);
		exit('Method not allowed.');
	}
?>

<!DOCTYPE HTML>
<HTML>
	<head>
		<meta charset="UTF-8">
		<title>Blue Docs Login</title>

		<link href="/styles/output.css" rel="stylesheet" type="text/css">
		<link href="/public/loading.gif" rel="preload" as="image">
	</head>

	<body>
		<main class="flex flex-col items-center justify-center min-h-screen">

			<!-- Login Container -->
			<div class="flex flex-col items-center justify-between w-1/2 lg:w-1/5 gap-7">

				<!-- Logo -->
				<a href="/index" class="w-full"><img src="/public/templogo.svg" alt="Temporary Blue Docs Logo"></a>

				<!-- Login Form -->
				<h1 class="">Log in with an existing account</h1>
				<form class="flex flex-col items-center justify-between w-full gap-4" id="loginForm">
					<input id="email" type="text" name="email" autocomplete="email" placeholder="Email" onclick="resetEmail()" class="block w-full rounded-sm outline-2 invalid:text-red-500 px-1"/>
					<input id="password" type="password" name="password" autocomplete="password" placeholder="Password" class="block w-full rounded-sm outline-2 px-1"/>
					<button id="loginSubmit" type="submit" class="block w-full rounded-sm bg-sky-500 outline-sky-500 outline-2 hover:bg-sky-400 hover:outline-sky-400 focus:bg-sky-500 focus:outline-sky-500">Log In →</button>
					<span id="errorSpan" class="text-red-500 text-center"></span>
				</form>

				<!-- Signup Link -->
				<p class="font-bold">or sign up <a href="/signup" class="underline">here</a>!</p>

			</div>
		</main>
	</body>

	<script>
		const errorSpan = document.getElementById('errorSpan');

		document.getElementById('loginForm').addEventListener('submit', (event) => {
			event.preventDefault();

			const formData = new FormData(document.getElementById('loginForm'));
			if (formData.get('email') && formData.get('password')) {
				if (/.+@(mail.)?gvsu\.edu/.test(formData.get('email'))) {
					errorSpan.innerHTML = '<img src="/public/loading.gif" alt="Loading GIF" class="size-6">';
					fetch('login.php', {
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
				errorSpan.textContent = 'Both an email and password are required.';
			}
		});

		function resetEmail() {
			document.getElementById('email').setCustomValidity('');
			errorSpan.textContent = '';
		}
	</script>
</HTML>
